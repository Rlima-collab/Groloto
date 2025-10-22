<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Entity\Role;
use App\Repository\RoleRepository;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si l'utilisateur est déjà connecté, rediriger vers le dashboard
        if ($this->getUser()) {
            return $this->redirectToRoute('dashboard');
        }

        // Récupérer l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Dernier nom d'utilisateur saisi par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Cette méthode peut être vide - elle sera interceptée par la clé logout dans votre firewall.');
    }

    

    #[Route('/register', name: 'app_register_choice')]
    public function registerChoice(): Response
    {
        return $this->render('auth/register_choice.html.twig');
    }

    #[Route('/register/benevole', name: 'app_register_benevole')]
    public function registerBenevole(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        RoleRepository $roleRepository,
        TokenStorageInterface $tokenStorage
    ): Response {
        $errors = [];

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');
            $prenom = $request->request->get('prenom');
            $nom = $request->request->get('nom');
            $telephone = $request->request->get('telephone');
            $remarque = $request->request->get('remarque');

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email invalide";
            }
            if (empty($password) || strlen($password) < 6) {
                $errors[] = "Le mot de passe doit faire au moins 6 caractères";
            }
            if ($password !== $confirmPassword) {
                $errors[] = "Les mots de passe ne correspondent pas";
            }

            if (empty($errors)) {
                $existingUser = $entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);
                if ($existingUser) {
                    $errors[] = "Cet email est déjà utilisé, merci d'en choisir un autre.";
                } else {
                    $role = $roleRepository->findOneBy(['nom' => 'benevole']);
                    if (!$role) {
                        $errors[] = "Rôle bénévole introuvable";
                    } else {
                        $user = new Utilisateur();
                        $user->setEmail($email);
                        $user->setMotDePasse($userPasswordHasher->hashPassword($user, $password));
                        $user->setPrenom($prenom);
                        $user->setNom($nom);
                        $user->setTelephone($telephone);
                        $user->setRole($role);
                        $user->setDateCreation(new \DateTime());
                        $user->setDateModification(new \DateTime());

                        try {
                            $entityManager->persist($user);
                            $entityManager->flush();

                            $benevole = new \App\Entity\Benevole();
                            $benevole->setUtilisateur($user);
                            $benevole->setRemarque($remarque);
                            $benevole->setActif(true);

                            $entityManager->persist($benevole);
                            $entityManager->flush();

                            $this->addFlash('success', 'Compte bénévole créé avec succès !');
                            
                            // Connecter automatiquement l'utilisateur
                            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
                            $tokenStorage->setToken($token);
                            
                            return $this->redirectToRoute('dashboard');
                        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                            $errors[] = "Cet email est déjà utilisé, merci d'en choisir un autre.";
                        }
                    }
                }
            }
        }

        return $this->render('auth/register_benevole.html.twig', [
            'errors' => $errors,
        ]);
    }



    #[Route('/register/mecene', name: 'app_register_mecene')]
    public function registerMecene(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        RoleRepository $roleRepository,
        TokenStorageInterface $tokenStorage
    ): Response {
        $errors = [];

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');
            $prenom = $request->request->get('prenom');
            $nom = $request->request->get('nom');
            $telephone = $request->request->get('telephone');
            $organisation = $request->request->get('organisation');
            $siret = $request->request->get('siret');

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email invalide";
            }
            if (empty($password) || strlen($password) < 6) {
                $errors[] = "Le mot de passe doit faire au moins 6 caractères";
            }
            if ($password !== $confirmPassword) {
                $errors[] = "Les mots de passe ne correspondent pas";
            }
            if (empty($organisation)) {
                $errors[] = "L'organisation est obligatoire";
            }
            if (empty($siret) || !preg_match('/^[0-9]{14}$/', $siret)) {
                $errors[] = "Le numéro SIRET doit contenir exactement 14 chiffres";
            }

            if (empty($errors)) {
                $existingUser = $entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);
                if ($existingUser) {
                    $errors[] = "Cet email est déjà utilisé, merci d'en choisir un autre.";
                } else {
                    $role = $roleRepository->findOneBy(['nom' => 'mecene']);
                    if (!$role) {
                        $errors[] = "Rôle mécène introuvable";
                    } else {
                        $user = new Utilisateur();
                        $user->setEmail($email);
                        $user->setMotDePasse($userPasswordHasher->hashPassword($user, $password));
                        $user->setPrenom($prenom);
                        $user->setNom($nom);
                        $user->setTelephone($telephone);
                        $user->setRole($role);
                        $user->setDateCreation(new \DateTime());
                        $user->setDateModification(new \DateTime());

                        try {
                            $entityManager->persist($user);
                            $entityManager->flush();

                            $mecene = new \App\Entity\Mecene();
                            $mecene->setUtilisateur($user);
                            $mecene->setOrganisation($organisation);
                            $mecene->setSiret($siret);

                            $entityManager->persist($mecene);
                            $entityManager->flush();

                            $this->addFlash('success', 'Compte mécène créé avec succès !');
                            
                            // Connecter automatiquement l'utilisateur
                            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
                            $tokenStorage->setToken($token);
                            
                            return $this->redirectToRoute('dashboard');
                        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                            $errors[] = "Cet email est déjà utilisé, merci d'en choisir un autre.";
                        }
                    }
                }
            }
        }

        return $this->render('auth/register_mecene.html.twig', [
            'errors' => $errors,
        ]);
    }


    #[Route('/profile', name: 'app_profile')]
    public function profile(): Response
    {
        // Vérifier que l'utilisateur est connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $user = $this->getUser();
        
        return $this->render('auth/profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit')]
    public function editProfile(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // Vérifier que l'utilisateur est connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        /** @var Utilisateur $user */
        $user = $this->getUser();
        
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données du formulaire
            $currentPassword = $form->get('currentPassword')->getData();
            $newPassword = $form->get('newPassword')->getData();
            $confirmPassword = $form->get('confirmPassword')->getData();

            // Vérifier le mot de passe actuel
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'Le mot de passe actuel est incorrect.');
                return $this->render('auth/profile_edit.html.twig', [
                    'form' => $form->createView(),
                    'user' => $user,
                ]);
            }

            // Si un nouveau mot de passe est fourni, le valider et le mettre à jour
            if (!empty($newPassword)) {
                if ($newPassword !== $confirmPassword) {
                    $this->addFlash('error', 'Les nouveaux mots de passe ne correspondent pas.');
                    return $this->render('auth/profile_edit.html.twig', [
                        'form' => $form->createView(),
                        'user' => $user,
                    ]);
                }

                if (strlen($newPassword) < 6) {
                    $this->addFlash('error', 'Le nouveau mot de passe doit contenir au moins 6 caractères.');
                    return $this->render('auth/profile_edit.html.twig', [
                        'form' => $form->createView(),
                        'user' => $user,
                    ]);
                }

                // Hasher et enregistrer le nouveau mot de passe
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setMotDePasse($hashedPassword);
            }

            // Vérifier si l'email a changé et s'il n'est pas déjà utilisé
            $originalEmail = $entityManager->getUnitOfWork()->getOriginalEntityData($user)['email'] ?? null;
            if ($user->getEmail() !== $originalEmail) {
                $existingUser = $entityManager->getRepository(Utilisateur::class)
                    ->findOneBy(['email' => $user->getEmail()]);
                
                if ($existingUser && $existingUser->getId() !== $user->getId()) {
                    $this->addFlash('error', 'Cette adresse email est déjà utilisée par un autre compte.');
                    return $this->render('auth/profile_edit.html.twig', [
                        'form' => $form->createView(),
                        'user' => $user,
                    ]);
                }
            }

            // Mettre à jour la date de modification
            $user->setDateModification(new \DateTime());

            try {
                $entityManager->flush();
                $this->addFlash('success', 'Votre profil a été mis à jour avec succès !');
                return $this->redirectToRoute('app_profile');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour de votre profil.');
            }
        }

        return $this->render('auth/profile_edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

}