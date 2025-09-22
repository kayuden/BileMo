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
    public function deleteUser(User $userId, EntityManagerInterface $em): JsonResponse {
        $em->remove($userId);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
