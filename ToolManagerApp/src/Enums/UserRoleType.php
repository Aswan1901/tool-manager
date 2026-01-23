<?php

namespace App\Enums;
enum UserRoleType: string
{
    case employee = 'employee';
    case admin = 'admin';
    case manager = 'manager';
}
