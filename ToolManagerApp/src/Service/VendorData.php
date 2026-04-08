<?php

namespace App\Service;

use App\Entity\Tools;
use App\Repository\ToolsRepository;
use Doctrine\ORM\EntityManagerInterface;

class VendorData{
    /*
        {
          "data": [
            {
              "vendor": "Google",
              "tools_count": 4,
              "total_monthly_cost": 234.50,
              "total_users": 67,
              "departments": "Engineering,Sales,Marketing",
              "average_cost_per_user": 3.50,
              "vendor_efficiency": "excellent"
            }
          ],
          "vendor_insights": {
            "most_expensive_vendor": "BigCorp",
            "most_efficient_vendor": "Google",
            "single_tool_vendors": 8
          }
        }
    */

    public function __construct(
        Private EntityManagerInterface $entityManager,
        Private ToolsRepository $toolsRepository,
    ){}

    public function getVendorsData(): array
    {
        $tools = $this->entityManager->getRepository(Tools::class)->findAll();
        $vendors = $this->toolsRepository->getAllVendors();
        $data = [];
        $totalMonthlyCost = 0;
        $averageCostPerUser=0;
        $vendorEfficiency=null;
        $highestCost=0;
        $mostExpensiveVendor=null;
        $mostEfficientVendor=[];

        foreach ($vendors as $vendor) {

            $toolCount =0;
            foreach ($tools as $tool) {
                if ($tool->getVendor() === $vendor) {
                    $toolCount++;
                    round($totalMonthlyCost += $tool->getMonthlyCost(), 2);
                }
                if ($tool->getMonthlyCost() > $highestCost ){
                    $mostExpensiveVendor = $tool->getVendor();
                }

                $averageCostPerUser = $averageCostPerUser > 0 ??((float) $tool->getMonthlyCost() / $tool->getActiveUsersCount());
                $vendorEfficiency = match(true) {
                    $averageCostPerUser < 5  => "excellent",
                    $averageCostPerUser <= 15  => "good",
                    $averageCostPerUser >25 => "poor",
                };

                $mostEfficientVendor = [$averageCostPerUser];

            }
                $data[]=[
                    'vendor' => $vendor,
                    'toolCount' => $toolCount,
                    'total_monthly_cost'=>$totalMonthlyCost,
                    'average_cost_per_user'=>$averageCostPerUser,
                    'vendor_efficiency'=>$vendorEfficiency,
                    'most_efficient_vendor'=>$mostEfficientVendor,
                ];
        }

        return [
            'data' => $data,
            'vendor_insights'=>[
                'most_expensive_vendor'=>$mostExpensiveVendor,
            ],
        ];
    }
}
