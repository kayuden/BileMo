<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ClientUserController extends AbstractController
{
    #[Route('/api/clients/{clientId}/users', name: 'listClientUsers', methods: ['GET'])]
    public function getClientUsers(int $clientId, UserRepository $userRepository, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        $idCache = "getClientUsers-" . $clientId;


        $users = $cache->get($idCache, function (ItemInterface $item) use ($userRepository, $clientId) {
            echo ("THE ELEMENT ISN'T YET CACHED ! \n");
            $item->tag("clientUsersCache");
            return $userRepository->findBy(['client' => $clientId]);
        });
        
        $jsonUsers = $serializer->serialize($users, 'json', ['groups' => 'getClientUsers']);
        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

    #[Route('/api/clients/{clientId}/users/{userId}', name: 'detailClientUser', methods: ['GET'])]
    public function getClientUserDetails(int $clientId, int $userId, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $user = $userRepository->findOneBy([
            'id' => $userId,
            'client' =>  $clientId,
        ]);

        if ($user) {
            $jsonProduct = $serializer->serialize($user, 'json', ['groups' => 'getClientUsers']);
            return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/clients/{clientId}/users', name: 'createClientUser', methods: ['POST'])]
    public function createClientUser(int $clientId, ClientRepository $clientRepository, Request $request, SerializerInterface $serializer, EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator, NormalizerInterface $normalizer, ValidatorInterface $validator): JsonResponse {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        $client = $clientRepository->find($clientId);
        $user->setClient($client);

        // errors check
        $errors = $validator->validate($user);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($user);
        $em->flush();

        $jsonUser = $normalizer->normalize($user, 'json', ['groups' => 'getClientUsers']);
        $jsonClient = $normalizer->normalize($client, 'json', ['groups' => 'getClientUsers']);

        $responseData = [
            'user' => array_merge($jsonUser, ['client' => $jsonClient]),
        ];

        $location = $urlGenerator->generate('detailClientUser', ['clientId' => $client->getId(), 'userId' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location" => $location]);
    }

    #[Route('/api/clients/{clientId}/users/{userId}', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteUser(int $clientId, User $userId, EntityManagerInterface $em, TagAwareCacheInterface $cache): JsonResponse {
        /** @var \App\Entity\Client $connectedClient */
        $connectedClient = $this->getUser();

        if (!$connectedClient) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        if ($connectedClient->getId() !== $clientId) {
            return new JsonResponse(['error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        if ($userId->getClient()->getId() !== $connectedClient->getId()) {
            return new JsonResponse(['error' => 'User doesn\'t belong to this client'], Response::HTTP_FORBIDDEN);
        }

        $cache->invalidateTags(["clientUsersCache"]);
        $em->remove($userId);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    // #[Route('/api/clients/{clientId}/users/{userId}', name: 'deleteUser', methods: ['DELETE'])]
    // public function deleteUser(User $userId, EntityManagerInterface $em): JsonResponse {
    //     $em->remove($userId);
    //     $em->flush();

    //     return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    // }
}
