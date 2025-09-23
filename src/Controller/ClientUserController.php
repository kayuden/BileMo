<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;

final class ClientUserController extends AbstractController
{
    #[Route('/api/clients/{clientId}/users', name: 'listClientUsers', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of registered users associated with a client',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type:User::class, groups: ['getClientUsers']))
        )
    )]
    #[OA\Tag(name: 'Users')]
    public function getClientUsers(int $clientId, UserRepository $userRepository, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        $idCache = "getClientUsers-" . $clientId;


        $users = $cache->get($idCache, function (ItemInterface $item) use ($userRepository, $clientId) {
            echo ("THE ELEMENT ISN'T YET CACHED ! \n");
            $item->tag("clientUsersCache");
            return $userRepository->findBy(['client' => $clientId]);
        });
        
        $context = SerializationContext::create()->setGroups(["getClientUsers"]);
        $jsonUsers = $serializer->serialize($users, 'json', $context);
        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

    #[Route('/api/clients/{clientId}/users/{userId}', name: 'detailClientUser', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the details of a registered user linked to a client',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type:User::class, groups: ['getClientUsers']))
        )
    )]
    #[OA\Tag(name: 'Users')]
    public function getClientUserDetails(int $clientId, int $userId, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $user = $userRepository->findOneBy([
            'id' => $userId,
            'client' =>  $clientId,
        ]);

        if ($user) {
            $context = SerializationContext::create()->setGroups(["getClientUsers"]);
            $jsonProduct = $serializer->serialize($user, 'json', $context);
            return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/clients/{clientId}/users', name: 'createClientUser', methods: ['POST'])]
    #[OA\Tag(name: 'Users')]
    #[OA\Parameter(
        name: 'clientId',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            // Décrit le schéma du body à partir de ton entité et des groupes "write"
            ref: new Model(type: User::class, groups: ['user:write']),
            // (Optionnel) ajoute un exemple concret
            examples: [
                new OA\Examples(example: 'payload', summary: 'Exemple minimal', value: [
                    'firstName' => 'Jane',
                    'lastName' => 'Doe',
                    'phoneNumber' => '+33784914616!'
                ])
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'User created',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(
                    property: 'user',
                    ref: new Model(type: User::class, groups: ['getClientUsers'])
                )
            ]
        )
    )]
    #[OA\Response(response: 400, description: 'Validation errors')]
    #[OA\Response(response: 404, description: 'Client not found')]
    public function createClientUser(int $clientId, ClientRepository $clientRepository, Request $request, SerializerInterface $serializer, EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse {
        /** @var User $user */ 
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        $client = $clientRepository->find($clientId);
        if (!$client) {
            return new JsonResponse(['error' => 'Client not found'], Response::HTTP_NOT_FOUND);
        }
        $user->setClient($client);

        // errors check
        $errors = $validator->validate($user);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($user);
        $em->flush();

        $context = SerializationContext::create()->setGroups(["getClientUsers"])->enableMaxDepthChecks();

        $json = $serializer->serialize(['user' => $user], 'json', $context);

        $location = $urlGenerator->generate('detailClientUser', ['clientId' => $client->getId(), 'userId' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($json, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/clients/{clientId}/users/{userId}', name: 'deleteUser', methods: ['DELETE'])]
    #[OA\Tag(name: 'Users')]
    public function deleteUser(int $clientId, User $userId, EntityManagerInterface $em, TagAwareCacheInterface $cache): JsonResponse {
        /** @var \App\Entity\Client $connectedClient */
        $connectedClient = $this->getUser();

        if (!$connectedClient) {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        if ($connectedClient->getId() !== $clientId) {
            return new JsonResponse(['error' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        $client = $userId->getClient();
        if ($client === null || $client->getId() !== $connectedClient->getId()) {
            return new JsonResponse(['error' => 'User doesn\'t belong to this client'], Response::HTTP_FORBIDDEN);
        }

        $cache->invalidateTags(["clientUsersCache"]);
        $em->remove($userId);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
