<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\DataAnalytics;


#[Route('/api/analytics', name: 'app_analytics')]
final class AnalyticsController extends AbstractController
{
    public function __construct(Private EntityManagerInterface $entityManager, Private DataAnalytics $dataAnalytics)
    {
    }

    #[Route('/department-costs', name: 'department_costs')]
    public function analytics(Request $request): JsonResponse
    {

        $sortByTotalCost = $request->query->get("sortByTotalCost");
        $data = $this->dataAnalytics->getDataAnalytics();

        $dataAnalytics = $data['dataAnalytics'];
        $summary = $data['summary'];

        if ($sortByTotalCost === 'asc'){

            uasort($dataAnalytics, function ($a, $b) {
                return $a["totalCost"] <=> $b["totalCost"];
            });

        }elseif ($sortByTotalCost === 'desc'){
            uasort($dataAnalytics, function ($a, $b) {
                return $b["totalCost"] <=> $a["totalCost"];
            });
        }

        return $this->json([
            "data analytics" => $dataAnalytics,
            "summary" => $summary,
        ], 200);
    }

    #[Route('/expensive-tool', name: 'expensive-tool')]
    public function expensiveTools(Request $request): JsonResponse
    {
        $limit = $request->query->get("limit", 10);
        $minCost = $request->query->get("min_cost");

        if ($limit <=0){
            return $this->json(["error"=>"La limite doit être supérieur à zero"], 400);
        }

        $data = $this->dataAnalytics->getDataAnalytics();
        $dataAnalytics = $data['dataAnalytics'];

        //trie descendent
        uasort($dataAnalytics, function ($a, $b) {
            return $b["totalCost"] <=> $a["totalCost"];
        });

        if ($minCost !== null){

            $minCost = (int) $minCost;

            $dataAnalytics = array_filter($dataAnalytics, function ($stats) use ($minCost) {
                return $stats["totalCost"] >= $minCost;
            });
        }

        $limited = array_slice($dataAnalytics,0, $limit, true);

        return $this->json([
            "data" => $limited,
        ]);
    }
}
