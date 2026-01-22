<?php

namespace App\Enums;

enum AccessStatusType: string
{
    case ACTIVE = 'active';
    case REVOKED = 'revoked';
}
