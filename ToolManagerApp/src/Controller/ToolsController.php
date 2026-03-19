<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\Tools;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Service\ValidatorResponder;

#[Route('/api')]
final class ToolsController extends AbstractController
{
    public function __construct(
        Private EntityManagerInterface $entityManager,
        Private ValidatorInterface $validator,
        Private ValidatorResponder $validatorResponder,

    )
    {
    }

    #[Route('/tools', name: 'app_tools')]
    public function showAllTools(): JsonResponse
    {

        $tools = $this->entityManager->getRepository(Tools::class)->findAll();
        try {
        return $this->json(
            $tools,
            200,
            [],
            ['groups' => ['tool:list']]
        );

        }catch (){

        }


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
            return $this->json([
                'error' => $exception->getMessage(),
                'message' => "tool with id {$id} not found",
                404]);
        }
    }

    #[Route('/tool/new', methods: ['POST'])]
    public function addNewTool(Request $request, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $tool = $serializer->deserialize($request->getContent(), Tools::class, 'json');

        if (isset($data['category'])){
        $category = $this->entityManager->getRepository(Categories::class)->findOneBy(['name' => $data['category']]);
        $tool->setCategory($category);
        }

        $this->validatorResponder->validate($tool);

        $this->entityManager->persist($tool);
        $this->entityManager->flush();

        return $this->json($tool, 201);
    }
    #[Route('/tool/update/{id}', methods: ['PUT'])]
    public function updateTool(int $id, Request $request, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $tool = $this->entityManager->getRepository(Tools::class)->find($id);
        if (!$tool) {
            throw new NotFoundHttpException("Tool not found");
        }

        $serializer->deserialize($request->getContent(), Tools::class, 'json', ['object_to_populate' => $tool]
        );

        if (isset($data['category'])){
            $category = $this->entityManager->getRepository(Categories::class)->findOneBy(['name' => $data['category']]);
            $tool->setCategory($category);
        }

        $this->validatorResponder->validate($tool);

        $this->entityManager->flush();

        return $this->json([
            'tool' => $tool,
            'status' => 'updated',
        ], 201);
    }

}
