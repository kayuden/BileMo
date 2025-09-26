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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

final class ClientUserController extends AbstractController
{
    #[Route('/api/clients/{clientId}/users', name: 'listClientUsers', methods: ['GET'])]
    #[OA\Get(
        path: "/api/clients/{clientId}/users",
        summary: "View the list of users linked to a customer",
        description: "Retrieve all users associated with the specified client, identified by 'clientId'."
    )]
    // 200 OK
    #[OA\Response(
        response: 200,
        description: 'Returns the list of registered users associated with a client',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type:User::class, groups: ['getClientUsers']))
        )
    )]
    // 204 No Content
    #[OA\Response(
        response: 205,
        description: 'No Content - client has no associated users, reset view'
    )]
    // 400 Bad Request
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Invalid clientId',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 400),
                new OA\Property(property: 'message', type: 'string', example: 'The "clientId" parameter must be greater than 0')
            ]
        )
    )]
    // 401 Unauthorized
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Authentication required — JWT token must be provided in the Authorization header as Bearer <token>',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 401),
                new OA\Property(property: 'message', type: 'string', example: 'JWT Token not found')
            ]
        )
    )]
    // 403 Forbidden
    #[OA\Response(
        response: Response::HTTP_FORBIDDEN,
        description: 'You are not allowed to access this resource',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 403),
                new OA\Property(property: 'message', type: 'string', example: 'Access denied')
            ]
        )
    )]
    // 404 Not Found
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Client not found',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 404),
                new OA\Property(property: 'message', type: 'string', example: 'Client not found')
            ]
        )
    )]
    // 500 Internal Server Error
    #[OA\Response(
        response: Response::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal server error',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 500),
                new OA\Property(property: 'message', type: 'string', example: 'An unexpected error occurred')
            ]
        )
    )]
    #[OA\Tag(name: 'Users')]
    public function getClientUsers(int $clientId, ClientRepository $clientRepository, UserRepository $userRepository, SerializerInterface $serializer, TagAwareCacheInterface $cache): JsonResponse
    {
        if ($clientId <= 0) {
            return new JsonResponse(
                [
                    'status' => Response::HTTP_BAD_REQUEST,
                    'error' => 'The "clientId" parameter must be greater than 0'
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $client = $clientRepository->find($clientId);
        if (!$client) {
            return new JsonResponse(
                [
                    'status' => Response::HTTP_BAD_REQUEST,
                    'error' => 'Client not found'
                ],
                Response::HTTP_NOT_FOUND
            );
        }

        $idCache = "getClientUsers-" . $clientId;

        $users = $cache->get($idCache, function (ItemInterface $item) use ($userRepository, $clientId) {
            echo ("THE ELEMENT ISN'T YET CACHED ! \n");
            $item->tag("clientUsersCache");
            return $userRepository->findBy(['client' => $clientId]);
        });

        if (empty($users)) {
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        
        $context = SerializationContext::create()->setGroups(["getClientUsers"]);
        $jsonUsers = $serializer->serialize($users, 'json', $context);
        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

    #[Route('/api/clients/{clientId}/users/{userId}', name: 'detailClientUser', methods: ['GET'])]
    #[OA\Get(
        path: "/api/clients/{clientId}/users/{userId}",
        summary: "View the details of a user linked to a client",
        description: "Retrieve detailed information about a specific user associated with the given client. The user is identified by 'userId' within the context of the specified 'clientId'."
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the details of a registered user linked to a client',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type:User::class, groups: ['getClientUsers']))
        )
    )]
    // 400 Bad Request
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Invalid clientId or userId',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 400),
                new OA\Property(property: 'message', type: 'string', example: 'The "clientId" parameter must be greater than 0')
            ]
        )
    )]
    // 401 Unauthorized
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Authentication required — JWT token must be provided in the Authorization header as Bearer <token>',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 401),
                new OA\Property(property: 'message', type: 'string', example: 'JWT Token not found')
            ]
        )
    )]
    // 403 Forbidden
    #[OA\Response(
        response: Response::HTTP_FORBIDDEN,
        description: 'You are not allowed to access this resource',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 403),
                new OA\Property(property: 'message', type: 'string', example: 'Access denied')
            ]
        )
    )]
    // 404 Not Found
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'User not found',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 404),
                new OA\Property(property: 'message', type: 'string', example: 'User not found')
            ]
        )
    )]
    // 500 Internal Server Error
    #[OA\Response(
        response: Response::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal server error',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 500),
                new OA\Property(property: 'message', type: 'string', example: 'An unexpected error occurred')
            ]
        )
    )]
    #[OA\Tag(name: 'Users')]
    public function getClientUserDetails(int $clientId, int $userId, ClientRepository $clientRepository, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        if ($clientId <= 0) {
            return new JsonResponse(
                [
                    'status' => Response::HTTP_BAD_REQUEST,
                    'error' => 'The "clientId" parameter must be greater than 0'
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($userId <= 0) {
            return new JsonResponse(
                [
                    'status' => Response::HTTP_BAD_REQUEST,
                    'error' => 'The "userId" parameter must be greater than 0'
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $client = $clientRepository->find($clientId);
        if (!$client) {
            return new JsonResponse(
                [
                    'status' => Response::HTTP_NOT_FOUND,
                    'error' => 'Client not found'
                ],
                Response::HTTP_NOT_FOUND
            );
        }

        $user = $userRepository->findOneBy([
            'id' => $userId,
            'client' =>  $clientId,
        ]);

        if ($user) {
            $context = SerializationContext::create()->setGroups(["getClientUsers"]);
            $jsonUser = $serializer->serialize($user, 'json', $context);
            return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(
            [
            'status' => Response::HTTP_NOT_FOUND,
            'message' => 'User not found'
            ], 
            Response::HTTP_NOT_FOUND
        );
    }

    #[Route('/api/clients/{clientId}/users', name: 'createClientUser', methods: ['POST'])]
    #[OA\Post(
        path: "/api/clients/{clientId}/users",
        summary: "Add a new user linked to a client",
        description: "Create a new user account associated with the specified client. The request body must include the user's details (e.g., firstName, lastName)."
    )]
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
            ref: new Model(type: User::class, groups: ['user:write']),
            // example
            examples: [
                new OA\Examples(example: 'payload', summary: 'Example', value: [
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
    // 400 Bad Request
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Invalid request body or parameters',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'property_path', type: 'string', example: 'lastName'),
                new OA\Property(property: 'message', type: 'string', example: 'The first name is required')
            ]
        )
    )]
    // 401 Unauthorized
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Authentication required — JWT token must be provided in the Authorization header as Bearer <token>',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 401),
                new OA\Property(property: 'message', type: 'string', example: 'JWT Token not found')
            ]
        )
    )]
    // 403 Forbidden
    #[OA\Response(
        response: Response::HTTP_FORBIDDEN,
        description: 'You are not allowed to access this resource',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 403),
                new OA\Property(property: 'message', type: 'string', example: 'Access denied')
            ]
        )
    )]
    // 404 Not Found
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Client not found',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 404),
                new OA\Property(property: 'message', type: 'string', example: 'Client not found')
            ]
        )
    )]
    // 500 Internal Server Error
    #[OA\Response(
        response: Response::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal server error',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 500),
                new OA\Property(property: 'message', type: 'string', example: 'An unexpected error occurred')
            ]
        )
    )]
    public function createClientUser(int $clientId, ClientRepository $clientRepository, Request $request, SerializerInterface $serializer, EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse {
        /** @var User $user */ 
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        $client = $clientRepository->find($clientId);
        if (!$client) {
            return new JsonResponse(
                [
                    'status' => Response::HTTP_NOT_FOUND,
                    'error' => 'Client not found'
                ], 
                Response::HTTP_NOT_FOUND);
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
    #[OA\Delete(
        path: "/api/clients/{clientId}/users/{userId}",
        summary: "Delete a user added by a client",
        description: "Remove a specific user associated with the given client. The user is identified by the 'userId' within the context of the specified 'clientId'. This operation permanently deletes the user and cannot be undone."
    )]
    //204 No Content
    #[OA\Response(
        response: 204,
        description: "User deleted successfully (no content returned)"
    )]
    // 401 Unauthorized
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Authentication required — JWT token must be provided in the Authorization header as Bearer <token>',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 401),
                new OA\Property(property: 'message', type: 'string', example: 'JWT Token not found')
            ]
        )
    )]
    // 403 Forbidden
    #[OA\Response(
        response: Response::HTTP_FORBIDDEN,
        description: 'You are not allowed to access this resource',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 403),
                new OA\Property(property: 'message', type: 'string', example: 'Access denied')
            ]
        )
    )]
    // 404 Not Found
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'User not found',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 404),
                new OA\Property(property: 'message', type: 'string', example: '"App\Entity\User" object not found by "Symfony\Bridge\Doctrine\ArgumentResolver\EntityValueResolver"')
            ]
        )
    )]
    // 500 Internal Server Error
    #[OA\Response(
        response: Response::HTTP_INTERNAL_SERVER_ERROR,
        description: 'Internal server error',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 500),
                new OA\Property(property: 'message', type: 'string', example: 'An unexpected error occurred')
            ]
        )
    )]
    #[OA\Tag(name: 'Users')]
    public function deleteUser(int $clientId, User $userId, EntityManagerInterface $em, TagAwareCacheInterface $cache): JsonResponse {
        /** @var \App\Entity\Client $connectedClient */
        $connectedClient = $this->getUser();

        if (!$connectedClient) {
            return new JsonResponse(
                [
                    'statut' => Response::HTTP_FORBIDDEN,
                    'error' => 'Unauthorized'
                ], 
                Response::HTTP_UNAUTHORIZED);
        }

        if ($connectedClient->getId() !== $clientId) {
            return new JsonResponse(
                [
                    'statut' => Response::HTTP_FORBIDDEN,
                    'error' => 'Forbiden'
                ], 
                Response::HTTP_FORBIDDEN);
        }

        $client = $userId->getClient();
        if ($client === null || $client->getId() !== $connectedClient->getId()) {
            return new JsonResponse(
                [
                    'statut' => Response::HTTP_FORBIDDEN,
                    'error' => 'User doesn\'t belong to this client'
                ], 
                Response::HTTP_FORBIDDEN);
        }

        $cache->invalidateTags(["clientUsersCache"]);
        $em->remove($userId);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
