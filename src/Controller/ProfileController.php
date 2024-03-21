<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route(path: "/profile", name: 'app_profile')]
    public function profile(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        $participant = $this->getUser();
        $profileForm = $this->createForm(ProfileFormType::class, $participant);
        $profileForm->handleRequest($request);

        if ($profileForm->isSubmitted() && $profileForm->isValid()) {
            $participant = $profileForm->getData();

            // Vérifier si un nouveau mot de passe a été saisi
            $plainPassword = $profileForm->get('plainPassword')->getData();
            if ($plainPassword) {
                // Encoder le nouveau mot de passe
                $hashedPassword = $userPasswordHasher->hashPassword($participant, $plainPassword);
                // Mettre à jour le mot de passe de l'utilisateur
                $participant->setPassword($hashedPassword);
            }

            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/profile.html.twig', [
            'profileForm' => $profileForm->createView(),
        ]);
    }
}
