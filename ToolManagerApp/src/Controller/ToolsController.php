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

    #[Route('/tools', name: 'app_tools', methods: ['GET'])]
    #[OA\Get(
        path: '/api/tools',
        name: 'récupérer les outils',
        tags: ['tools'],
    )]
    #[OA\Response(
        response:200,
        description: 'liste les outils',
    )]
    #[OA\Response(
        response:500,
        description: 'Erreur Serveur',
    )]
    public function showAllTools(): JsonResponse
    {
        try {
        $tools = $this->entityManager->getRepository(Tools::class)->findAll();
        return $this->json(
            $tools,
            200,
            [],
            ['groups' => ['tool:list'],]
        );
        }catch (\Exception $e){
            return $this->json(["error" => "Internal server error",
            "message"=>"Database connection failed"
            ], 500);
        }
    }

    #[Route('/tools/filter', name: 'app_tools_filter', methods: ['GET'])]
    #[OA\Get(
        path: '/api/tools/filter',
        summary: 'Filtrer les outils',
        tags: ['Tools']
    )]
    #[OA\Parameter(name: 'ownerDepartment', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'Finance')]
    #[OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'active')]
    #[OA\Parameter(name: 'category', in: 'query', required: false, schema: new OA\Schema(type: 'string'), example: 'Communication')]
    #[OA\Parameter(name: 'minCost', in: 'query', required: false, schema: new OA\Schema(type: 'number', format: 'float'), example: 10)]
    #[OA\Parameter(name: 'maxCost', in: 'query', required: false, schema: new OA\Schema(type: 'number', format: 'float'), example: 100)]
    #[OA\Response(
        response: 200,
        description: 'Résultat du filtrage'
    )]
    #[OA\Response(
        response: 400,
        description: 'Paramètres invalides'
    )]
    #[OA\Response(
        response: 500,
        description: 'Erreur base de données ou serveur'
    )]
    public function filteredTools(Request $request): JsonResponse
    {
        try {
            $department = $request->query->get('ownerDepartment');
            $status = $request->query->get('status');
            $category = $request->query->get('category');
            $minCost = $request->query->get('min_cost');
            $maxCost = $request->query->get('max_cost');

            $tools = $this->entityManager->getRepository(Tools::class)->findByFilters(
                $department, $status, $category, $minCost, $maxCost
            );
            $data = [
                "data" => $tools,
                "total"=>count($tools),
                "filtered_applied"=>array_filter([
                    'ownerDepartment' => $department,
                    'status' => $status,
                    'category' => $category,
                    'min_cost' => $minCost,
                    'max_cost' => $maxCost,
                ])
            ];


            return $this->json($data, 200, [], ['groups' => ['tool:list']]);

        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        } catch (\Doctrine\DBAL\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/tool/{id}', name: 'app_tool', methods: ['GET'])]
    #[OA\Get(
        path: '/api/tool/{id}',
        summary: 'Récupérer un outil par son ID',
        tags: ['Tools']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer'),
        example: 1
    )]
    #[OA\Response(
        response: 200,
        description: 'Outil trouvé'
    )]
    #[OA\Response(
        response: 404,
        description: 'Outil non trouvé'
    )]
    public function showOneTool(int $id): JsonResponse
    {
        try {
            $tool = $this->entityManager->getRepository(Tools::class)->find($id);
            if (!$tool) {
                throw new NotFoundHttpException();
            }
            return $this->json($tool);

        }catch (NotFoundHttpException $exception){
            return $this->json([
                'error' => "Tool not found",
                'message' => "Tool with id $id does not exist",
                ],404);
        }catch (\Exception $e){
            return $this->json(["error" => $e->getMessage()], 500);
        }
    }

    #[Route('/tool/new', name: 'app_new_tool', methods: ['POST'])]
    #[OA\Post(
        path: '/api/tool/new',
        summary: 'Créer un nouvel outil',
        tags: ['Tools']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'New Tool'),
                new OA\Property(property: 'description', type: 'string', example: 'This is a description'),
                new OA\Property(property: 'status', type: 'string', example: 'active'),
                new OA\Property(property: 'ownerDepartment', type: 'string', example: 'Sales'),
                new OA\Property(property: 'cost', type: 'number', format: 'float', example: 89.99),
                new OA\Property(property: 'category', type: 'string', example: 'Finance')
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Outil créé avec succès'
    )]
    #[OA\Response(
        response: 422,
        description: 'Erreur de validation'
    )]
    #[OA\Response(
        response: 500,
        description: 'Erreur serveur'
    )]
    public function addNewTool(Request $request, SerializerInterface $serializer): JsonResponse
    {
        try {

        $data = json_decode($request->getContent(), true);
        $tool = $serializer->deserialize($request->getContent(), Tools::class, 'json');

        if (isset($data['category'])){
        $category = $this->entityManager->getRepository(Categories::class)->findOneBy(['name' => $data['category']]);
        $tool->setCategory($category);
        }

        // vérifie s'il y a des erreurs pour les afficher
        $response = $this->validatorResponder->validate($tool);
        if ($response !== null) {
            return $response;
        }


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
    #[Route('/tool/update/{id}', name: "app_update_tool",methods: ['PUT'])]
    #[OA\Put(
        path: '/api/tool/update/{id}',
        summary: 'Mettre à jour un outil',
        tags: ['Tools']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer'),
        example: 1
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Outil version Pro'),
                new OA\Property(property: 'description', type: 'string', example: 'ceci est une description'),
                new OA\Property(property: 'status', type: 'string', example: 'revoked'),
                new OA\Property(property: 'ownerDepartment', type: 'string', example: 'Design'),
                new OA\Property(property: 'cost', type: 'number', format: 'float', example: 99.99),
                new OA\Property(property: 'category', type: 'string', example: 'Analytics')
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Outil mis à jour'
    )]
    #[OA\Response(
        response: 404,
        description: 'Outil non trouvé'
    )]
    #[OA\Response(
        response: 422,
        description: 'Erreur de validation'
    )]
    public function updateTool(int $id, Request $request, SerializerInterface $serializer): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $tool = $this->entityManager->getRepository(Tools::class)->find($id);
            $serializer->deserialize($request->getContent(), Tools::class, 'json', ['object_to_populate' => $tool]
            );

            if (!$tool){
                throw new NotFoundHttpException("tool with id {$id} not found");
            }

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
            return $this->json(['error' => $exception->getMessage()],422);

        }catch (NotFoundHttpException $exception){
            return $this->json([
                'error' => $exception->getMessage(),
                'message' => "tool with id {$id} not found",
                ],404);
        }catch (\Exception $exception){
            return $this->json(['error' => $exception->getMessage()],500);
        }
    }
}
