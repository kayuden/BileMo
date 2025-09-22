<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Client;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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

    // #[Route('/api/clients/{clientId}/users/{userId}', name: 'deleteUser', methods: [''])]
    // public function (): JsonResponse {
        
    // }

    #[Route('/api/clients/{clientId}/users/{userId}', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteUser(User $userId, EntityManagerInterface $em): JsonResponse {
        $em->remove($userId);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
