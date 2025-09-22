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
    #[Route('/api/clients/{clientId}/users', name: 'client_user_lists', methods: ['GET'])]
    public function getClientUsers(int $clientId, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $users = $userRepository->findBy(['client' => $clientId]);
        
        $jsonUsers = $serializer->serialize($users, 'json', ['groups' => 'getClientUsers']);
        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

    // #[Route('/api/clients/{clientId}/users/{userId}', name: 'detailUser', methods: ['GET'])]
    // public function getClientUserDetails(int $productId, ProductRepository $productRepository, SerializerInterface $serializer): JsonResponse
    // {
    //     $product = $productRepository->find($productId);
    //     if ($product) {
    //         $jsonProduct = $serializer->serialize($product, 'json');
    //         return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
    //     }
    //     return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    // }

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
