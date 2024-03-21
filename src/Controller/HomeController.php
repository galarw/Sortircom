<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieFiltreFormType;
use App\Repository\EtatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\SortieRepository;


class HomeController extends AbstractController
{
    #[Route(path: "/")]
    #[Route(path: "home", name: 'app_home')]
    public function home(Request $request, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }


        $formFilter = $this->createForm(SortieFiltreFormType::class);
        $formFilter->handleRequest($request);


        // Récupération des valeurs de filtrage du formulaire
        $filters = $formFilter->getData();
        $sorties = $entityManager->getRepository(Sortie::class)->findSortiesFiltrees($filters, $this->getUser()->getId());

        // Si aucun filtre n'est appliqué, rechargez toutes les sorties
        if (!$formFilter->isSubmitted() || !$formFilter->isValid() || $this->isFiltersEmpty($filters)) {
            $sorties = $entityManager->getRepository(Sortie::class)->findAll();
        } else {
            // Appliquer le filtrage
            $sorties = $entityManager->getRepository(Sortie::class)->findSortiesFiltrees($filters, $this->getUser()->getId());
        }

        foreach ($sorties as $sortie) {
            $etatRepository->updateEtat($sortie); // On met à jour l'état
            $entityManager->persist($sortie);
        }
        $entityManager->flush();

        return $this->render('home/index.html.twig', [
            'formFilter' => $formFilter->createView(),
            'sorties' => $sorties,
        ]);
    }

    //
    private function isFiltersEmpty($filters)
    {
        foreach ($filters as $filter) {
            if (!empty($filter)) {
                return false;
            }
        }
        return true;
    }
}
