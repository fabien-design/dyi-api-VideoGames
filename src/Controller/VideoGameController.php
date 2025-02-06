<?php

namespace App\Controller;

use App\Entity\VideoGame;
use App\Repository\CategoryRepository;
use App\Repository\EditorRepository;
use App\Repository\VideoGameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations\MediaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('/api/v1/videogames')]
#[OA\Tag(name: 'Video Game')]
class VideoGameController extends AbstractController
{
    #[Route('/', name: 'video_game_list', methods: ['GET'])]
    #[OA\QueryParameter(
        name: 'page',
        description: 'The page number',
        required: false,
        allowEmptyValue: false,
        example: 1
    )]
    #[OA\QueryParameter(
        name: 'limit',
        description: 'The number of items per page',
        required: false,
        allowEmptyValue: false,
        example: 10
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: VideoGame::class, groups: ['videoGame:read']))
        )
    )]
    public function index(VideoGameRepository $repository, Request $request, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        if ($limit > 100) {
            return $this->json(['error' => 'The limit parameter must be lower than 100'], Response::HTTP_BAD_REQUEST);
        }

        $cacheIdentifier = 'videogames_list-' . $page . '-' . $limit;
        $videoGames = $cachePool->get($cacheIdentifier, 
            function (ItemInterface $item) use ($repository, $page, $limit) {
                $item->tag('videogamesCache');
                return $repository->findAllWithPagination($page, $limit);
            }
        );
        $repository->findAllWithPagination($page, $limit);
        return $this->json($videoGames, context: [
            'groups' => ['videoGame:read']
        ]);
    }

    #[Route('/{id}',
    name: 'video_game_show',
    requirements: ['id' => Requirement::DIGITS],
    methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: VideoGame::class, groups: ['videoGame:read', 'videoGame:detail'])
    )]
    public function show(VideoGame $videoGame): JsonResponse
    {
        return $this->json($videoGame, context: [
            'groups' => ['videoGame:read', 'videoGame:detail']
        ]);
    }

    #[Route('/new', name: 'video_game_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied, you must be an admin to access this route')]
    #[OA\Post(
        description: "Create a new video game",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Zelda the latest adventure"),
                    new OA\Property(property: "releaseDate", type: "string", format: "date", example: "2024-02-28"),
                    new OA\Property(property: "description", type: "string", example: "A great game, really!"),
                    new OA\Property(property: "editor", type: "integer", example: 3),
                    new OA\Property(
                        property: "categories",
                        type: "array",
                        items: new OA\Items(type: "integer"),
                        example: [6, 9]
                    )
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Content created',
        content: new Model(type: VideoGame::class, groups: ['videoGame:read', 'videoGame:detail'])
    )]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EditorRepository $editorRepository,
        CategoryRepository $categoryRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        $editorId = filter_var($data['editor'], FILTER_SANITIZE_NUMBER_INT);
        $editor = $editorRepository->find($editorId);
        if (!$editor) {
            return $this->json(['error' => 'Editor not found'], Response::HTTP_NOT_FOUND);
        }

        $categories = [];
        foreach ($data['categories'] as $categoryIri) {
            $categoryId = filter_var($categoryIri, FILTER_SANITIZE_NUMBER_INT);
            $category = $categoryRepository->find($categoryId);
            if (!$category) {
                return $this->json(['error' => 'Category not found'], Response::HTTP_NOT_FOUND);
            }
            $categories[] = $category;
        }

        $videoGame = $serializer->deserialize($request->getContent(), VideoGame::class, 'json', [
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['editor', 'categories'],
            "groups" => "videoGame:write"
        ]);
        $videoGame->setEditor($editor);
        
        foreach ($categories as $category) {
            $videoGame->addCategory($category);
        }

        $errors = $validator->validate($videoGame);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $em->persist($videoGame);
        $em->flush();

        return $this->json($videoGame, Response::HTTP_CREATED, [], ['groups' => ['videoGame:read', 'videoGame:detail']]);
    }

    #[Route('/{id}',
    name: 'video_game_update',
    requirements: ['id' => Requirement::DIGITS],
    methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied, you must be an admin to access this route')]
    #[OA\Put(
        description: "Update a video game",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Zelda the latest adventure"),
                    new OA\Property(property: "releaseDate", type: "string", format: "date", example: "2024-02-28"),
                    new OA\Property(property: "description", type: "string", example: "A great game, really!"),
                    new OA\Property(property: "editor", type: "integer", example: 3),
                    new OA\Property(
                        property: "categories",
                        type: "array",
                        items: new OA\Items(type: "integer"),
                        example: [6, 9]
                    )
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Content updated',
        content: new Model(type: VideoGame::class, groups: ['videoGame:read', 'videoGame:detail'])
    )]
    public function update(
        VideoGame $videoGame,
        Request $request,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        TagAwareCacheInterface $cachePool,
        EditorRepository $editorRepository,
        CategoryRepository $categoryRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (isset($data['editor'])) {
            $editorId = filter_var($data['editor'], FILTER_SANITIZE_NUMBER_INT);
            $editor = $editorRepository->find($editorId);
            if (!$editor) {
                return $this->json(['error' => 'Editor not found'], Response::HTTP_NOT_FOUND);
            }
            $videoGame->setEditor($editor);
        }

        if (isset($data['categories'])) {
            foreach ($videoGame->getCategory() as $category) {
                $videoGame->removeCategory($category);
            }

            // Ajouter les nouvelles catégories
            foreach ($data['categories'] as $categoryId) {
                $categoryId = filter_var($categoryId, FILTER_SANITIZE_NUMBER_INT);
                $category = $categoryRepository->find($categoryId);
                if (!$category) {
                    return $this->json(['error' => 'Category not found'], Response::HTTP_NOT_FOUND);
                }
                $videoGame->addCategory($category);
            }
        }

        $updatedVideoGame = $serializer->deserialize($request->getContent(), VideoGame::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $videoGame,
        ]);

        $errors = $validator->validate($updatedVideoGame);
        if (count($errors) > 0) {
            return $this->json($errors, status: Response::HTTP_BAD_REQUEST);
        }
        $em->persist($updatedVideoGame);
        $em->flush();
        $cachePool->invalidateTags(['videogamesCache']);

        return $this->json($updatedVideoGame, context: [
            'groups' => ['videoGame:read', 'videoGame:detail']
        ]);
    }

    #[Route('/{id}',
    name: 'video_game_delete',
    requirements: ['id' => Requirement::DIGITS],
    methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied, you must be an admin to access this route')]
    #[OA\Delete(
        description: "Delete a video game"
    )]
    #[OA\Response(
        response: 204,
        description: 'Content deleted'
    )]
    public function delete(VideoGame $videoGame, EntityManagerInterface $em, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $cachePool->invalidateTags(['videogamesCache']);
        $em->remove($videoGame);
        $em->flush();
        
        return $this->json(null, status: Response::HTTP_NO_CONTENT);
    }
    
}
