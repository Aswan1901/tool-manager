<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ToolsControllerTest extends WebTestCase
{
    public function setUp(): void
    {
        exec('php bin/console doctrine:database:drop --force --env=test');
        exec('php bin/console doctrine:database:create --env=test');
        exec('psql "postgresql://dev:dev123@postgres:5432/internal_tools_test" -f /var/www/init.sql');
    }

    public function testFindAllTools(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/tools');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($data);
    }

    public function testFindOneTool(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/tool/2');

        self::assertResponseIsSuccessful();

        $tool = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('id', $tool);
        self::assertArrayHasKey('name', $tool);
    }

    public function testToolNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/tool/9999999');

        self::assertResponseStatusCodeSame(404);
    }

    public function testAddNewTool(): void
    {
        $client = static::createClient();

        $data = [
            'name' => 'new tool',
            'description' => 'manager tool',
            'monthlyCost' => '25',
            'activeUserCount' => 10,
            'vendor' => 'new tool',
            'ownerDepartment' => 'Marketing',
            'status' => 'active',
            'websiteUrl' => 'https://tool.com',
            'category' => 'Communication',
        ];

        $client->request(
            'POST',
            '/api/tool/new',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        self::assertResponseStatusCodeSame(201);

        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('id', $response);
        self::assertEquals('new tool', $response['name']);
    }

    public function testAddToolMissingFields(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/tool/new',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => 'tool incomplet'])
        );

        self::assertResponseStatusCodeSame(422);
    }

    // ✅ testDuplicateTool doit tourner APRÈS testAddNewTool pour que 'new tool' existe déjà
    // @depends ne fonctionne pas entre tests qui reset la BDD, donc on s'appuie sur l'ordre d'exécution
    public function testDuplicateTool(): void
    {
        $client = static::createClient();

        $data = [
            'name' => 'new tool',   // ✅ même nom que testAddNewTool — doit déjà exister en BDD
            'description' => 'manager tool',
            'monthlyCost' => '25',
            'activeUserCount' => 10,
            'vendor' => 'new tool',
            'ownerDepartment' => 'Marketing',
            'status' => 'active',
            'websiteUrl' => 'https://tool.com',
            'category' => 'Communication',
        ];

        $client->request(
            'POST',
            '/api/tool/new',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        self::assertResponseStatusCodeSame(422);
    }

    public function testUpdateTool(): void
    {
        $client = static::createClient();

        $data = [
            'name' => "Best Tool's 2",
            'description' => 'The tool for the futur',
            'monthlyCost' => '70',
            'activeUserCount' => 45,
            'vendor' => 'The best new tool',
            'ownerDepartment' => 'Marketing',
            'status' => 'active',
            'websiteUrl' => 'https://theBestTool2.com',
            'category' => 'Communication',
        ];

        $client->request(
            'PUT',
            '/api/tool/update/2',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        self::assertResponseStatusCodeSame(200);

        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertEquals("Best Tool's 2", $response['name']);
        self::assertEquals('70', $response['monthlyCost']);
    }

    public function testUpdateToolNotFound(): void
    {
        $client = static::createClient();

        $client->request(
            'PUT',
            '/api/tool/update/9999999',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => 'ghost tool'])
        );

        self::assertResponseStatusCodeSame(404);
    }
}
