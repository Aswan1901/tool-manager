<?php

namespace App\Controller;

use App\Entity\Tools;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    //filtré par département et statut
    #[Route('/tools/filter', name: 'app_tools_filter', methods: ['GET'])]
    public function filteredTools(Request $request): JsonResponse
    {
        $department = $request->query->get('ownerDepartment');
        $status = $request->query->get('status');
        $tools = $this->entityManager->getRepository(Tools::class)->findByFilters($department, $status);

        return $this->json(
            $tools,
            200,
            [],
            ['groups' => ['tool:list']]
        );

    }
}
