<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegisterController extends AbstractController
{
    #[Route('/api/register', name: 'api_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
    ): Response
    {

        if ($this->getUser()) {
            return new JsonResponse($serializer->serialize(['message' => 'Vous etes déjà connecté'], 'json'), Response::HTTP_UNAUTHORIZED);
        }

        $newUser = $serializer->deserialize($request->getContent(),User::class,'json');

        $error = $validator->validate($newUser);

        if ($error->count() > 0 ) {
            return new JsonResponse($serializer->serialize($error, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $getPassword = $newUser->getPassword();

        $user = $this->getUser();

            // encode the plain password
        $newUser->setPassword(
            $userPasswordHasher->hashPassword(
                $newUser,
                $getPassword
            )
        );

        $entityManager->persist($newUser);
        $entityManager->flush();
        // do anything else you need here, like send an email
        return new JsonResponse($serializer->serialize(['message' => 'Votre compte a ete cree'], 'json'), Response::HTTP_OK, ['accept' => 'application/json'], true);
    }

}