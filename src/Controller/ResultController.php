<?php

namespace App\Controller;

use App\DTO\WaterAnalysisDto;
use App\Service\CallApiHubeau;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ResultController extends AbstractController
{
    #[Route('/result', name: 'app_result')]
    public function showWaterAnalyseResults(Request $request, CallApiHubeau $callApi): Response
    {
        $codeCommune = $request->query->get('code_commune');

        //on récupérer toutes les données d'analyse sur 6 mois
        $rawDate = $callApi->fetchLast6MonthsResults($codeCommune);
        
        //filtrage par le dto
        $dtos = [];
        foreach ($rawDate['data'] as $result) {
            $dtos[] = new WaterAnalysisDto(
                parameter: $result['libelle_parametre'],
                value: isset($result['resultat_numerique']) ? (float) $result['resultat_numerique'] : null,
                unit: $result['libelle_unite'] ?? null,
                date: new \DateTimeImmutable($result['date_prelevement'])
            );
        }

        dd($dtos);

        return $this->render('result/index.html.twig', [
            'code_commune' => $codeCommune
        ]);
    }
}
