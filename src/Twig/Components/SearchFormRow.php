<?php

declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\Component\Form\FormView;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class SearchFormRow
{
    public FormView $field;
    public string $label;
}
