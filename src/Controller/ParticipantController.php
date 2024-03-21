<?php

namespace App\Controller;

use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends AbstractController
{
    #[Route('/participant/{id}', name: 'participant_profile')]
    public function profile(int $id, EntityManagerInterface $em): Response
    {
        $participant = $em->getRepository(Participant::class)->find($id);

        if (!$participant) {
            throw $this->createNotFoundException('Le participant n\'existe pas.');
        }

        return $this->render('participant/profile.html.twig', [
            'participant' => $participant,
        ]);
    }

    #[Route('/participant/{id}/toggle-active', name: 'participant_toggle_active', methods: ['GET'])]
    public function toggleActive(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $participant = $entityManager->getRepository(Participant::class)->find($id);

        if (!$participant) {
            throw $this->createNotFoundException('Le participant n\'existe pas.');
        }


        $participant->setActif(false);
        $entityManager->flush();

        $this->addFlash('success', 'Participant rendu inactif avec succès.');

        // Redirigez l'utilisateur où vous le souhaitez
        return $this->redirectToRoute('app_home', ['id' => $id]);
    }
}
