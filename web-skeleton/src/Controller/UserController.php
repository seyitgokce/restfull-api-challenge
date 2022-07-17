<?php

namespace App\Controller;

use App\Service\UserService;
use \Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;



class UserController extends AbstractController
{

    /**
     * @var UserService
     */
    private $service;

    public function __construct(UserService $service)
    {

        $this->service = $service;
    }


    /**
     * @Route("/register")
     */
    public function register()
    {

        $user = $this->getUser();


        $this->service->newUser(
            'info@company1.com',
            'company1'
        );

        return new Response('home');
    }



    /**
     * @Route("/api/my-profile", name="api_my_profile", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     * @return Response
     */
    public function profile()
    {
        $user = $this->getUser();


        return $this->json([
            $user->getUsername()
        ]);
    }

}