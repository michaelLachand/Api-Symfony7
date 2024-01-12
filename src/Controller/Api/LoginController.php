<?php

namespace App\Controller\Api;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;


class LoginController extends AbstractController
{
    #[Route(path: 'api/login', name: 'api_login', methods: ['POST'])]
    public function ApiLogin(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $userData = [
            'email' => $user->getEmail(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
        ];

        return new JsonResponse(json_encode($userData, JSON_THROW_ON_ERROR));
    }

}