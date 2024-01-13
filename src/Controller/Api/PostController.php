<?php

namespace App\Controller\Api;

use App\Entity\Category;
use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PostController extends AbstractController
{
    #[Route(path: 'api/category/{id}/post/new', name: 'api_post_new', methods: ['POST'])]
    public function newPost(
        SerializerInterface $serializer,
        Request $request,
        Category $category,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse
    {
        if (!$this->getUser()) {
            return new JsonResponse($serializer->serialize(['message' => 'you must be logged'], 'json'), Response::HTTP_UNAUTHORIZED, [], true);
        }

        $postRequest = $request->request->all();
        $file = $request->files->get('imageName');
        $post = new Post();
        if (!empty($file)) {
            $fileExt = ['png', 'PNG', 'jpg', 'JPG', 'JPEG', 'jpeg'];
            if (in_array($file->guessExtension(), $fileExt, true)) {
                $imageName = md5(uniqid("",true)).'.'.$file->guessExtension();
                $file->move($this->getParameter('app.image.dir'),$imageName);

                $post->setImageName($imageName);
            }
        }

        $post->setTitle($postRequest['title']);
        $post->setContent($postRequest['content']);
        $post->setCategory($category);
        $user = $this->getUser();
        $post->setUser($user);

        $entityManager->persist($post);
        $entityManager->flush();

        return new JsonResponse($serializer->serialize(['message' => 'Votre post a ete crée'], 'json'), Response::HTTP_CREATED, [], true);
    }

    #[Route(path: 'api/post/{id}/delete', name: 'api_post_delete', methods: ['DELETE'])]
    public function deletePost(
        SerializerInterface $serializer,
        Post $post,
        EntityManagerInterface $entityManager,
    ): JsonResponse
    {
        if (!$this->getUser()) {
            return new JsonResponse($serializer->serialize(['message' => 'you must be logged'], 'json'), Response::HTTP_UNAUTHORIZED, [], true);
        }

        if ($this->getUser() === $post->getUser()) {

            $entityManager->remove($post);
            $entityManager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }


        return new JsonResponse($serializer->serialize(['message' => "Vous n'êtes pas autorisé a supprimer ce post"], 'json'), Response::HTTP_UNAUTHORIZED, [], true);
    }

}