<?php

namespace App\Controller;

use App\Form\Type\SearchCityByInseeCode;
use App\Form\Type\SearchCityType;
use App\Service\CallApiHubeau;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WaterAnalysisController extends AbstractController
{
    #[Route('/water/analysis', name: 'app_water_analysis')]
    public function showAnalysis(CallApiHubeau $callApiHubeau, Request $request): Response
    {
        $form = $this->createForm(SearchCityType::class);
        $form->handleRequest($request);

        $inseeCodeForm = $this->createForm(SearchCityByInseeCode::class);
        $inseeCodeForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $codeCommune = $callApiHubeau->getInseeCode($form->get('city')->getData());

                return $this->redirectToRoute('app_result', ['code_commune' => $codeCommune]);
            } catch (\Throwable $e) {
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('app_result');
            }
        }

        if ($inseeCodeForm->isSubmitted() && $inseeCodeForm->isValid()) {
            $codeCommune = $inseeCodeForm->get('insee_code')->getData();

            return $this->redirectToRoute('app_result', ['code_commune' => $codeCommune]);
        }


        return $this->render('water_analysis/index.html.twig', [
            'form' => $form,
            'insee_code_form' => $inseeCodeForm
        ]);
    }
}
