<?php

namespace App\Controller\Api;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryController extends AbstractController
{
    #[Route(path: 'api/category', name: 'api_category_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository, SerializerInterface $serializer): JsonResponse
    {
        $categories =  $categoryRepository->findAll();

        $jsonCategories = $serializer->serialize($categories, 'json');

        return new JsonResponse($jsonCategories, Response::HTTP_OK, [], true);


    }
    #[Route(path: 'api/category/new', name: 'api_category_add', methods: ['POST'])]
    public function addCategory(
        SerializerInterface $serializer,
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse
    {
        if (!$this->getUser()) {
            return new JsonResponse($serializer->serialize(['message' => 'you must be logged'], 'json'), Response::HTTP_UNAUTHORIZED, [], true);
        }
        $category = $serializer->deserialize($request->getContent(), Category::class, 'json');

        $error = $validator->validate($category);

        if ($error->count() > 0 ) {
            return new JsonResponse($serializer->serialize($error, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $entityManager->persist($category);
        $entityManager->flush();

        $jsonCategory = $serializer->serialize($category, 'json');

        return new JsonResponse($jsonCategory, Response::HTTP_CREATED, [], true);
    }

    #[Route(path: 'api/category/{id}/update', name: 'api_category_update', methods: ['POST'])]
    public function updateCategory(Category $category,SerializerInterface $serializer, EntityManagerInterface $entityManager, Request $request)
    {
        if (!$this->getUser()) {
            return new JsonResponse($serializer->serialize(['message' => 'you must be logged'], 'json'), Response::HTTP_UNAUTHORIZED, [], true);
        }

        $updateCategory = $serializer->deserialize($request->getContent(), Category::class, 'json');
        $category->setName($updateCategory->getName());
        $entityManager->flush();

        return new JsonResponse($serializer->serialize(['message' => 'you category has been updated'], 'json'), Response::HTTP_OK, [], true);

    }

    #[Route(path: 'api/category/{id}/delete', name: 'api_category_delete', methods: ['DELETE'])]
    public function deleteCategory(
        SerializerInterface $serializer,
        Category $category,
        EntityManagerInterface $entityManager,
    ): JsonResponse
    {
        if (!$this->getUser()) {
            return new JsonResponse($serializer->serialize(['message' => 'you must be logged'], 'json'), Response::HTTP_UNAUTHORIZED, [], true);
        }

        $entityManager->remove($category);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}