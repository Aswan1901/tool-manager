<?php

namespace App\Controller;

use App\Entity\Tools;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;



#[Route('/api/analytics', name: 'app_analytics')]
final class AnalyticsController extends AbstractController
{
    public function __construct(Private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/', name: 'app_analytics')]
    public function analytics(): JsonResponse
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
                    "totalCost"=>0,
                    "totalCount"=>0,
                    "totalUsers"=>0,
                    "averageCost"=>0,
                    "percentageCost"=>0,
                ];
            }

            $dataAnalytics[$department]["totalCost"] += round($toolCost, 2);
            $dataAnalytics[$department]["totalCount"]++;
            $dataAnalytics[$department]["totalUsers"] += $users;

        }
        $totalDepartmentsCost = array_sum(
            array_column($dataAnalytics, "totalCost")
        );

        $summary["total_company_cost"] = $totalDepartmentsCost;
        $maxCost = 0;

            foreach($dataAnalytics as $department => $stats){

                $averageCost  = $stats["totalCost"] / $stats["totalCount"];
                $percentageCost = ($stats["totalCost"] / $totalDepartmentsCost) * 100;

                $dataAnalytics[$department]["averageCost"] = round($averageCost, 2);
                $dataAnalytics[$department]["percentageCost"] = round($percentageCost, 2);

                if ($stats["totalCost"] > $maxCost)
                {
                    $maxCost = $stats["totalCost"];
                    $summary["most_expensive_department"] = $department;
                }

            }

            $summary["department_count"] = count($dataAnalytics);
        return $this->json([
            "data analytics" => $dataAnalytics,
            "summary" => $summary,
        ], 200);
    }


}
