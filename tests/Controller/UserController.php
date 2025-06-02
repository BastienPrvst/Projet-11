<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/api/user', name: 'app_api_user_')]
final class UserController extends AbstractController
{
    public function __construct(
    )
    {
    }

    #[Route('/user', name: 'app_create_user', methods: ['POST'])]
    public function createUser(): void
    {

    }


    
}
