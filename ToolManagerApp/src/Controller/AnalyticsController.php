<?php

namespace App\Controller;

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
use OpenApi\Attributes as OA;

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
    #[OA\Get(
        path: '/api/analytics/department-costs',
        summary: 'Budget par département',
        description: 'Retourne le coût total par département. Filtrable par ordre croissant (asc) ou décroissant (desc).',
        tags: ['Analytics']
    )]
    #[OA\Parameter(
        name: 'sortByTotalCost',
        in: 'query',
        required: false,
        description: 'Trier par coût total. Valeurs acceptées : asc, desc.',
        schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])
    )]
    #[OA\Response(
        response: 200,
        description: 'Budget par département avec résumé global',
        content: new OA\JsonContent(properties: [
            new OA\Property(
                property: 'data analytics',
                type: 'object',
                additionalProperties: new OA\AdditionalProperties(
                    properties: [
                        new OA\Property(property: 'total_cost', type: 'number', example: 6.19),
                        new OA\Property(property: 'total_count', type: 'integer', example: 1),
                        new OA\Property(property: 'total_users', type: 'integer', example: 3),
                        new OA\Property(property: 'average_cost_per_tool', type: 'number', example: 6.19),
                        new OA\Property(property: 'percentage_cost', type: 'number', example: 0.56),
                    ],
                    type: 'object'
                )
            ),
            new OA\Property(
                property: 'summary',
                type: 'object',
                properties: [
                    new OA\Property(property: 'total_company_cost', type: 'number', example: 1115.14),
                    new OA\Property(property: 'department_count', type: 'integer', example: 6),
                    new OA\Property(property: 'most_expensive_department', type: 'string', example: 'Marketing'),
                ]
            ),
        ])
    )]
    public function analytics(Request $request): JsonResponse
    {

        $sortByTotalCost = $request->query->get("sortByTotalCost");
        $data = $this->dataAnalytics->getDataAnalytics();

        $dataAnalytics = $data['dataAnalytics'];
        $summary = $data['summary'];

        if ($sortByTotalCost === 'asc') {

            uasort($dataAnalytics, function ($a, $b) {
                return $a["total_cost"] <=> $b["total_cost"];
            });

        } elseif ($sortByTotalCost === 'desc') {
            uasort($dataAnalytics, function ($a, $b) {
                return $b["total_cost"] <=> $a["total_cost"];
            });
        }

        return $this->json([
            "data analytics" => $dataAnalytics,
            "summary" => $summary,
        ], 200);
    }

    #[Route('/expensive-tool', name: 'expensive-tool')]
    #[OA\Get(
        path: '/api/analytics/expensive-tool',
        summary: 'Outils les plus coûteux',
        description: 'Retourne les outils triés par coût mensuel décroissant. Filtrable par coût minimum et limitable en nombre de résultats.',
        tags: ['Analytics']
    )]
    #[OA\Parameter(
        name: 'limit',
        in: 'query',
        required: false,
        description: 'Nombre maximum de résultats. Défaut : 10. Doit être supérieur à 0.',
        schema: new OA\Schema(type: 'integer', default: 10, minimum: 1)
    )]
    #[OA\Parameter(
        name: 'min_cost',
        in: 'query',
        required: false,
        description: 'Filtre les outils dont le coût mensuel est supérieur ou égal à cette valeur.',
        schema: new OA\Schema(type: 'number', format: 'float')
    )]
    #[OA\Response(
        response: 200,
        description: 'Liste des outils coûteux avec analyse globale',
        content: new OA\JsonContent(properties: [
            new OA\Property(
                property: 'data',
                type: 'array',
                items: new OA\Items(properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 21),
                    new OA\Property(property: 'name', type: 'string', example: 'Specialized Analytics Pro'),
                    new OA\Property(property: 'monthly_cost', type: 'string', example: '159.99'),
                    new OA\Property(property: 'active_users_count', type: 'integer', example: 4),
                    new OA\Property(property: 'cost_per_user', type: 'number', example: 40),
                    new OA\Property(property: 'department', type: 'string', example: 'Marketing'),
                    new OA\Property(property: 'vendor', type: 'string', example: 'DataCorp'),
                    new OA\Property(
                        property: 'efficiency_rating',
                        type: 'string',
                        enum: ['low', 'medium', 'high'],
                        example: 'low'
                    ),
                ])
            ),
            new OA\Property(
                property: 'analysis',
                type: 'object',
                properties: [
                    new OA\Property(property: 'total_tools_analyzed', type: 'integer', example: 30),
                    new OA\Property(property: 'avg_cost_per_user_company', type: 'number', example: 8.38),
                    new OA\Property(property: 'potential_savings_identified', type: 'number', example: 904.98),
                ]
            ),
        ])
    )]
    #[OA\Response(
        response: 400,
        description: 'Limite invalide (≤ 0)',
        content: new OA\JsonContent(properties: [
            new OA\Property(property: 'error', type: 'string', example: 'La limite doit être supérieure à zéro'),
        ])
    )]
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
    #[OA\Get(
        path: '/api/analytics/tools-by-category',
        summary: 'Outils par catégorie',
        description: 'Retourne les outils regroupés par catégorie avec leur coût total, nombre d\'utilisateurs et pourcentage du budget.',
        tags: ['Analytics']
    )]
    #[OA\Response(
        response: 200,
        description: 'Liste des catégories avec résumé global',
        content: new OA\JsonContent(properties: [
            new OA\Property(
                property: 'data',
                type: 'object',
                properties: [
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(properties: [
                            new OA\Property(property: 'category_name', type: 'string', example: 'Communication'),
                            new OA\Property(property: 'tools_count', type: 'integer', example: 9),
                            new OA\Property(property: 'total_cost', type: 'number', example: 417.99),
                            new OA\Property(property: 'total_users', type: 'integer', example: 225),
                            new OA\Property(property: 'percentage_of_budget', type: 'number', example: 37.48),
                            new OA\Property(property: 'average_cost_per_user', type: 'number', example: 1.86),
                        ])
                    ),
                    new OA\Property(
                        property: 'summary',
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'total_company_cost', type: 'number', example: 1115.14),
                            new OA\Property(property: 'departments_count', type: 'integer', example: 6),
                            new OA\Property(property: 'most_expensive_department', type: 'string', example: 'Marketing'),
                            new OA\Property(property: 'most_expensive_department_cost', type: 'number', example: 622.98),
                        ]
                    ),
                ]
            ),
        ])
    )]
    public function toolsByCategory(): JsonResponse
    {
        return $this->json([
            "data" => $this->toolsByCategory->findToolsByCategory()
        ], 200);
    }

    #[Route('/low-usage-tools', name: 'low-usage-tool', methods: ['GET'])]
    #[OA\Get(
        path: '/api/analytics/low-usage-tools',
        summary: 'Outils peu utilisés',
        description: 'Identifie les outils avec un faible nombre d\'utilisateurs actifs. Filtrable par nombre maximum d\'utilisateurs.',
        tags: ['Analytics']
    )]
    #[OA\Parameter(
        name: 'max_users',
        in: 'query',
        required: false,
        description: 'Filtre les outils ayant au maximum N utilisateurs actifs.',
        schema: new OA\Schema(type: 'integer', example: 3)
    )]
    #[OA\Response(
        response: 200,
        description: 'Liste des outils sous-utilisés avec analyse des économies potentielles',
        content: new OA\JsonContent(properties: [
            new OA\Property(
                property: 'data',
                type: 'array',
                items: new OA\Items(properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 7),
                    new OA\Property(property: 'name', type: 'string', example: 'Postman'),
                    new OA\Property(property: 'monthly_cost', type: 'string', example: '12.00'),
                    new OA\Property(property: 'active_users_count', type: 'integer', example: 5),
                    new OA\Property(property: 'cost_per_user', type: 'number', example: 2.4),
                    new OA\Property(property: 'vendor', type: 'string', example: 'Postman Inc.'),
                    new OA\Property(
                        property: 'warning_level',
                        type: 'string',
                        enum: ['low', 'medium', 'high'],
                        example: 'low'
                    ),
                    new OA\Property(property: 'potential_actions', type: 'string', example: 'Monitor usage trends'),
                ])
            ),
            new OA\Property(
                property: 'savings_analysis',
                type: 'object',
                properties: [
                    new OA\Property(property: 'total_underutilized_tools', type: 'string', example: 'Premium Design Tools'),
                    new OA\Property(property: 'potential_monthly_savings', type: 'number', example: 469.98),
                    new OA\Property(property: 'potential_annual_savings', type: 'number', example: 5639.76),
                ]
            ),
        ])
    )]
    public function findLowUsageTool(Request $request): JsonResponse
    {
        $maxUsers = $request->query->get('max_users');
        $tools = $this->lowUsageTool->getLowUsageTool();
        $data = $tools['data'];

        if ($maxUsers !== null) {
            $maxUsers = (int) $maxUsers;
            $data = array_values(array_filter($data, function (array $item) use ($maxUsers) {
                return $item['active_users_count'] <= $maxUsers;
            }));
        }

        return $this->json([
            'data' => $data,
            'savings_analysis' => $tools['savings_analysis'],
        ], 200);
    }

    #[Route('/vendor-summary', name: 'vendor-summary', methods: ['GET'])]
    #[OA\Get(
        path: '/api/analytics/vendor-summary',
        summary: 'Synthèse par fournisseur',
        description: 'Retourne les dépenses, le nombre d\'outils et l\'efficacité agrégés par fournisseur.',
        tags: ['Analytics']
    )]
    #[OA\Response(
        response: 200,
        description: 'Liste des fournisseurs avec insights globaux',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(properties: [
                        new OA\Property(property: 'vendor', type: 'string', example: 'Amazon Web Services'),
                        new OA\Property(property: 'tools_count', type: 'integer', example: 1),
                        new OA\Property(property: 'total_monthly_cost', type: 'number', example: 150),
                        new OA\Property(property: 'total_users', type: 'integer', example: 2),
                        new OA\Property(property: 'departments', type: 'string', example: 'Operations,Marketing'),
                        new OA\Property(property: 'average_cost_per_user', type: 'number', example: 75),
                        new OA\Property(
                            property: 'vendor_efficiency',
                            type: 'string',
                            enum: ['excellent', 'good', 'average', 'poor'],
                            example: 'poor'
                        ),
                    ])
                ),
                new OA\Property(
                    property: 'vendor_insights',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'most_expensive_vendor', type: 'string', example: 'The best new tool'),
                        new OA\Property(property: 'most_efficient_vendor', type: 'string', example: 'DesignPro'),
                        new OA\Property(property: 'single_tool_vendors', type: 'integer', example: 19),
                    ]
                ),
            ])
        )
    )]
    public function findVendorData(Request $request): JsonResponse
    {
        return $this->json([
            $this->vendorData->getVendorsData(),
        ], 200);
    }
}
