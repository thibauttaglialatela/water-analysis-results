<?php

declare(strict_types=1);

namespace App\Enums;

enum WaterParameter: string
{
    case PH = "pH";
    case NITRATE = "Nitrates (en NO3)";
    case NITRITE = "Nitrites (en NO2)";
    case TEMPERATURE = "Température de l'eau";
    case CHLORE_LIBRE = "Chlore libre";
    case COLIFORMES = "Bactéries coliformes /100ml-MS";
    case CONDUCTIVITE = "Conductivité à 25°C";
}