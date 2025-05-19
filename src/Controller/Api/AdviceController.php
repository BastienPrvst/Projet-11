<?php

namespace App\Controller\Api;

use App\Entity\Advice;
use App\Repository\AdviceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

 class AdviceController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdviceRepository $adviceRepository,
    )
    {
    }

    #[Route('/conseils', name: 'app_advice', methods: ['GET'])]
    public function getAllAdvices(): JsonResponse
    {
        $allMonthAdvices = $this->adviceRepository->getAdvicesForCurrentMonth();


        return $this->json([
            'current-month-advices' => !empty($allMonthAdvices)
                ? $allMonthAdvices
                : 'Aucun conseil n\'a été enregistré ce mois-ci',
        ]);
    }

     #[Route('/conseils/{mois}', name: 'app_advice_month', methods: ['GET'])]
     public function month(int $mois): JsonResponse
     {
         if ($mois < 1 || ($mois > 12)) {
             return $this->json([
                 'parameters_error' => 'Le mois n\'est pas valide',
             ], '400');
         }

         $allMonthAdvices = $this->adviceRepository->getAdvicesForSelectedMonth($mois);
         return $this->json([
             'selected-month-advices' => !empty($allMonthAdvices)
                 ? $allMonthAdvices
                 : 'Pas de conseils trouvés pour ce mois-ci',
         ]);
     }

     #[Route('/conseils', name: 'app_advice_create', methods: ['POST'])]
     public function index3(): JsonResponse
     {
         $allMonthAdvices = $this->adviceRepository->getAdvicesForCurrentMonth();


         return $this->json([
             'current-month-advices' => !empty($allMonthAdvices)
                 ? $allMonthAdvices
                 : 'Aucun conseil n\'a été enregistré ce mois-ci',
         ]);
     }

     #[Route('/conseils/{id}', name: 'app_advice_delete', methods: ['DELETE'])]
     public function deleteAdivce(Advice $advice): JsonResponse
     {
         $this->entityManager->remove($advice);
         $this->entityManager->flush();
         return new JsonResponse('Advice with id '.$advice->getId().' deleted', Response::HTTP_OK,[], false);
     }



}
