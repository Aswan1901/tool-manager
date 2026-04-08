<?php

namespace App\Service;

use App\Entity\Categories;
use App\Entity\Tools;
use Doctrine\ORM\EntityManagerInterface;

class ToolsByCategory
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function findToolsByCategory(): array
    {
        $categories = $this->entityManager->getRepository(Categories::class)->findAll();
        $tools = $this->entityManager->getRepository(Tools::class)->findAll();

        $data = [];
        $toolsCount = [];
        $totalCost = [];
        $usersCount = [];
        $totalCompanyCost = 0.0;

        $departmentCosts = [];
        $departmentsSet = [];
        $mostExpensiveDepartment = null;
        $highestCost = 0.0;

        foreach ($tools as $tool) {
            $categoryId = $tool->getCategory()->getId();
            $department = $tool->getOwnerDepartment()->name;
            $cost = (float) $tool->getMonthlyCost();

            $toolsCount[$categoryId] = ($toolsCount[$categoryId] ?? 0) + 1;
            $totalCost[$categoryId] = ($totalCost[$categoryId]  ?? 0) + $cost;
            $usersCount[$categoryId] = ($usersCount[$categoryId] ?? 0) + $tool->getActiveUsersCount();
            $totalCompanyCost += $cost;

            $departmentCosts[$department] = ($departmentCosts[$department] ?? 0) + $cost;
            $departmentsSet[$department] = true;
        }

        foreach ($departmentCosts as $departmentName => $deptCost) {
            if ($deptCost > $highestCost) {
                $highestCost = $deptCost;
                $mostExpensiveDepartment = $departmentName;
            }
        }

        foreach ($categories as $category) {
            $catId = $category->getId();
            $cost = $totalCost[$catId]  ?? 0;
            $users = $usersCount[$catId] ?? 0;
            $count = $toolsCount[$catId] ?? 0;

            $data[] = [
                'category_name' => $category->getName(),
                'tools_count' => $count,
                'total_cost' => round($cost, 2),
                'total_users' => $users,
                'percentage_of_budget' => $totalCompanyCost > 0 ? round($cost / $totalCompanyCost * 100, 2) : 0,
                'average_cost_per_user' => $users > 0 ? round($cost / $users, 2) : 0,
            ];
        }

        $summary = [
            'total_company_cost' => round($totalCompanyCost, 2),
            'departments_count' => count($departmentsSet),
            'most_expensive_department' => $mostExpensiveDepartment,
            'most_expensive_department_cost' => round($highestCost, 2),
        ];

        return [
            'data' => $data,
            'summary' => $summary,
        ];
    }
}
