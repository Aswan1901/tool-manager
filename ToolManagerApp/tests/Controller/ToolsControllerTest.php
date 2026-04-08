<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ToolsControllerTest extends WebTestCase
{
    protected function setUp(): void
    {
        exec('symfony console doctrine:database:drop --force --env=test');
        exec('symfony console doctrine:database:create --env=test');
        exec('psql "postgresql://dev:dev123@postgres:5432/internal_tools_test" -f /var/www/init.sql');
    }

    public function testFindAllTools(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/tools');

        self::assertResponseIsSuccessful();
    }

    public function testFindOneTool(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/tool/2');

        self::assertResponseIsSuccessful();
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
            'name'             => 'new tool',
            'description'      => 'manager tool',
            'monthlyCost'      => '25',
            'activeUserCount'  => 10,
            'vendor'           => 'new tool',
            'ownerDepartment'  => 'Marketing',
            'status'           => 'active',
            'websiteUrl'       => 'https://tool.com',
            'category'         => 'Communication',
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
    }

    public function testDuplicateTool(): void
    {
        $client = static::createClient();

        $data = [
            'name'             => 'new tool',
            'description'      => 'manager tool',
            'monthlyCost'      => '25',
            'activeUserCount'  => 10,
            'vendor'           => 'new tool',
            'ownerDepartment'  => 'Marketing',
            'status'           => 'active',
            'websiteUrl'       => 'https://tool.com',
            'category'         => 'Communication',
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
            "name"=> "Best Tool's 2",
            "description"=> "The tool for the futur",
            "monthlyCost"=> "70",
            "activeUserCount"=> 45,
            "vendor"=> "The best new tool",
            "ownerDepartment"=> "Marketing",
            "status"=> "active",
            "websiteUrl"=> "https://theBestTool2.com",
            "category"=> "Communication",
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
    }
}
