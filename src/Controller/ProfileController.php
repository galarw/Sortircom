<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class Profile extends AbstractController
{
    #[Route(path:"/profile", name: 'app_profile')]
    public function profile(): Response
    {
        // ...
    }
}