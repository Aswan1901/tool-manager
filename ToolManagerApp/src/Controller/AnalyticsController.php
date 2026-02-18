<?php

namespace App\Controller;

use App\Entity\Tools;
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

    #[Route('/department-costs', name: 'app_analytics')]
    public function analytics(Request $request): JsonResponse
    {

        $sortByTotalCost = $request->query->get("sortByTotalCost");
        $result = $this->dataAnalytics->getDataAnalytics();

        $dataAnalytics = $result['dataAnalytics'];
        $summary = $result['summary'];

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

    #[Route('/expensive-tool', name: 'app_analytics')]
    public function expensiveTools(Request $request): JsonResponse
    {
        $expensiveTool = $request->query->get("expensive-tool");



    }
}
