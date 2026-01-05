<?php

namespace App\Controller;

use App\Form\Type\SearchCityByInseeCode;
use App\Form\Type\SearchCityType;
use App\Service\CallApiHubeau;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SanitaryControlController extends AbstractController
{
    #[Route('/sanitary/control', name: 'app_sanitary_control')]
    public function ShowSanitaryControl(CallApiHubeau $callApiHubeau, Request $request): Response
    {
        $form = $this->createForm(SearchCityType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $codeCommune = $callApiHubeau->getInseeCode($form->get('city')->getData());
                $cityName = strtoupper($form->get('city')->getData());

                return $this->redirectToRoute('app_sanitary_control_history', ['code_commune' => $codeCommune, 'city' =>$cityName]);
            } catch (\Throwable $e) {
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('app_sanitary_control_history');
            }
        }

        $inseeCodeForm = $this->createForm(SearchCityByInseeCode::class);
        $inseeCodeForm->handleRequest($request);

        if ($inseeCodeForm->isSubmitted() && $inseeCodeForm->isValid()) {
            $codeCommune = $inseeCodeForm->get('insee_code')->getData();

            return $this->redirectToRoute('app_sanitary_control_history', ['code_commune' => $codeCommune, 'city' => $callApiHubeau->findCityNameByInseeCode($codeCommune)]);
        }

        return $this->render('sanitary_control/index.html.twig', [
            'form' => $form,
            'insee_code_form' => $inseeCodeForm
        ]);
    }
}
