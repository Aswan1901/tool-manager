<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\Tools;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Service\ValidatorResponder;

#[Route('/api')]
#[OA\Tag(name: 'Tools')]
final class ToolsController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private ValidatorResponder $validatorResponder,
    ) {
    }

    #[Route('/tools', name: 'app_tools', methods: ['GET'])]
    #[OA\Get(
        path: '/api/tools',
        summary: 'Récupérer tous les outils',
        description: 'Retourne la liste complète de tous les outils enregistrés en base de données.',
        tags: ['Tools']
    )]
    #[OA\Response(
        response: 200,
        description: 'Liste des outils récupérée avec succès',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'name', type: 'string', example: 'Slack'),
                    new OA\Property(property: 'description', type: 'string', example: 'Outil de communication'),
                    new OA\Property(property: 'status', type: 'string', example: 'active'),
                    new OA\Property(property: 'ownerDepartment', type: 'string', example: 'Marketing'),
                    new OA\Property(property: 'cost', type: 'number', format: 'float', example: 12.99),
                    new OA\Property(property: 'category', type: 'string', example: 'Communication'),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Erreur serveur interne',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Internal server error'),
                new OA\Property(property: 'message', type: 'string', example: 'Database connection failed'),
            ]
        )
    )]
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
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Internal server error',
                'message' => 'Database connection failed',
            ], 500);
        }
    }

    #[Route('/tools/filter', name: 'app_tools_filter', methods: ['GET'])]
    #[OA\Get(
        path: '/api/tools/filter',
        summary: 'Filtrer les outils',
        description: 'Retourne les outils correspondant aux filtres fournis en query string. Tous les paramètres sont optionnels et cumulables.',
        tags: ['Tools']
    )]
    #[OA\Parameter(
        name: 'ownerDepartment',
        in: 'query',
        required: false,
        description: 'Département propriétaire de l\'outil',
        schema: new OA\Schema(type: 'string'),
        example: 'Finance'
    )]
    #[OA\Parameter(
        name: 'status',
        in: 'query',
        required: false,
        description: 'Statut de l\'outil (active, inactive, revoked)',
        schema: new OA\Schema(type: 'string', enum: ['active', 'inactive', 'revoked']),
        example: 'active'
    )]
    #[OA\Parameter(
        name: 'category',
        in: 'query',
        required: false,
        description: 'Nom de la catégorie',
        schema: new OA\Schema(type: 'string'),
        example: 'Communication'
    )]
    #[OA\Parameter(
        name: 'min_cost',
        in: 'query',
        required: false,
        description: 'Coût minimum (inclus)',
        schema: new OA\Schema(type: 'number', format: 'float'),
        example: 10.00
    )]
    #[OA\Parameter(
        name: 'max_cost',
        in: 'query',
        required: false,
        description: 'Coût maximum (inclus)',
        schema: new OA\Schema(type: 'number', format: 'float'),
        example: 100.00
    )]
    #[OA\Response(
        response: 200,
        description: 'Résultat du filtrage',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'name', type: 'string', example: 'Slack'),
                            new OA\Property(property: 'status', type: 'string', example: 'active'),
                            new OA\Property(property: 'ownerDepartment', type: 'string', example: 'Finance'),
                            new OA\Property(property: 'cost', type: 'number', format: 'float', example: 12.99),
                            new OA\Property(property: 'category', type: 'string', example: 'Communication'),
                        ]
                    )
                ),
                new OA\Property(property: 'total', type: 'integer', example: 5),
                new OA\Property(
                    property: 'filters_applied',
                    type: 'object',
                    example: ['ownerDepartment' => 'Finance', 'status' => 'active']
                ),
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Paramètres de filtre invalides',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Valeur de statut invalide'),
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Erreur base de données ou serveur',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Internal server error'),
            ]
        )
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
                'data' => $tools,
                'total' => count($tools),
                'filters_applied' => array_filter([
                    'ownerDepartment' => $department,
                    'status' => $status,
                    'category' => $category,
                    'min_cost' => $minCost,
                    'max_cost' => $maxCost,
                ]),
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
        description: 'Retourne le détail complet d\'un outil à partir de son identifiant.',
        tags: ['Tools']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'Identifiant unique de l\'outil',
        schema: new OA\Schema(type: 'integer'),
        example: 1
    )]
    #[OA\Response(
        response: 200,
        description: 'Outil trouvé',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'Slack'),
                new OA\Property(property: 'description', type: 'string', example: 'Outil de communication d\'équipe'),
                new OA\Property(property: 'status', type: 'string', example: 'active'),
                new OA\Property(property: 'ownerDepartment', type: 'string', example: 'Marketing'),
                new OA\Property(property: 'cost', type: 'number', format: 'float', example: 12.99),
                new OA\Property(property: 'category', type: 'string', example: 'Communication'),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Outil non trouvé',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Tool not found'),
                new OA\Property(property: 'message', type: 'string', example: 'Tool with id 99 does not exist'),
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Erreur serveur interne',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Internal server error'),
            ]
        )
    )]
    public function showOneTool(int $id): JsonResponse
    {
        try {
            $tool = $this->entityManager->getRepository(Tools::class)->find($id);
            if (!$tool) {
                throw new NotFoundHttpException();
            }
            return $this->json($tool);

        } catch (NotFoundHttpException $exception) {
            return $this->json([
                'error' => 'Tool not found',
                'message' => "Tool with id $id does not exist",
            ], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/tool/new', name: 'app_new_tool', methods: ['POST'])]
    #[OA\Post(
        path: '/api/tool/new',
        summary: 'Créer un nouvel outil',
        description: 'Crée un nouvel outil en base de données. Le champ `name` est obligatoire.',
        tags: ['Tools']
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Données du nouvel outil à créer',
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Notion', description: 'Nom de l\'outil (obligatoire)'),
                new OA\Property(property: 'description', type: 'string', example: 'Outil de gestion de projet et de documentation', description: 'Description de l\'outil'),
                new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive', 'revoked'], example: 'active', description: 'Statut de l\'outil'),
                new OA\Property(property: 'ownerDepartment', type: 'string', example: 'Sales', description: 'Département responsable'),
                new OA\Property(property: 'cost', type: 'number', format: 'float', example: 89.99, description: 'Coût mensuel en euros'),
                new OA\Property(property: 'category', type: 'string', example: 'Finance', description: 'Nom de la catégorie existante'),
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Outil créé avec succès',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 42),
                new OA\Property(property: 'name', type: 'string', example: 'Notion'),
                new OA\Property(property: 'description', type: 'string', example: 'Outil de gestion de projet'),
                new OA\Property(property: 'status', type: 'string', example: 'active'),
                new OA\Property(property: 'ownerDepartment', type: 'string', example: 'Sales'),
                new OA\Property(property: 'cost', type: 'number', format: 'float', example: 89.99),
                new OA\Property(property: 'category', type: 'string', example: 'Finance'),
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Erreur de validation des données',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'name : Cette valeur ne doit pas être vide.'),
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Erreur serveur interne',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Internal server error'),
            ]
        )
    )]
    public function addNewTool(Request $request, SerializerInterface $serializer): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $tool = $serializer->deserialize($request->getContent(), Tools::class, 'json');

            if (isset($data['category'])) {
                $category = $this->entityManager->getRepository(Categories::class)->findOneBy(['name' => $data['category']]);
                $tool->setCategory($category);
            }

            $response = $this->validatorResponder->validate($tool);
            if ($response !== null) {
                return $response;
            }

            $this->entityManager->persist($tool);
            $this->entityManager->flush();

            return $this->json($tool, 201);

        } catch (\InvalidArgumentException $exception) {
            return $this->json(['error' => $exception->getMessage()], 422);
        } catch (\Exception $exception) {
            return $this->json(['error' => $exception->getMessage()], 500);
        }
    }

    #[Route('/tool/update/{id}', name: 'app_update_tool', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/tool/update/{id}',
        summary: 'Mettre à jour un outil existant',
        description: 'Met à jour les informations d\'un outil existant. Seuls les champs fournis seront modifiés.',
        tags: ['Tools']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'Identifiant unique de l\'outil à modifier',
        schema: new OA\Schema(type: 'integer'),
        example: 1
    )]
    #[OA\RequestBody(
        required: true,
        description: 'Champs à mettre à jour (tous optionnels)',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Outil version Pro'),
                new OA\Property(property: 'description', type: 'string', example: 'Ceci est une description mise à jour'),
                new OA\Property(property: 'status', type: 'string', enum: ['active', 'inactive', 'revoked'], example: 'revoked'),
                new OA\Property(property: 'ownerDepartment', type: 'string', example: 'Design'),
                new OA\Property(property: 'cost', type: 'number', format: 'float', example: 99.99),
                new OA\Property(property: 'category', type: 'string', example: 'Analytics'),
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Outil mis à jour avec succès',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'tool',
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Outil version Pro'),
                        new OA\Property(property: 'status', type: 'string', example: 'revoked'),
                        new OA\Property(property: 'ownerDepartment', type: 'string', example: 'Design'),
                        new OA\Property(property: 'cost', type: 'number', format: 'float', example: 99.99),
                        new OA\Property(property: 'category', type: 'string', example: 'Analytics'),
                    ]
                ),
                new OA\Property(property: 'status', type: 'string', example: 'updated'),
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Outil non trouvé',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'tool with id 99 not found'),
                new OA\Property(property: 'message', type: 'string', example: 'tool with id 99 not found'),
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Erreur de validation des données',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'name : Cette valeur ne doit pas être vide.'),
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Erreur serveur interne',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'error', type: 'string', example: 'Internal server error'),
            ]
        )
    )]
    public function updateTool(int $id, Request $request, SerializerInterface $serializer): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $tool = $this->entityManager->getRepository(Tools::class)->find($id);

            if (!$tool) {
                throw new NotFoundHttpException("tool with id {$id} not found");
            }

            $serializer->deserialize($request->getContent(), Tools::class, 'json', ['object_to_populate' => $tool]);

            if (isset($data['category'])) {
                $category = $this->entityManager->getRepository(Categories::class)->findOneBy(['name' => $data['category']]);
                $tool->setCategory($category);
            }

            $this->validatorResponder->validate($tool);
            $this->entityManager->flush();

            return $this->json([
                'tool' => $tool,
                'status' => 'updated',
            ], 200);

        } catch (\InvalidArgumentException $exception) {
            return $this->json(['error' => $exception->getMessage()], 422);
        } catch (NotFoundHttpException $exception) {
            return $this->json([
                'error' => $exception->getMessage(),
                'message' => "tool with id {$id} not found",
            ], 404);
        } catch (\Exception $exception) {
            return $this->json(['error' => $exception->getMessage()], 500);
        }
    }
}
