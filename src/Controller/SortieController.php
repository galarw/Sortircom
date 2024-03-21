<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\SortieFormType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;





class SortieController extends AbstractController
{
    #[Route(path: "/sortie", name: 'app_sortie')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $sortie = new Sortie();


        $form = $this->createForm(SortieFormType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $organisateur = $this->getUser();
            $sortie->setOrganisateur($organisateur);
            $sortie->setSite($organisateur->getSite());

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Sortie créée avec succès !');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('sortie/sortie.html.twig', [
            'sortieForm' => $form->createView(),
        ]);
    }


    #[Route('/sortie/{id}', name: 'sortie_detail')]
    public function show(int $id, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException('La sortie demandée n\'existe pas.');
        }

        // Récupérer les participants inscrits à la sortie
        $participants = $sortie->getParticipants();

        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
            'participants' => $participants,
        ]);
    }


    #[Route('/sortie/inscription/{id}', name: 'sortie_inscription')]
    public function inscription(int $id, EntityManagerInterface $entityManager, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);
        $user = $this->getUser();

        if (!$sortie || !$user) {
            throw $this->createNotFoundException('La sortie ou l\'utilisateur n\'existe pas.');
        }

        $now = new \DateTime();
        $etatFerme = $entityManager->getRepository(Etat::class)->find(5);
        $etatOuvert = $entityManager->getRepository(Etat::class)->find(2);

        if ($sortie->getEtat() === $etatFerme || $sortie->getEtat() !== $etatOuvert || $now > $sortie->getDateLimiteInscription()) {
            $this->addFlash('error', 'Inscription impossible : sortie non ouverte, fermée ou date limite dépassée.');
            return $this->redirectToRoute('sortie_detail', ['id' => $id]);
        }

        if ($sortie->getParticipants()->contains($user)) {
            $this->addFlash('error', 'Vous êtes déjà inscrit à cette sortie.');
            return $this->redirectToRoute('sortie_detail', ['id' => $id]);
        }

        if ($sortie->getParticipants()->count() >= $sortie->getNmInscriptionMax()) {
            $this->addFlash('error', 'La sortie est complète.');
            return $this->redirectToRoute('sortie_detail', ['id' => $id]);
        }

        $sortie->addParticipant($user);
        $entityManager->flush();

        $this->addFlash('success', 'Inscription confirmée.');
        return $this->redirectToRoute('sortie_detail', ['id' => $id]);
    }

    #[Route('/sortie/desinscription/{id}', name: 'sortie_desinscription')]
    public function desinscription(int $id, EntityManagerInterface $entityManager, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);
        $user = $this->getUser();

        if (!$sortie || !$user) {
            throw $this->createNotFoundException('La sortie ou l\'utilisateur n\'existe pas.');
        }

        if (!$sortie->getParticipants()->contains($user)) {
            $this->addFlash('error', 'Vous n\'êtes pas inscrit à cette sortie.');
            return $this->redirectToRoute('sortie_detail', ['id' => $id]);
        }

        if (new \DateTime() > $sortie->getDateLimiteInscription()) {
            $this->addFlash('error', 'La date de clôture est dépassée, désinscription impossible.');
            return $this->redirectToRoute('sortie_detail', ['id' => $id]);
        }

        $sortie->removeParticipant($user);
        $entityManager->flush();

        $this->addFlash('success', 'Vous avez été désinscrit de la sortie.');

        return $this->redirectToRoute('sortie_detail', ['id' => $id]);
    }


    #[Route('/sortie/annuler/{id}', name: 'sortie_annuler')]
    public function annuler(int $id, EntityManagerInterface $entityManager, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);
        $user = $this->getUser();

        if (!$sortie || !$user) {
            throw $this->createNotFoundException('La sortie ou l\'utilisateur n\'existe pas.');
        }

        if ($sortie->getOrganisateur() !== $user) {
            $this->addFlash('error', 'Vous n\'êtes pas l\'organisateur de cette sortie.');
            return $this->redirectToRoute('sortie_detail', ['id' => $id]);
        }

        if (new \DateTime() < $sortie->getDateDebutInscription()) {
            $etatAnnule = $entityManager->getRepository(Etat::class)->find(6);
            $sortie->setEtat($etatAnnule);
            $entityManager->flush();
            $this->addFlash('success', 'La sortie a été annulée.');
        } else {
            $this->addFlash('error', 'La sortie ne peut pas être annulée après la date de début d\'inscription.');
        }

        return $this->redirectToRoute('app_home');
    }


    #[Route('/sortie/edit/{id}', name: 'sortie_edit')]
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SortieFormType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'La sortie a été modifiée avec succès.');

            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/edit.html.twig', [
            'sortieForm' => $form->createView(),
        ]);
    }
}