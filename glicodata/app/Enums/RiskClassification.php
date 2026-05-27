<?php

// Representa o enum da propriedade CLASSIFICACAO DE RISCO (classificacao do risco de desenvolvimento de Diabetes Mellitus II) 

namespace App\Enums;

enum RiskClassification: string
{
    case Low = 'low';
    case Moderate = 'moderate';
    case High = 'high';
}
