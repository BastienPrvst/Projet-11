<?php

namespace App\Controller\Api;

use App\Entity\Advice;
use App\Repository\AdviceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class AdviceController extends AbstractController
{

    public function __construct(
        private readonly AdviceRepository $adviceRepository,
        private readonly Request $request,
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator
    )
    {
    }

    #[Route('/conseil', name: 'app_advice', methods: ['GET'])]
    public function getAllAdvices(): JsonResponse
    {
        $allMonthAdvices = $this->adviceRepository->getAdvicesForCurrentMonth();


        return $this->json([
            'current-month-advices' => !empty($allMonthAdvices)
                ? $allMonthAdvices
                : 'Aucun conseil n\'a été enregistré ce mois-ci',
        ]);
    }

     #[Route('/conseil/{mois}', name: 'app_advice_month', methods: ['GET'])]
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

     #[OA\RequestBody(
         required: true,
         content: new OA\JsonContent(
             required: ["description"],
             properties: [
                 new OA\Property(property: "description", type: "string"),
                 new OA\Property(property: "date", type: "date"),
             ]
         )
     )]
     #[Security(name: 'Bearer')]
     #[Route('/conseil', name: 'app_advice_create', methods: ['POST'])]
     public function index3(): JsonResponse
     {
         $advice = $this->serializer->deserialize($this->request->getContent(), Advice::class, 'json');
         $errors = $this->validator->validate($advice);
         if (count($errors) > 0) {
             return new JsonResponse($this->serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
         }
         $this->em->persist($advice);
         $this->em->flush();
         return new JsonResponse($this->serializer->serialize($advice, 'json'), Response::HTTP_CREATED, [], true);
     }

     #[Route('/conseil/{id}', name: 'app_advice_delete', methods: ['DELETE'])]
     public function deleteAdivce(Advice $advice): JsonResponse
     {
         $this->em->remove($advice);
         $this->em->flush();
         return new JsonResponse('Advice with id '.$advice->getId().' deleted', Response::HTTP_OK,[], false);
     }



}
