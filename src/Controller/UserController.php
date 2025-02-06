<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route('/api/v1/users')]
#[IsGranted('ROLE_ADMIN', message: 'Access denied, you must be an admin to access this route')]
#[OA\Tag(name: 'User')]
class UserController extends AbstractController
{
    #[Route('/', name: 'user_list', methods: ['GET'])]
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
            items: new OA\Items(ref: new Model(type: User::class, groups: ['user:read']))
        )
    )]
    public function index(UserRepository $userRepository, Request $request, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);

        if ($limit > 100) {
            return $this->json(['error' => 'The limit parameter must be lower than 100'], Response::HTTP_BAD_REQUEST);
        }

        $cacheIdentifier = 'users_list-' . $page . '-' . $limit;

        $users = $cachePool->get($cacheIdentifier, function (ItemInterface $item) use ($userRepository, $page, $limit) {
            $item->tag('usersCache');
            return $userRepository->findAllWithPagination($page, $limit);
        });

        return $this->json($users, Response::HTTP_OK, [], ['groups' => 'user:read']);
    }

    #[Route('/{id}',
        name: 'user_show',
        requirements: ['id' => Requirement::DIGITS],
        methods: ['GET']
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: User::class, groups: ['user:read'])
    )]
    public function show(User $user): JsonResponse
    {
        return $this->json($user, Response::HTTP_OK, [], ['groups' => 'user:read']);
    }

    #[Route('/new', name: 'user_create', methods: ['POST'], priority: 1)]
    #[OA\Post(
        description: "Create a new user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "email", type: "string", example: "Yaaaah"),
                    new OA\Property(property: "password", type: "string", example: "Yaaaaah"),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Created content',
        content: new Model(type: User::class, groups: ['user:read'])
    )]
    public function create(
        Request $request, 
        SerializerInterface $serializer, 
        EntityManagerInterface $em, 
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher,
        TagAwareCacheInterface $cachePool
    ): JsonResponse
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $user->getPassword()
        );
        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_USER']);

        $cachePool->invalidateTags(['usersCache']);

        $em->persist($user);
        $em->flush();

        return $this->json($user, Response::HTTP_CREATED, [], ['groups' => 'user:read']);
    }

    #[Route('/{id}',
        name: 'user_update',
        requirements: ['id' => Requirement::DIGITS],
        methods: ['PUT']
    )]
    #[IsGranted('ROLE_USER')]
    #[OA\Put(
        description: "Update a user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "email", type: "string", example: "Yaaaah"),
                    new OA\Property(property: "password", type: "string", example: "Yaaaaah"),
                    new OA\Property(property: "repeatPassword", type: "string", example: "Yaaaaah"),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Updated content',
        content: new Model(type: User::class, groups: ['user:read'])
    )]
    public function update(
        User $user,
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher,
        TagAwareCacheInterface $cachePool
    ): JsonResponse
    {
        if ($user !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        
        if (isset($data['password'])) {
            if (!isset($data['repeatPassword']) || $data['password'] !== $data['repeatPassword']) {
                return $this->json(['error' => 'Passwords do not match'], Response::HTTP_BAD_REQUEST);
            }
        }

        $updatedUser = $serializer->deserialize($request->getContent(), User::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $user
        ]);

        $errors = $validator->validate($updatedUser);
        if (count($errors) > 0) {
            return $this->json($errors, Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['password'])) {
            $hashedPassword = $passwordHasher->hashPassword(
                $updatedUser,
                $data['password']
            );
            $updatedUser->setPassword($hashedPassword);
        }

        $cachePool->invalidateTags(['usersCache']);

        $em->persist($updatedUser);
        $em->flush();

        return $this->json($updatedUser, Response::HTTP_OK, [], ['groups' => 'user:read']);
    }

    #[Route('/{id}',
        name: 'user_delete',
        requirements: ['id' => Requirement::DIGITS],
        methods: ['DELETE']
    )]
    #[OA\Response(
        response: 204,
        description: 'No content'
    )]
    public function delete(User $user, EntityManagerInterface $em, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $cachePool->invalidateTags(['usersCache']);

        $em->remove($user);
        $em->flush();
        
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
