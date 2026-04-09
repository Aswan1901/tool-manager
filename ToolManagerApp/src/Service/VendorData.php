<?php

namespace App\Service;

use App\Entity\Tools;
use App\Repository\ToolsRepository;
use Doctrine\ORM\EntityManagerInterface;

class VendorData
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ToolsRepository $toolsRepository,
    ) {}

    public function getVendorsData(): array
    {
        $tools = $this->entityManager->getRepository(Tools::class)->findAll();
        $vendors = $this->toolsRepository->getAllVendors();

        $data = [];

        $highestCost = 0;
        $mostExpensiveVendor = null;

        $lowestAverageCostPerUser = null;
        $mostEfficientVendor = null;

        $singleToolVendors = 0;

        foreach ($vendors as $vendor) {
            $toolsCount = 0;
            $totalMonthlyCost = 0.0;
            $totalUsers = 0;
            $departments = [];

            foreach ($tools as $tool) {
                if ($tool->getVendor() !== $vendor) {
                    continue;
                }

                $toolsCount++;
                $totalMonthlyCost += (float) $tool->getMonthlyCost();
                $totalUsers += (int) $tool->getActiveUsersCount();

                $department = $tool->getOwnerDepartment();

                if ($department !== null) {
                    $departmentValue = $department->value;

                    if (!in_array($departmentValue, $departments, true)) {
                        $departments[] = $departmentValue;
                    }
                }
            }

            if ($toolsCount === 1) {
                $singleToolVendors++;
            }

            $averageCostPerUser = $totalUsers > 0
                ? $totalMonthlyCost / $totalUsers
                : 0.0;

            $vendorEfficiency = match (true) {
                $averageCostPerUser < 5 => 'excellent',
                $averageCostPerUser <= 15 => 'good',
                $averageCostPerUser <= 25 => 'average',
                default => 'poor',
            };

            if ($totalMonthlyCost > $highestCost) {
                $highestCost = $totalMonthlyCost;
                $mostExpensiveVendor = $vendor;
            }

            if ($lowestAverageCostPerUser === null || $averageCostPerUser < $lowestAverageCostPerUser) {
                $lowestAverageCostPerUser = $averageCostPerUser;
                $mostEfficientVendor = $vendor;
            }

            $data[] = [
                'vendor' => $vendor,
                'tools_count' => $toolsCount,
                'total_monthly_cost' => round($totalMonthlyCost, 2),
                'total_users' => $totalUsers,
                'departments' => implode(',', $departments),
                'average_cost_per_user' => round($averageCostPerUser, 2),
                'vendor_efficiency' => $vendorEfficiency,
            ];
        }

        return [
            'data' => $data,
            'vendor_insights' => [
                'most_expensive_vendor' => $mostExpensiveVendor,
                'most_efficient_vendor' => $mostEfficientVendor,
                'single_tool_vendors' => $singleToolVendors,
            ],
        ];
    }
}
