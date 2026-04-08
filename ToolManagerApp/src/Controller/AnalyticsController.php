<?php

namespace App\Controller;

use App\Entity\Tools;
use App\Repository\ToolsRepository;
use App\Service\LowUsageTool;
use App\Service\ToolsByCategory;
use App\Service\VendorData;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\DataAnalytics;
use App\Service\MostExpensiveTool;


#[Route('/api/analytics', name: 'app_analytics')]
final class AnalyticsController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DataAnalytics $dataAnalytics,
        private MostExpensiveTool $mostExpensiveTool,
        private ToolsByCategory $toolsByCategory,
        private LowUsageTool $lowUsageTool,
        private ToolsRepository $toolsRepository,
        private VendorData $vendorData,
    )
    {
    }

    #[Route('/department-costs', name: 'department_costs')]
    public function analytics(Request $request): JsonResponse
    {

        $sortByTotalCost = $request->query->get("sortByTotalCost");
        $data = $this->dataAnalytics->getDataAnalytics();

        $dataAnalytics = $data['dataAnalytics'];
        $summary = $data['summary'];

        if ($sortByTotalCost === 'asc') {

            uasort($dataAnalytics, function ($a, $b) {
                return $a["totalCost"] <=> $b["totalCost"];
            });

        } elseif ($sortByTotalCost === 'desc') {
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
        $limit = (int)$request->query->get("limit", 10);
        $minCost = $request->query->get("min_cost");

        if ($limit <= 0) {
            return $this->json(["error" => "La limite doit être supérieure à zéro"], 400);
        }

        $result = $this->mostExpensiveTool->findMostExpensiveTool();
        $tools = $result["data"];

        if ($minCost !== null) {
            $minCost = (float)$minCost;
            $tools = array_values(array_filter(
                $tools,
                fn($tool) => $tool["monthly_cost"] >= $minCost
            ));
        }

        $limited = array_slice($tools, 0, $limit);

        return $this->json([
            "data" => $limited,
            "analysis" => $result["analysis"],
        ]);
    }

    #[Route('/tools-by-category', name: 'tools-category', methods: ['GET'])]
    public function toolsByCategory(): JsonResponse
    {
        return $this->json([
            "data" => $this->toolsByCategory->findToolsByCategory()
        ], 200);
    }

    #[Route('/low-usage-tools', name: 'low-usage-tool', methods: ['GET'])]
    public function findLowUsageTool(Request $request): JsonResponse
    {
        $maxUser = $request->query->get("max_user");
        $tools = $this->lowUsageTool->getLowUsageTool();

        if ($maxUser !== null) {
            $tools = array_filter($tools["data"], function ($item) use ($maxUser) {
                return $item["active_user_count"] <= $maxUser;
            });
        }

        return $this->json([
            $tools,
        ], 200);
    }

    #[Route('/vendor-summary', name: 'vendor-summary', methods: ['GET'])]
    public function findVendorData(Request $request): JsonResponse
    {

        return $this->json([
            $this->vendorData->getVendorsData(),
        ], 200);
    }
}
