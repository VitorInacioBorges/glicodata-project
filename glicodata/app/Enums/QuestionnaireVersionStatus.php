<?php

namespace App\Enums;

enum QuestionnaireVersionStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Retired = 'retired';
}
