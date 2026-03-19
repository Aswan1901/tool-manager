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
        try {
        $tools = $this->entityManager->getRepository(Tools::class)->findAll();
        return $this->json(
            $tools,
            200,
            [],
            ['groups' => ['tool:list']]
        );

        }catch (\Exception $e){
            error_log($e->getMessage());
            return $this->json(
                ['error' => $e->getMessage()],
                500
            );
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
            return $this->json(['error' => $exception->getMessage(),500]);
        }
    }

    #[Route('/tool/{id}', name: 'app_tool', methods: ['GET'])]
    public function showOneTool(int $id): JsonResponse
    {
        try {
            $tool = $this->entityManager->getRepository(Tools::class)->find($id);
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
        try {

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
        }catch (\InvalidArgumentException $exception){

            return $this->json(['error' => $exception->getMessage(),422]);

        }catch (\Exception $exception){

            return $this->json(
                [
                    'error' => $exception->getMessage(),
                    500
                ]);
        }
    }
    #[Route('/tool/update/{id}', methods: ['PUT'])]
    public function updateTool(int $id, Request $request, SerializerInterface $serializer): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $tool = $this->entityManager->getRepository(Tools::class)->find($id);
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

        }catch (\InvalidArgumentException $exception){
            return $this->json(['error' => $exception->getMessage(),422]);

        }catch (NotFoundHttpException $exception){
            return $this->json([
                'error' => $exception->getMessage(),
                'message' => "tool with id {$id} not found",
                404]);
        }
    }
}
