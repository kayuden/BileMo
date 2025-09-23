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

final class ProductController extends AbstractController
{
    #[Route('/api/products', name: 'products', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the list of products',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type:Product::class))
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

        $idCache = "getAllProducts-" . $page . "-" . $limit;

        $productList = $cache->get($idCache, function (ItemInterface $item) use ($productRepository, $page, $limit) {
            echo ("THE ELEMENT ISN'T YET CACHED ! \n");
            $item->tag("productsCache");
            /** @var int $page */
            /** @var int $limit */
            return $productRepository->findAllWithPagination($page, $limit);
        });
        
        $jsonProductList = $serializer->serialize($productList, 'json');
        return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/products/{productId}', name: 'detailProduct', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the detail of a product',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type:Product::class))
        )
    )]
    #[OA\Tag(name: 'Products')]
    public function getProductDetails(int $productId, ProductRepository $productRepository, SerializerInterface $serializer, VersioningService $versioningService): JsonResponse
    {
        $product = $productRepository->find($productId);
        if ($product) {
            $version = $versioningService->getVersion();
            $context = SerializationContext::create()->setVersion($version);
            $jsonProduct = $serializer->serialize($product, 'json', $context);
            return new JsonResponse($jsonProduct, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
