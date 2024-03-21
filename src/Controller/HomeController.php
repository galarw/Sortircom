<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class Home extends AbstractController
{
    #[Route(path: "")]
    #[Route(path: "home", name: 'app_home')]
    public function home(Request $request): Response
    {

        return $this->render('home/index.html.twig');
    }
}