<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Entity\Role;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
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

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        RoleRepository $roleRepository,
        ValidatorInterface $validator
    ): Response {
        // Si l'utilisateur est déjà connecté, rediriger vers le dashboard
        if ($this->getUser()) {
            return $this->redirectToRoute('dashboard');
        }

        $user = new Utilisateur();
        $errors = [];

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');
            $prenom = $request->request->get('prenom');
            $nom = $request->request->get('nom');
            $telephone = $request->request->get('telephone');
            $roleId = $request->request->get('role');

            // Validation des données
            if (empty($email)) {
                $errors[] = 'L\'email est obligatoire';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'L\'email n\'est pas valide';
            }

            if (empty($password)) {
                $errors[] = 'Le mot de passe est obligatoire';
            } elseif (strlen($password) < 6) {
                $errors[] = 'Le mot de passe doit contenir au moins 6 caractères';
            }

            if ($password !== $confirmPassword) {
                $errors[] = 'Les mots de passe ne correspondent pas';
            }

            if (empty($roleId)) {
                $errors[] = 'Vous devez sélectionner un rôle';
            }

            // Vérifier si l'email n'existe pas déjà
            $existingUser = $entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);
            if ($existingUser) {
                $errors[] = 'Cette adresse email est déjà utilisée';
            }

            // Si pas d'erreurs, créer l'utilisateur
            if (empty($errors)) {
                $role = $roleRepository->find($roleId);
                if (!$role) {
                    $errors[] = 'Rôle invalide';
                } else {
                    $user->setEmail($email);
                    $user->setMotDePasse($userPasswordHasher->hashPassword($user, $password));
                    $user->setPrenom($prenom);
                    $user->setNom($nom);
                    $user->setTelephone($telephone);
                    $user->setRole($role);
                    $user->setDateCreation(new \DateTime());
                    $user->setDateModification(new \DateTime());

                    // Valider l'entité
                    $validationErrors = $validator->validate($user);
                    if (count($validationErrors) > 0) {
                        foreach ($validationErrors as $error) {
                            $errors[] = $error->getMessage();
                        }
                    } else {
                        $entityManager->persist($user);
                        $entityManager->flush();

                        $this->addFlash('success', 'Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.');
                        return $this->redirectToRoute('app_login');
                    }
                }
            }
        }

        // Récupérer tous les rôles pour le formulaire
        $roles = $roleRepository->findAll();

        return $this->render('auth/register.html.twig', [
            'user' => $user,
            'roles' => $roles,
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
}