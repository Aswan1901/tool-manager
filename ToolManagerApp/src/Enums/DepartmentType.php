<?php


namespace App\Enums;

enum DepartmentType: string
{
    case Engineering = 'Engineering';
    case Sales = 'Sales';
    case Marketing = 'Marketing';
    case Finance = 'Finance';
    case Operations = 'Operations';
    case HR = 'HR';
    case Design = 'Design';
}
