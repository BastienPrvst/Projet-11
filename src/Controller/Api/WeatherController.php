<?php

namespace App\Controller\Api;

use App\Entity\User;
use Cmfcmf\OpenWeatherMap;
use Cmfcmf\OpenWeatherMap\NotFoundException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Http\Factory\Guzzle\RequestFactory;
use OpenApi\Attributes as OA;


final class WeatherController extends AbstractController
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly Security $security,
    )
    {
    }

    #[OA\Parameter(
        name: 'ville',
        description: 'La ville demandée.',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string')
    )]
    #[Route('/meteo', name: 'app_weather', methods: ['GET'])]
    public function getWeather(Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $ville = $request->query->get('ville');

        $id = 'getWeather-' . $ville;


        /* @var User $user */
        $user = $this->security->getUser();

        if (!$ville){
            $ville = $user->getCity();
        }

        try {

            $weather = $cache->get($id, function (ItemInterface $item) use ($ville) {
                $psr18Client = new Psr18Client($this->httpClient);
                $requestFactory = new RequestFactory();
                $owm = new OpenWeatherMap($_ENV['API_KEY'], $psr18Client, $requestFactory);
                $item->expiresAfter(3600);
                $item->tag('weatherCache');
                echo ('pas en cache');
                return $owm->getWeather($ville, 'metric', 'fr');
            });

            return new JsonResponse(
                [
                    'ville' => $ville,
                    'température' => $weather->temperature->__toString(),
                    'conditions' => $weather->weather->description],
                Response::HTTP_OK,
                [],
                false
            );


        } catch (NotFoundException|\Exception|InvalidArgumentException $e) {
            return new JsonResponse(['message' => 'La ville demandée est introuvable.'], Response::HTTP_NOT_FOUND, [], false);
        }


    }
}
