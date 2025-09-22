<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ClientUserController extends AbstractController
{
    #[Route('/api/clients/{clientId}/users', name: 'listClientUsers', methods: ['GET'])]
    public function getClientUsers(int $clientId, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $users = $userRepository->findBy(['client' => $clientId]);
        
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
    public function createClientUser(int $clientId, ClientRepository $clientRepository, Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        $client = $clientRepository->find($clientId);
        $user->setClient($client);

        $em->persist($user);
        $em->flush();

        $jsonUser = $serializer->serialize($user, 'json', ['groups' => 'getClientUsers']);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/clients/{clientId}/users/{userId}', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteUser(int $clientId, User $userId, EntityManagerInterface $em): JsonResponse {
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
