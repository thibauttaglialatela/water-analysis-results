<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ResultController extends AbstractController
{
    #[Route('/result', name: 'app_result')]
    public function showWaterAnalyseResults(Request $request): Response
    {
        $codeCommune = $request->query->get('code_commune');

        return $this->render('result/index.html.twig', [
            'code_commune' => $codeCommune
        ]);
    }
}
