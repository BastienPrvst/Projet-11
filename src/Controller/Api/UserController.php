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
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user', methods: ['POST'])]
    public function createUser(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): JsonResponse
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
        $em->persist($user);
        $em->flush();

        $jsonUser = $serializer->serialize($user, 'json');

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, [], true);

    }
    #[Route('/login', name: 'app_login', methods: ['POST'])]
    public function authentificate(): JsonResponse
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
