<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\Tools;
use App\Enums\DepartmentType;
use App\Enums\ToolStatusType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api')]
final class ToolsController extends AbstractController
{
    public function __construct(
        Private EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route('/tools', name: 'app_tools')]
    public function showAllTools(): JsonResponse
    {
        $tools = $this->entityManager->getRepository(Tools::class)->findAll();

        return $this->json(
            $tools,
            200,
            [],
            ['groups' => ['tool:list']]
        );
    }

    #[Route('/tools/filter', name: 'app_tools_filter', methods: ['GET'])]
    public function filteredTools(Request $request): JsonResponse
    {
        try {
        $department = $request->query->get('ownerDepartment');
        $status = $request->query->get('status');
        $category = $request->query->get('category');
        $minCost = $request->query->get('minCost');
        $maxCost = $request->query->get('maxCost');
        $tools = $this->entityManager->getRepository(Tools::class)->findByFilters($department, $status, $category, $minCost, $maxCost);

        return $this->json(
            $tools,
            200,
            [],
            ['groups' => ['tool:list']]
        );


        }catch (\InvalidArgumentException $exception){
            return $this->json(['error' => $exception->getMessage(),400]);
        }catch (\Doctrine\DBAL\Exception $exception){
            return $this->json(['error' => "Error database",500]);
        }
    }

    #[Route('/tool/{id}', name: 'app_tool', methods: ['GET'])]
    public function showOneTool(int $id): JsonResponse
    {
        try {
            $tool = $this->entityManager->getRepository(Tools::class)->find($id);
            if (!$tool) {
                throw new NotFoundHttpException("Tool not found");
            }

            return $this->json($tool);

        }catch (NotFoundHttpException $exception){
            return $this->json(['error' => $exception->getMessage(),404]);
        }
    }

    #[Route('/tool/new', methods: ['POST'])]
    public function addNewTool(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $departmentValue = $data['ownerDepartment'] ?? null;
        $statusValue = $data['status'] ?? null;
        $categoryValue = $data['category'] ?? null;

        $department = DepartmentType::tryFrom($departmentValue);
        $status = ToolStatusType::tryFrom($statusValue);

        $category = $this->entityManager->getRepository(Categories::class)->findOneBy(['name'=> $categoryValue]);

        $tool = new Tools();
        $tool->setName($data['name']);
        $tool->setDescription($data['description'] ?? null);
        $tool->setMonthlyCost((float) ($data['monthlyCost'] ?? 0));
        $tool->setActiveUserCount((int) ($data['activeUserCount'] ?? 0));
        $tool->setVendor($data['vendor'] ?? null);
        $tool->setOwnerDepartment($department);
        $tool->setStatus($status);
        $tool->setWebsiteUrl($data['websiteUrl'] ?? null);
        $tool->setCategory($category);

        $this->entityManager->persist($tool);
        $this->entityManager->flush();

        return $this->json($tool, 201);
    }

}
