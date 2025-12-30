<?php

namespace App\Controller;

use App\DTO\WaterAnalysisDto;
use App\Enums\WaterParameter;
use App\Service\CallApiHubeau;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

final class ResultController extends AbstractController
{
    #[Route('/result', name: 'app_result')]
    public function showWaterAnalyseResults(Request $request, CallApiHubeau $callApi): Response
    {
        $codeCommune = $request->query->get('code_commune');

        if (!$codeCommune) {
            return $this->redirectToRoute('app_home');
        }
        
        try {
            $rawdata = $callApi->fetchLast6MonthsResults($codeCommune);
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_home');
        }
        
        
        //filtrage par le dto
        $results = [];
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

        

        return $this->render('result/index.html.twig', [
            'code_commune' => $codeCommune,
            'grouped_results' => $grouped
        ]);
    }
}
