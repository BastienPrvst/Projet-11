<?php

namespace App\Controller;

use App\Entity\Advice;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Attribute\Route;

final class AdviceController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route('/conseil', name: 'app_advice', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/AdviceController.php',
        ]);
    }

    #[Route('/conseil/{month}', name: 'app_advice_month', methods: ['GET'])]
    public function month(): JsonResponse
    {

        $adviceRepo = $this->entityManager->getRepository(Advice::class);
        $allMonthAdvices = $adviceRepo->getAdvicesForCurrentMonth();


        return $this->json([
            'current-month-advices' => $allMonthAdvices,
        ]);
    }
}
