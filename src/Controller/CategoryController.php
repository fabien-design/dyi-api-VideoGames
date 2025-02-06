<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('/api/v1/categories')]
#[OA\Tag(name: 'Category')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'category_list', methods: ['GET'])]
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
            items: new OA\Items(ref: new Model(type: Category::class, groups: ['category:read']))
        )
    )]
    public function index(CategoryRepository $categoryRepository, Request $request, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        if ($limit > 100) {
            return $this->json(['error' => 'The limit parameter must be lower than 100'], Response::HTTP_BAD_REQUEST);
        }

        $cacheIdentifier = 'categories_list-' . $page . '-' . $limit;

        $categories = $cachePool->get($cacheIdentifier, function (ItemInterface $item) use ($categoryRepository, $page, $limit) {
            $item->tag('categoriesCache');
            return $categoryRepository->findAllWithPagination($page, $limit);
        });

        return $this->json($categories, Response::HTTP_OK, [], ['groups' => 'category:read']);
    }

    #[Route(
        '/{id}',
        name: 'category_show',
        requirements: ['id' => Requirement::DIGITS],
        methods: ['GET']
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: Category::class, groups: ['category:read','category:detail'])
    )]
    public function show(Category $category): JsonResponse
    {

        return $this->json($category, Response::HTTP_OK, [], ['groups' => ['category:read', 'category:detail']]);
    }

    #[Route('/new', name: 'category_create', methods: ['POST'], priority: 1)]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied, you must be an admin to access this route')]
    #[OA\Post(
        description: "Create a new category",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Action"),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Successful response',
        content: new Model(type: Category::class, groups: ['category:read', 'category:detail'])
    )]
    public function create(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $category = $serializer->deserialize($request->getContent(), Category::class, 'json');

        $errors = $validator->validate($category);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $em->persist($category);
        $em->flush();
        
        $cachePool->invalidateTags(['categoriesCache']);

        return $this->json($category, status: Response::HTTP_CREATED, context: [
            'groups' => ['category:read', 'category:detail']
        ]);
    }

    #[Route('/{id}',
    name: 'category_update',
    requirements: ['id' => Requirement::DIGITS],
    methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied, you must be an admin to access this route')]
    #[OA\Put(
        description: "Update a category",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Action"),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Content updated',
        content: new Model(type: Category::class, groups: ['category:read', 'category:detail'])
    )]
    public function update(Request $request, Category $category, SerializerInterface $serializerInterface, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $category = $serializerInterface->deserialize($request->getContent(), Category::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $category]);
        
        $errors = $validator->validate($category);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }
        
        $cachePool->invalidateTags(['categoriesCache']);

        $em->persist($category);
        $em->flush();


        $location = $urlGenerator->generate('app_category', ['id' => $category->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->json($category, Response::HTTP_OK, ['Location' => $location], ['groups' => ['category:read', 'category:detail']]);
    }

    #[Route('/{id}', 
    name: 'category_delete',
    methods: ['DELETE'],
    requirements: ['id' => Requirement::DIGITS],)]
    #[IsGranted('ROLE_ADMIN', message: 'Access denied, you must be an admin to access this route')]
    #[OA\Delete(
        description: "Delete a category"
    )]
    #[OA\Response(
        response: 204,
        description: 'Category deleted'
    )]
    public function delete(Category $category, EntityManagerInterface $entityManager, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $cachePool->invalidateTags(['categoriesCache']);

        $entityManager->remove($category);
        $entityManager->flush();
        return $this->json(null, Response::HTTP_OK);
    }
}
