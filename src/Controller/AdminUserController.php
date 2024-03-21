<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\ResetPasswordRequest;
use App\Form\ParticipantType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminUserController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }
    #[Route('/admin/users', name: 'app_users')]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        // Vérifiez si l'utilisateur a le rôle ADMIN
        if (!$this->isGranted('ROLE_ADMIN')) {
            // Redirigez l'utilisateur vers la page d'accueil ou une autre page
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            $selectedIds = $request->request->all()['selected_ids'] ?? [];

            // Convertir les valeurs en entiers
            $selectedIds = array_map('intval', $selectedIds);

            // Supprimer les éléments vides et doublons
            $selectedIds = array_unique(array_filter($selectedIds));

            if (!empty($selectedIds)) {
                foreach ($selectedIds as $userId) {
                    // Supprimer les demandes de réinitialisation de mot de passe associées
                    $resetPasswordRequests = $entityManager->getRepository(ResetPasswordRequest::class)->findBy(['user' => $userId]);
                    foreach ($resetPasswordRequests as $request) {
                        $entityManager->remove($request);
                    }

                    // Supprimer l'utilisateur
                    $user = $entityManager->getRepository(Participant::class)->find($userId);
                    if ($user) {
                        $entityManager->remove($user);
                    }
                }

                $entityManager->flush();

                $this->addFlash('success', count($selectedIds) . ' utilisateur(s) ont été supprimé(s) avec succès.');
            } else {
                $this->addFlash('error', 'Aucun utilisateur sélectionné.');
            }
        }

        $participants = $entityManager->getRepository(Participant::class)->findAll();

        return $this->render('admin/index.html.twig', [
            'participants' => $participants,
        ]);
    }


    #[Route('/admin/users/create', name: 'admin_user_create')]
    public function createUser(Request $request): Response
    {

        if (!$this->isGranted('ROLE_ADMIN')) {

            return $this->redirectToRoute('app_home');
        }
        $user = new Participant();
        $form = $this->createForm(ParticipantType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Hacher le mot de passe avant de le stocker
            $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);

            // Persister l'utilisateur dans la base de données
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Utilisateur créé avec succès.');

            return $this->redirectToRoute('app_users');
        }

        return $this->render('admin/create_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}