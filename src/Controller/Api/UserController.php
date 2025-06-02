<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Type;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class UserController extends AbstractController
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator
    )
    {
    }

    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["email", "password", "city"],
            properties: [
                new OA\Property(property: "email", type: "string", format: "email"),
                new OA\Property(property: "password", type: "string", format: "password"),
                new OA\Property(property: "city", type: "string"),
            ]
        )
    )]
    #[Route('/user', name: 'app_user', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return new JsonResponse($this->serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));
        $this->em->persist($user);
        $this->em->flush();

        $jsonUser = $this->serializer->serialize($user, 'json');

        return new JsonResponse("Account Created", Response::HTTP_CREATED, [], true);

    }

    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "roles",
                    type: "array",
                    items: new OA\Items(type: "string")
                ),
                new OA\Property(property: "city", type: "string", example: "Paris"),
                new OA\Property(property: "email", type: "string", example: "user@example.com"),
                new OA\Property(property: "password", type: "string", example: "password"),
            ]
        )
    )]
    #[Route('/user/{id}', name: 'app_user_modify', methods: ['PUT'])]
    public function modifyUser(User $user, Request $request): JsonResponse
    {
        try {
            $updatedUser =
                $this->serializer ->deserialize($request->getContent(),
                    User::class,
                    'json',
                    [AbstractNormalizer::OBJECT_TO_POPULATE => $user]
                );
            $content = $request->toArray();
            if (array_key_exists('password', $content) && $content['password'] !== null) {
                $updatedUser->setPassword(
                    $this->passwordHasher->hashPassword($updatedUser, $content['password'])
                );
            }

            $errors = $this->validator->validate($updatedUser);
            if (count($errors) > 0) {
                return new JsonResponse($this->serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
            }
            $this->em->persist($updatedUser);
            $this->em->flush();
            return new JsonResponse("Account Updated", Response::HTTP_ACCEPTED, [], false);
        }catch (\Exception $exception){
            return new JsonResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST, [], false);
        }
    }

    #[Route('/user/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    public function deleteUser(User $user): JsonResponse
    {
        try {
            $this->em->remove($user);
            $this->em->flush();
            return new JsonResponse("Account Deleted", Response::HTTP_ACCEPTED, [], true);
        }catch (\Exception $exception){
            return new JsonResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST, [], false);
        }
    }


}
