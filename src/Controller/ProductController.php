<?php

namespace App\Controller;

use App\Entity\Product;
use OpenApi\Attributes as OA;
use App\Service\VersioningService;
use App\Repository\ProductRepository;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use JMS\Serializer\SerializationContext; 
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class ProductController extends AbstractController
{
    #[Route('/api/products', name: 'products', methods: ['GET'])]
    #[OA\Get(
        path: "/api/products",
        summary: "View the list of BileMo products",
        description: "Retrieve the complete list of BileMo products available in the catalog. Supports pagination and can be used by client applications to display the product catalog."
    )]
    // 200 OK
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Returns the list of products',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type:Product::class))
        )
    )]
    // 400 Bad Request
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Invalid query parameters (page or limit not valid)',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 400),
                new OA\Property(property: 'message', type: 'string', example: 'The "page" parameter must be greater than 0')
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
        description: 'No products found',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 404),
                new OA\Property(property: 'message', type: 'string', example: 'No products found')
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
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'The page you want to display',
        schema: new OA\Schema(type: 'int')
    )]
    #[OA\Parameter(
        name: 'limit',
        in: 'query',
        description: 'The number of items you want to display',
        schema: new OA\Schema(type: 'int')
    )]
    #[OA\Tag(name: 'Products')]
    public function getAllProducts(ProductRepository $productRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = (int) $request->get('page', 1);
        $limit = (int) $request->get('limit', 3);

        if ($page < 1) {
            throw new BadRequestHttpException('The "page" parameter must be greater than 0');
        }
        if ($limit < 1 || $limit > 100) {
            throw new BadRequestHttpException('The "limit" parameter must be between 1 and 100');
        }

        $idCache = "getAllProducts-" . $page . "-" . $limit;

        $productList = $cache->get($idCache, function (ItemInterface $item) use ($productRepository, $page, $limit) {
            echo ("THE ELEMENT ISN'T YET CACHED ! \n");
            $item->tag("productsCache");
            /** @var int $page */
            /** @var int $limit */
            return $productRepository->findAllWithPagination($page, $limit);
        });

        if (count($productList) === 0) {
            throw new NotFoundHttpException('No products found');
        }
        
        $jsonProductList = $serializer->serialize($productList, 'json');
        return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/products/{productId}', name: 'detailProduct', methods: ['GET'])]
    #[OA\Get(
        path: "/api/products/{productId}",
        summary: "View the details of a BileMo product",
        description: "Retrieve detailed information about a specific BileMo product using its unique identifier ('productId')."
    )]
    // 200 OK
    #[OA\Response(
        response: 200,
        description: 'Returns the detail of a product',
        content: new OA\JsonContent(
            ref: new Model(type: Product::class)
        )
    )]
    // 400 Bad Request
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Invalid productId',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 400),
                new OA\Property(property: 'message', type: 'string', example: 'The "productId" parameter must be greater than 0')
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
        description: 'Product not found for the given productId',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'status', type: 'integer', example: 404),
                new OA\Property(property: 'message', type: 'string', example: 'Product not found')
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
    #[OA\Tag(name: 'Products')]
    public function getProductDetails(int $productId, ProductRepository $productRepository, SerializerInterface $serializer, VersioningService $versioningService): JsonResponse
    {
        $product = $productRepository->find($productId);

        if ($productId <= 0) {
            return new JsonResponse(
                [
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => 'The "productId" parameter must be greater than 0'
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($product) {
            $version = $versioningService->getVersion();
            $context = SerializationContext::create()->setVersion($version);
            $jsonProduct = $serializer->serialize($product, 'json', $context);
            return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(
            [
            'status' => Response::HTTP_NOT_FOUND,
            'message' => 'Product not found'
            ], 
            Response::HTTP_NOT_FOUND
        );
    }
}
