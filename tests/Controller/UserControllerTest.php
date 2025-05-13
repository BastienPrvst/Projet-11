<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class UserControllerTest extends AbstractController
{
    public function __construct(
        private HttpClientInterface $client,
    )
    {
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/user', name: 'app_create_user', methods: ['POST'])]
    public function createUser(): void
    {
        $data = $this->client->request('GET', '/user');



    }


    
}
