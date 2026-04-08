<?php

namespace App\Service;

use App\Entity\Tools;
use Doctrine\ORM\EntityManagerInterface;

class DataAnalytics
{
    public function __construct(Private EntityManagerInterface $entityManager){}

    public function getDataAnalytics(): array
    {
        $dataAnalytics=[];
        $summary = [
            "total_company_cost" => 0,
            "department_count" => 0,
            "most_expensive_department" => null,
        ];
        $tools = $this->entityManager->getRepository(Tools::class)->findAll();

        foreach ($tools as $tool){

            $department = $tool->getOwnerDepartment()->value;
            $toolCost = $tool->getMonthlyCost();
            $users = $tool->getActiveUsersCount();

            if (!isset($dataAnalytics[$department])){
                $dataAnalytics[$department]=[
                    "total_cost"=> 0,
                    "total_count"=>0,
                    "total_users"=>0,
                    "average_cost"=>0,
                    "percentage_cost"=>0,
                ];
            }

            $dataAnalytics[$department]["total_cost"] = round($dataAnalytics[$department]["total_cost"] + $toolCost, 2);
            $dataAnalytics[$department]["total_count"]++;
            $dataAnalytics[$department]["total_users"] += $users;

        }

        //la somme totale de tous les départements
        $totalDepartmentsCost = array_sum(
            array_column($dataAnalytics, "total_cost")
        );

        $summary["total_company_cost"] = round($totalDepartmentsCost,2) ;
        $maxCost = 0;

        foreach($dataAnalytics as $department => $stats){

            $averageCost  = $stats["total_cost"] / $stats["total_count"];
            $percentageCost = ($stats["total_cost"] / $totalDepartmentsCost) * 100;

            $dataAnalytics[$department]["average_cost"] = round($averageCost, 2);
            $dataAnalytics[$department]["percentage_cost"] = round($percentageCost, 2);

            if ($stats["total_cost"] > $maxCost)
            {
                $maxCost = $stats["total_cost"];
                $summary["most_expensive_department"] = $department;
            }
        }

        $summary["department_count"] = count($dataAnalytics);

        return [
            "dataAnalytics"=> $dataAnalytics,
            "summary"=>$summary,
        ];
    }

}

