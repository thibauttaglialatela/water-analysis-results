<?php

namespace App\Controller;

use App\Service\CallApiHubeau;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WaterAnalysisController extends AbstractController
{
    #[Route('/water/analysis', name: 'app_water_analysis')]
    public function showAnalysis(CallApiHubeau $callApiHubeau): Response
    {
        $codeCommune = $callApiHubeau->getInseeCode('Nice');

        $lastResults = $callApiHubeau->fetchLast6MonthsResults($codeCommune);

        dd($lastResults['data']);

        return $this->render('water_analysis/index.html.twig', [

        ]);
    }
}
