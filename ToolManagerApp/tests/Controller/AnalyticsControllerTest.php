<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AnalyticsControllerTest extends WebTestCase
{
    public function setUp(): void
    {
        exec('php bin/console doctrine:database:drop --force --env=test');
        exec('php bin/console doctrine:database:create --env=test');
        exec('psql "postgresql://dev:dev123@postgres:5432/internal_tools_test" -f /var/www/init.sql');
    }

    // ==================== DEPARTMENT COSTS ====================

    public function testDepartmentCosts(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/analytics/department-costs');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('data analytics', $data);
        self::assertArrayHasKey('summary', $data);

        $summary = $data['summary'];
        self::assertArrayHasKey('total_company_cost', $summary);
        self::assertArrayHasKey('department_count', $summary);
        self::assertArrayHasKey('most_expensive_department', $summary);

        // Vérifier la structure d'un département
        $departments = $data['data analytics'];
        $firstDept = array_values($departments)[0];
        self::assertArrayHasKey('totalCost', $firstDept);
        self::assertArrayHasKey('total_count', $firstDept);
        self::assertArrayHasKey('total_users', $firstDept);
        self::assertArrayHasKey('average_cost_per_tool', $firstDept);
        self::assertArrayHasKey('percentage_cost', $firstDept);
    }

    public function testDepartmentCostsSortAsc(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/analytics/department-costs?sortByTotalCost=asc');

        self::assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);
        $departments = array_values($data['data analytics']);

        // Vérifier que les coûts sont bien triés en ordre croissant
        for ($i = 0; $i < count($departments) - 1; $i++) {
            self::assertLessThanOrEqual(
                $departments[$i + 1]['totalCost'],
                $departments[$i]['totalCost'],
                'Les départements ne sont pas triés par coût croissant'
            );
        }
    }

    public function testDepartmentCostsSortDesc(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/analytics/department-costs?sortByTotalCost=desc');

        self::assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);
        $departments = array_values($data['data analytics']);

        // Vérifier que les coûts sont bien triés en ordre décroissant
        for ($i = 0; $i < count($departments) - 1; $i++) {
            self::assertGreaterThanOrEqual(
                $departments[$i + 1]['totalCost'],
                $departments[$i]['totalCost'],
                'Les départements ne sont pas triés par coût décroissant'
            );
        }
    }

    public function testDepartmentCostsInvalidSortIsIgnored(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/analytics/department-costs?sortByTotalCost=invalid');

        // Une valeur invalide doit être ignorée — pas de 400, retour normal
        self::assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('data analytics', $data);
    }

    // ==================== EXPENSIVE TOOL ====================

    public function testExpensiveTool(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/analytics/expensive-tool');

        self::assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('data', $data);
        self::assertArrayHasKey('analysis', $data);

        $analysis = $data['analysis'];
        self::assertArrayHasKey('total_tools_analyzed', $analysis);
        self::assertArrayHasKey('avg_cost_per_user_company', $analysis);
        self::assertArrayHasKey('potential_savings_identified', $analysis);

        // Vérifier la structure du premier outil
        $firstTool = $data['data'][0];
        self::assertArrayHasKey('id', $firstTool);
        self::assertArrayHasKey('name', $firstTool);
        self::assertArrayHasKey('monthly_cost', $firstTool);
        self::assertArrayHasKey('active_users_count', $firstTool);
        self::assertArrayHasKey('cost_per_user', $firstTool);
        self::assertArrayHasKey('department', $firstTool);
        self::assertArrayHasKey('vendor', $firstTool);
        self::assertArrayHasKey('efficiency_rating', $firstTool);
    }

    public function testExpensiveToolDefaultLimit(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/analytics/expensive-tool');

        self::assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);

        // Limite par défaut = 10
        self::assertLessThanOrEqual(10, count($data['data']));
    }

    public function testExpensiveToolCustomLimit(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/analytics/expensive-tool?limit=3');

        self::assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertCount(3, $data['data']);
    }

    public function testExpensiveToolInvalidLimit(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/analytics/expensive-tool?limit=0');

        self::assertResponseStatusCodeSame(400);

        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('error', $data);
        self::assertEquals('La limite doit être supérieure à zéro', $data['error']);
    }

    public function testExpensiveToolMinCost(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/analytics/expensive-tool?min_cost=100');

        self::assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);

        foreach ($data['data'] as $tool) {
            self::assertGreaterThanOrEqual(
                100,
                (float) $tool['monthly_cost'],
                "L'outil {$tool['name']} a un coût inférieur au min_cost"
            );
        }
    }

    public function testExpensiveToolMinCostNoResult(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/analytics/expensive-tool?min_cost=999999');

        self::assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertEmpty($data['data']);
    }

    // ==================== TOOLS BY CATEGORY ====================

    public function testToolsByCategory(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/analytics/tools-by-category');

        self::assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('data', $data);
        self::assertArrayHasKey('data', $data['data']);
        self::assertArrayHasKey('summary', $data['data']);

        // Vérifier la structure d'une catégorie
        $firstCategory = $data['data']['data'][0];
        self::assertArrayHasKey('category_name', $firstCategory);
        self::assertArrayHasKey('tools_count', $firstCategory);
        self::assertArrayHasKey('total_cost', $firstCategory);
        self::assertArrayHasKey('total_users', $firstCategory);
        self::assertArrayHasKey('percentage_of_budget', $firstCategory);
        self::assertArrayHasKey('average_cost_per_user', $firstCategory);

        // Vérifier le summary
        $summary = $data['data']['summary'];
        self::assertArrayHasKey('total_company_cost', $summary);
        self::assertArrayHasKey('departments_count', $summary);
        self::assertArrayHasKey('most_expensive_department', $summary);
        self::assertArrayHasKey('most_expensive_department_cost', $summary);
    }

    public function testToolsByCategoryPercentageSum(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/analytics/tools-by-category');

        $data = json_decode($client->getResponse()->getContent(), true);
        $categories = $data['data']['data'];

        $totalPercentage = array_sum(array_column($categories, 'percentage_of_budget'));

        // La somme des pourcentages doit être proche de 100%
        self::assertEqualsWithDelta(100, $totalPercentage, 0.1, 'La somme des pourcentages doit être ~100%');
    }

    // ==================== LOW USAGE TOOLS ====================

    public function testLowUsageTools(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/analytics/low-usage-tools');

        self::assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);

        self::assertArrayHasKey('data', $data);
        self::assertArrayHasKey('savings_analysis', $data);

        $savingsAnalysis = $data['savings_analysis'];
        self::assertArrayHasKey('total_underutilized_tools', $savingsAnalysis);
        self::assertArrayHasKey('potential_monthly_savings', $savingsAnalysis);
        self::assertArrayHasKey('potential_annual_savings', $savingsAnalysis);

        // Vérifier la structure du premier outil
        $firstTool = $data['data'][0];
        self::assertArrayHasKey('id', $firstTool);
        self::assertArrayHasKey('name', $firstTool);
        self::assertArrayHasKey('monthly_cost', $firstTool);
        self::assertArrayHasKey('active_users_count', $firstTool);
        self::assertArrayHasKey('cost_per_user', $firstTool);
        self::assertArrayHasKey('vendor', $firstTool);
        self::assertArrayHasKey('warning_level', $firstTool);
        self::assertArrayHasKey('potential_actions', $firstTool);
    }

    public function testLowUsageToolsMaxUsers(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/analytics/low-usage-tools?max_users=3');

        self::assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);

        // Tous les outils retournés doivent avoir <= 3 utilisateurs actifs
        foreach ($data['data'] as $tool) {
            self::assertLessThanOrEqual(
                3,
                $tool['active_users_count'],
                "L'outil {$tool['name']} dépasse le max_users"
            );
        }
    }

    public function testLowUsageToolsMaxUsersZero(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/analytics/low-usage-tools?max_users=0');

        self::assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);

        // Seuls les outils sans aucun utilisateur actif
        foreach ($data['data'] as $tool) {
            self::assertEquals(0, $tool['active_users_count']);
        }
    }

    public function testLowUsageToolsAnnualSavingsConsistency(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/analytics/low-usage-tools');

        $data = json_decode($client->getResponse()->getContent(), true);
        $savings = $data['savings_analysis'];

        self::assertEqualsWithDelta(
            $savings['potential_monthly_savings'] * 12,
            $savings['potential_annual_savings'],
            0.01,
            'Les économies annuelles ne correspondent pas aux économies mensuelles × 12'
        );
    }

    // ==================== VENDOR SUMMARY ====================

    public function testVendorSummary(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/analytics/vendor-summary');

        self::assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);

        // La réponse est un tableau contenant un objet
        self::assertIsArray($data);
        self::assertArrayHasKey('data', $data[0]);
        self::assertArrayHasKey('vendor_insights', $data[0]);

        // Vérifier la structure d'un vendor
        $firstVendor = $data[0]['data'][0];
        self::assertArrayHasKey('vendor', $firstVendor);
        self::assertArrayHasKey('tools_count', $firstVendor);
        self::assertArrayHasKey('total_monthly_cost', $firstVendor);
        self::assertArrayHasKey('total_users', $firstVendor);
        self::assertArrayHasKey('departments', $firstVendor);
        self::assertArrayHasKey('average_cost_per_user', $firstVendor);
        self::assertArrayHasKey('vendor_efficiency', $firstVendor);

        // Vérifier les insights
        $insights = $data[0]['vendor_insights'];
        self::assertArrayHasKey('most_expensive_vendor', $insights);
        self::assertArrayHasKey('most_efficient_vendor', $insights);
        self::assertArrayHasKey('single_tool_vendors', $insights);
    }

    public function testVendorEfficiencyValues(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/analytics/vendor-summary');

        $data = json_decode($client->getResponse()->getContent(), true);
        $vendors = $data[0]['data'];

        $validEfficiencies = ['excellent', 'good', 'average', 'poor'];

        foreach ($vendors as $vendor) {
            self::assertContains(
                $vendor['vendor_efficiency'],
                $validEfficiencies,
                "Valeur vendor_efficiency invalide : {$vendor['vendor_efficiency']}"
            );
        }
    }
}
