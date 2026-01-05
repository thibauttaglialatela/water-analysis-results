<?php

namespace App\Controller;

use App\DTO\SanitaryControlDto;
use App\DTO\WaterAnalysisDto;
use App\Enums\WaterParameter;
use App\Service\CallApiHubeau;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Throwable;

final class ResultController extends AbstractController
{
    #[Route('/result', name: 'app_result')]
    public function showWaterAnalyseResults(Request $request, CallApiHubeau $callApi, ChartBuilderInterface $chartBuilder): Response
    {
        $codeCommune = $request->query->get('code_commune');


        if (!$codeCommune) {
            $this->addFlash('error', 'Code INSEE manquant.');
            return $this->redirectToRoute('app_home');
        }

        try {
            $rawdata = $callApi->fetchLast6MonthsResults($codeCommune);
        } catch (\Throwable $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_home');
        }

        if (empty($rawdata['data'])) {
            $this->addFlash('error', 'Aucune analyse disponible pour ce code INSEE');
            return $this->redirectToRoute('app_home');
        }

        //filtrage par le dto
        $results = [];
        $cityName = $rawdata['data'][0]['nom_commune'];

        foreach ($rawdata['data'] as $row) {
            $parameter = WaterParameter::tryFrom($row['libelle_parametre']);

            if ($parameter === null) {
                continue;
            }

            $results[] = new WaterAnalysisDto(
                parameter: $parameter,
                value: isset($row['resultat_numerique']) ? (float) $row['resultat_numerique'] : null,
                unit: $row['libelle_unite'] ?? null,
                date: new \DateTimeImmutable($row['date_prelevement'])
            );
        }

        //on groupe par paramétre
        $grouped = [];

        foreach ($results as $dto) {
            $grouped[$dto->parameter->value][] = $dto;
        }

        $charts = [];

        foreach ($grouped as $title => $values) {
            $labels = [];
            $data = [];
            $unit = $values[0]->unit;

            foreach ($values as $value) {
                $labels[] = $value->date->format('d/m');
                $data[] = $value->value;
            }

            $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
            $charts[] = $chart->setData([
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => "$title",
                        'backgroundColor' => 'oklch(37.9% 0.146 265.522)',
                        'borderColor' => 'oklch(98.5% 0 0)',
                        'data' => $data,
                        'fill' => true,
                    ]
                ],
            ]);


            $chart->setOptions([
                'responsive' => true,
                'plugins' => [
                    'legend' => ['display' => true, 'labels' => ['usePointStyle' => false]],
                ],
                'scales' => [
                    'y' => [
                        'title' => [
                            'display' => true,
                            'text' => $unit,
                            'color' => 'oklch(37.9% 0.146 265.522)'
                        ],
                        'beginAtZero' => true,
                    ],
                ],
            ]);
        }

        return $this->render('result/index.html.twig', [
            'nom_commune' => $cityName,
            'charts' => $charts,
        ]);
    }


    #[Route('/sanitary-control-history', name: 'app_sanitary_control_history')]
    public function showSanitaryControlHistory(Request $request, CallApiHubeau $callApi): Response
    {
        $codeCommune = $request->query->get('code_commune');
        $cityName = $request->query->get('city');

        if (!$codeCommune) {
            $this->addFlash('error', 'Code INSEE manquant.');
            return $this->redirectToRoute('app_home');
        }

        try {
            $rawdata = $callApi->fetchLast6MonthsResults($codeCommune);
        } catch (\Throwable $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_home');
        }

        if (empty($rawdata['data'])) {
            $this->addFlash('error', 'Aucune analyse disponible pour ce code INSEE');
            return $this->redirectToRoute('app_home');
        }

        $controls = [];

        foreach ($rawdata['data'] as $row) {
            $controls[] = new SanitaryControlDto(
                date: new \DateTimeImmutable($row['date_prelevement']),
                conclusionConformite: $this->hasNonConformity($row) ? $row['conclusion_conformite_prelevement'] : null
            );
        }
        
        return $this->render('result/sanitary_control_history.html.twig', [
            'controls' => $this->uniqueMultimArray($controls),
            'city_name' => $cityName
        ]);
    }

    private function hasNonConformity(array $row): bool
    {
        return in_array('N', [
            $row['conformite_limites_bact_prelevement'] ?? null,
            $row['conformite_limites_pc_prelevement'] ?? null,
            $row['conformite_references_bact_prelevement'] ?? null,
            $row['conformite_references_pc_prelevement'] ?? null
        ], true);
    }

    private function uniqueMultimArray(array $array): array
    {
        $serialized = array_map('serialize', $array);
        $unique = array_unique($serialized);
        return array_map('unserialize', $unique);
    }
}
