<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\VersioningService;
use JMS\Serializer\SerializerInterface;
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
    public function getAllProducts(ProductRepository $productRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getAllProducts-" . $page . "-" . $limit;

        $productList = $cache->get($idCache, function (ItemInterface $item) use ($productRepository, $page, $limit) {
            echo ("THE ELEMENT ISN'T YET CACHED ! \n");
            $item->tag("productsCache");
            return $productRepository->findAllWithPagination($page, $limit);
        });
        
        $jsonProductList = $serializer->serialize($productList, 'json');
        return new JsonResponse($jsonProductList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/products/{productId}', name: 'detailProduct', methods: ['GET'])]
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
