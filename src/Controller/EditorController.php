<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Editor;
use App\Repository\EditorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
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

#[Route('/api/v1/editors')]
#[OA\Tag(name: 'Editor')]
class EditorController extends AbstractController
{
    #[Route('/', name: 'editor_list', methods: ['GET'])]
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
            items: new OA\Items(ref: new Model(type: Editor::class, groups: ['editor:read']))
        )
    )]
    public function index(EditorRepository $repository, Request $request, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        if ($limit > 100) {
            return $this->json(['error' => 'The limit parameter must be lower than 100'], Response::HTTP_BAD_REQUEST);
        }

        $cacheIdentifier = 'editors_list-' . $page . '-' . $limit;

        $editors = $cachePool->get($cacheIdentifier, function (ItemInterface $item) use ($repository, $page, $limit) {
            $item->tag('editorsCache');
            return $repository->findAllWithPagination($page, $limit);
        });

        $editors = $repository->findAllWithPagination($page, $limit);
        return $this->json($editors, context: [
            'groups' => ['editor:read']
        ]);
    }

    #[Route('/{id}',
    name: 'editor_show',
    requirements: ['id' => Requirement::DIGITS],
    methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: Editor::class, groups: ['editor:read', 'editor:detail'])
    )]
    public function show(Editor $editor): JsonResponse
    {
        return $this->json($editor, context: [
            'groups' => ['editor:read', 'editor:detail']
        ]);
    }

    #[Route('/new', name: 'editor_create', methods: ['POST'], priority: 20)]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied, you must be an admin to access this route')]
    #[OA\Post(
        description: "Create a new editor",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Nintendo"),
                    new OA\Property(property: "country", type: "string", example: "Japon"),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Content created',
        content: new Model(type: Editor::class, groups: ['editor:read', 'editor:detail'])
    )]
    public function create(Request $request, EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $editor = $serializer->deserialize($request->getContent(), Editor::class, 'json');

        $errors = $validator->validate($editor);
        if (count($errors) > 0) {
            return $this->json($errors, status: Response::HTTP_BAD_REQUEST);
        }

        $em->persist($editor);
        $em->flush();

        $cachePool->invalidateTags(['videogamesCache']);
        
        return $this->json($editor, status: Response::HTTP_CREATED, context: [
            'groups' => ['editor:read', 'editor:detail']
        ]);
    }

    #[Route('/{id}',
    name: 'editor_update',
    requirements: ['id' => Requirement::DIGITS],
    methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied, you must be an admin to access this route')]
    #[OA\Put(
        description: "Update an editor",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Nintendo"),
                    new OA\Property(property: "country", type: "string", example: "Japon"),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Content updated',
        content: new Model(type: Editor::class, groups: ['editor:read', 'editor:detail'])
    )]
    public function update(Editor $editor, Request $request, EntityManagerInterface $em, SerializerInterface $serializer, ValidatorInterface $validator, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $updatedEditor = $serializer->deserialize($request->getContent(), Editor::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $editor
        ]);

        $errors = $validator->validate($updatedEditor);
        if (count($errors) > 0) {
            return $this->json($errors, status: Response::HTTP_BAD_REQUEST);
        }

        $em->persist($updatedEditor);
        $em->flush();

        $cachePool->invalidateTags(['videogamesCache']);
        
        return $this->json($updatedEditor, context: [
            'groups' => ['editor:read', 'editor:detail']
        ]);
    }

    #[Route('/{id}',
    name: 'editor_delete',
    requirements: ['id' => Requirement::DIGITS],
    methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied, you must be an admin to access this route')]
    #[OA\Delete(description: "Delete an editor")]
    #[OA\Response(response: 204, description: 'No content')]
    public function delete(Editor $editor, EntityManagerInterface $em, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $em->remove($editor);
        $em->flush();

        $cachePool->invalidateTags(['videogamesCache']);
        
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
