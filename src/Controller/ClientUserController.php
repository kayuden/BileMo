<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ClientUserController extends AbstractController
{
    #[Route('/api/clients/{clientId}/users/{userId}', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteUser(int $clientId, int $userId, ClientRepository $clientRepo, UserRepository $userRepo, EntityManagerInterface $em): JsonResponse {
        $client = $clientRepo->find($clientId);
        if (!$client) {
            return new JsonResponse(['error' => 'Client not found'], Response::HTTP_NOT_FOUND);
        }

        $user = $userRepo->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        if ($user->getClient()->getId() !== $client->getId()) {
            return new JsonResponse(['error' => 'User does not belong to this client'], Response::HTTP_FORBIDDEN);
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
