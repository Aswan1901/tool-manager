<?php

namespace App\Service;


use App\Entity\Tools;
use App\Entity\Users;
use App\Enums\UserStatusType;
use Doctrine\ORM\EntityManagerInterface;

class MostExpensiveTool{

    public function __construct(
        Private EntityManagerInterface $entityManager
    ){}

    public function findMostExpensiveTool(): array
    {
        $tools = $this->entityManager->getRepository(Tools::class)->findAll();
        $users = $this->entityManager->getRepository(Users::class)->findAll();

        // Pre-group active user counts by department
        $userCountByDepartment = [];
        foreach ($users as $user) {
            if ($user->getUserStatusActive() === UserStatusType::active) {
                $dept = $user->getDepartmentType()->value;
                $userCountByDepartment[$dept] = ($userCountByDepartment[$dept] ?? 0) + 1;
            }
        }

        $toolsTemp = [];
        $totalCost = 0.0;
        $totalUsers = 0;

        foreach ($tools as $tool) {
            $dept = $tool->getOwnerDepartment()->value;
            $activeUsersCount = $userCountByDepartment[$dept] ?? 0;
            $monthlyCost = $tool->getMonthlyCost();

            $costPerUser = $activeUsersCount > 0
                ? round($monthlyCost / $activeUsersCount, 2)
                : 0;

            if ($activeUsersCount > 0) {
                $totalCost  += $monthlyCost;
                $totalUsers += $activeUsersCount;
            }

            $toolsTemp[] = [
                "id"                 => $tool->getId(),
                "name"               => $tool->getName(),
                "monthly_cost"       => $monthlyCost,
                "active_users_count" => $activeUsersCount,
                "cost_per_user"      => $costPerUser,
                "department"         => $dept,
                "vendor"             => $tool->getVendor(),
            ];
        }

        $avgCostPerUserCompany = $totalUsers > 0 ? round($totalCost / $totalUsers, 2) : 0;

        $toolsData      = [];
        $potentialSavings = 0.0;

        foreach ($toolsTemp as $tool) {
            $costPerUser = $tool['cost_per_user'];

            if ($avgCostPerUserCompany > 0 && $tool['active_users_count'] > 0) {
                $ratio = $costPerUser / $avgCostPerUserCompany;

                $efficiencyRating = match(true) {
                    $ratio < 0.5  => "excellent",
                    $ratio < 0.8  => "good",
                    $ratio <= 1.2 => "average",
                    default       => "low",
                };
            } else {
                $efficiencyRating = "average";
            }

            if ($efficiencyRating === "low") {
                $potentialSavings += $tool['monthly_cost'];
            }

            $toolsData[] = array_merge($tool, [
                "efficiency_rating" => $efficiencyRating,
            ]);
        }

        // Sort descending by monthly_cost
        usort($toolsData, fn($a, $b) => $b['monthly_cost'] <=> $a['monthly_cost']);

        return [
            "data" => $toolsData,
            "analysis" => [
                "total_tools_analyzed"         => count($toolsData),
                "avg_cost_per_user_company"    => $avgCostPerUserCompany,
                "potential_savings_identified" => round($potentialSavings, 2),
            ],
        ];
    }
}
