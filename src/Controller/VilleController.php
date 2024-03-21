<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Form\VilleFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class VilleController extends AbstractController
{
    #[Route("/ville", name: "app_ville")]
    public function ville(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_home');
        }

        $ville = new Ville();
        $lieu = new Lieu();
        $ville->getLieus()->add($lieu);
        $form = $this->createForm(VilleFormType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ville);
            foreach ($ville->getLieus() as $lieu) {
                $lieu->setVille($ville);
                $entityManager->persist($lieu);
            }
            $entityManager->flush();
            $this->addFlash('success', 'La ville et les lieux associés ont été ajoutés avec succès.');
            return $this->redirectToRoute('app_ville');
        }

        return $this->render('ville/ville.html.twig', [
            'villeForm' => $form->createView(),
        ]);
    }
}
