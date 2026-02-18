<?php

namespace App\Modules\Template\Enums;

use App\Traits\Enums\EnumValuesTrait;

enum TemplateModule: string
{
    use EnumValuesTrait;

    case CONTRACT_TYPE = 'contract_type';
    case CONTRACT = 'contract';
    case PROJECT = 'project';
}

