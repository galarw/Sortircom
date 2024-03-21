<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SiteController extends AbstractController
{
    #[Route(path: "site", name: 'app_site')]
    public function site(Request $request): Response
    {
        // Vérifier les permissions de l'utilisateur
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home');
        }

        // Code de la page Site si l'utilisateur a les permissions
        return $this->render('site/site.html.twig');
    }
}