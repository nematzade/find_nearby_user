<?php

namespace App\Controller;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthController extends ApiController
{
    /**
     * @Route("/register", name="register",methods={"POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function register(Request $request,UserPasswordEncoderInterface $encoder)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->transformJsonBody($request);
        $username = $request->get('username');
        $password = $request->get('password');

        if (empty($username) || empty($password)){
            return $this->respondValidationError('Invalid username or password!');
        }

        $user = new User();
        $user->setUsername($username);
        $user->setPassword($encoder->encodePassword($user,$password));

        $em->persist($user);
        $em->flush();

        return $this->respondWithSuccess(sprintf('user %s successfully created!',$user->getUsername()));
    }

    /**
     * @param UserInterface $user
     * @param JWTTokenManagerInterface $JWTTokenManager
     * @return JsonResponse
     * @Route("/api/login_check",name="api_login_check")
     */
    public function getTokenUser(UserInterface $user,JWTTokenManagerInterface $JWTTokenManager)
    {
        return new JsonResponse(['token' => $JWTTokenManager->create($user)]);
    }
}
