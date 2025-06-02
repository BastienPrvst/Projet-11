<?php

namespace App\Controller\Api;

use App\Entity\Advice;
use App\Repository\AdviceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Attribute\Security;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class AdviceController extends AbstractController
{

    public function __construct(
        private readonly AdviceRepository       $adviceRepository,
        private readonly SerializerInterface    $serializer,
        private readonly EntityManagerInterface $em,
        private readonly ValidatorInterface     $validator
    )
    {
    }

    #[Route('/conseil', name: 'app_advice', methods: ['GET'])]
    public function getAllAdvices(): JsonResponse
    {
        try {
            $allMonthAdvices = $this->adviceRepository->getAdvicesForCurrentMonth();
            return new JsonResponse($this->serializer->serialize($allMonthAdvices, 'json'), Response::HTTP_OK, [], 'true');
        }catch (\Exception $exception){
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

     #[Route('/conseil/{mois}', name: 'app_advice_month', methods: ['GET'])]
     public function getMontlhyAdvice(int $mois): JsonResponse
     {
         if ($mois < 1 || ($mois > 12)) {
             return new JsonResponse(['error' => 'Mois incorrect.'], Response::HTTP_BAD_REQUEST, [], false);
         }

         try {
             $allMonthAdvices = $this->adviceRepository->getAdvicesForSelectedMonth($mois);
             if(!empty($allMonthAdvices)){
                 return new JsonResponse($this->serializer->serialize($allMonthAdvices, 'json'), Response::HTTP_OK, [], true);
             }

             return new JsonResponse(['info' => 'Aucun conseil trouvé pour le mois selectionné.'], Response::HTTP_OK, [], false
             );

         }catch (\Exception $exception){
             return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST, [], true);
         }

     }

     #[OA\RequestBody(
         required: true,
         content: new OA\JsonContent(
             required: ["description"],
             properties: [
                 new OA\Property(property: "description", type: "string"),
                 new OA\Property(property: "date", type: "string", example: "1,4,6,12"),
             ]
         )
     )]
     #[Route('/conseil', name: 'app_advice_create', methods: ['POST'])]
     public function createAdvice(Request $request): JsonResponse
     {

         try {
             $content = $request->getContent();
             $content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
             $monthString = $content['date'] ?? '';

             $allMonth = array_map('trim', explode(',', $monthString));
             $allAdvices = [];
             foreach ($allMonth as $month) {
                 if ((int)$month < 1 || (int)$month > 12) {
                     return new JsonResponse(
                         ['error' => sprintf('Mois %s incorrect', $month)],
                         Response::HTTP_BAD_REQUEST,
                         [],
                         false
                     );
                 }

                 $advice = new Advice();
                 $advice->setDescription($content['description']);
                 $dateToAdd = new \DateTime();
                 $year = (int)$dateToAdd->format('Y');
                 $day = (int)$dateToAdd->format('d');
                 $dateToAdd->setDate($year, (int)$month, $day);
                 $advice->setDate($dateToAdd);
                 $errors = $this->validator->validate($advice);
                 if (count($errors) > 0) {
                     return new JsonResponse($this->serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
                 }

                 $allAdvices[] = $advice;
                 $this->em->persist($advice);
             }

             $this->em->flush();
             return new JsonResponse($this->serializer->serialize($allAdvices, 'json'), Response::HTTP_CREATED, [], true);
         }catch (\Exception $exception){
             return new JsonResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST, [], true);
         }

     }

     #[Route('/conseil/{id}', name: 'app_advice_delete', methods: ['DELETE'])]
     public function deleteAdvice(Advice $advice): JsonResponse
     {
         try {
             $this->em->remove($advice);
             $this->em->flush();
             return new JsonResponse([
                 'success' => sprintf('Le conseil avec l\'ID %s a bien été supprimé', $advice->getId())
             ], Response::HTTP_OK,[], false);
         }catch (\Exception $exception){
             return new JsonResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST, [], true);
         }

     }

    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "description", type: "string"),
                new OA\Property(property: "date", type: "date", example: "10-10-1998"),
            ]
        )
    )]
    #[Route('/conseil/{id}', name: 'app_advice_put', methods: ['PUT'])]
    public function putAdivce(Advice $advice, Request $request): JsonResponse
    {
        try {

            $updatedAdvice = $this->serializer->deserialize($request->getContent(),
                    Advice::class,
                    'json',
                    [AbstractNormalizer::OBJECT_TO_POPULATE => $advice]
                );
            $this->em->persist($updatedAdvice);
            $this->em->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);

        }catch (\Exception $exception){
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST, [], false);
        }

    }
}
