<?php

namespace App\Enums;

enum ToolStatusType: string
{
    case active = 'active';
    case deprecated = 'deprecated';
    case trial = 'trial';
}
