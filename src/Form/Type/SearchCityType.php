<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchCityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('city', TextType::class, [
            'attr' => ['class' => 'mt-0.5 w-full rounded border border-blue-800 text-gray-800 pe-10 shadow-xl shadow-emerald-50 sm:text-sm'],
            'mapped' => false,
        ])
        ;
    }


}