<?php

namespace App\Controller;

use App\Entity\Tools;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;


#[Route('/api')]
final class ToolsController extends AbstractController
{
    public function __construct(
        Private EntityManagerInterface $entityManager,
        Private SerializerInterface $serializer,

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
    #[Route('/tools/filter', name: 'app_tools_filter')]
    public function filteredTools(): JsonResponse
    {
        $tools = $this->entityManager->getRepository(Tools::class)->findAll();

        return $this->json(
            $tools,
            200,
            [],
            ['groups' => ['tool:list']]
        );

    }
}
