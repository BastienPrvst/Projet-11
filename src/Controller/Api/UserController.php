<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class UserController extends AbstractController
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly Request $request,
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
    public function createUser(): JsonResponse
    {
        $user = $this->serializer->deserialize($this->request->getContent(), User::class, 'json');
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return new JsonResponse($this->serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));
        $this->em->persist($user);
        $this->em->flush();

        $jsonUser = $this->serializer->serialize($user, 'json');

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, [], true);

    }

    #[Route('/user/{id}', name: 'app_user_modify', methods: ['PUT'])]
    public function modifyUser(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ]);
    }

    #[Route('/user/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    public function deleteUser(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ]);
    }


}
