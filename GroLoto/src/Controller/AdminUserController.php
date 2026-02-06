<?php
namespace App\Controller;

use App\Entity\Utilisateur;
use App\Entity\Role;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[IsGranted('ROLE_ADMIN')]
class AdminUserController extends AbstractController
{
    #[Route('/admin/create-user', name: 'admin_create_user')]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        RoleRepository $roleRepository,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $errors = [];
        $currentUser = $this->getUser();
        
        // Récupérer tous les admins
        $roleAdmin = $em->getRepository(Role::class)->findOneBy(['nom' => 'admin']);
        $allAdmins = $roleAdmin 
            ? $em->getRepository(Utilisateur::class)->findBy(['role' => $roleAdmin])
            : [];
        // Filtrer pour exclure l'utilisateur connecté
        $admins = array_filter($allAdmins, fn($admin) => $admin->getId() !== $currentUser->getId());

        if ($request->isMethod('POST')) {
            $email = trim($request->request->get('email', ''));
            $password = $request->request->get('password', '');
            $confirm = $request->request->get('confirm_password', '');
            $prenom = trim($request->request->get('prenom', ''));
            $nom = trim($request->request->get('nom', ''));

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email invalide.';
            }
            if (empty($password) || strlen($password) < 6) {
                $errors[] = 'Mot de passe trop court (>= 6).';
            }
            if ($password !== $confirm) {
                $errors[] = 'Les mots de passe ne correspondent pas.';
            }

            if (empty($errors)) {
                $existing = $em->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);
                if ($existing) {
                    $errors[] = 'Cet email est déjà utilisé.';
                } else {
                    $role = $roleRepository->findOneBy(['nom' => 'admin']);
                    if (!$role) {
                        $errors[] = 'Rôle admin introuvable.';
                    } else {
                        $user = new Utilisateur();
                        $user->setEmail($email);
                        $user->setPrenom($prenom ?: null);
                        $user->setNom($nom ?: null);
                        $user->setRole($role);
                        $user->setMotDePasse($passwordHasher->hashPassword($user, $password));
                        $user->setDateCreation(new \DateTime());
                        $user->setDateModification(new \DateTime());

                        try {
                            $em->persist($user);
                            $em->flush();
                            $this->addFlash('success', 'Compte administrateur créé.');
                            return $this->redirectToRoute('dashboard');
                        } catch (\Exception $e) {
                            $errors[] = 'Erreur lors de la création du compte.';
                        }
                    }
                }
            }
        }

        return $this->render('admin/create_user.html.twig', [
            'errors' => $errors,
            'admins' => $admins,
        ]);
    }

    #[Route('/admin/manage-admins', name: 'admin_manage')]
    public function manage(EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser();
        $roleAdmin = $em->getRepository(Role::class)->findOneBy(['nom' => 'admin']);
        $allAdmins = $roleAdmin 
            ? $em->getRepository(Utilisateur::class)->findBy(['role' => $roleAdmin])
            : [];
        // Filtrer pour exclure l'utilisateur connecté
        $admins = array_filter($allAdmins, fn($admin) => $admin->getId() !== $currentUser->getId());

        return $this->render('admin/manage_admins.html.twig', [
            'admins' => $admins,
        ]);
    }

    #[Route('/admin/delete-admin/{id}', name: 'admin_delete', methods: ['POST'])]
    public function deleteAdmin(
        Utilisateur $admin,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        $csrfToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete' . $admin->getId(), $csrfToken)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_manage');
        }

        // Empêcher la suppression du dernier admin
        $roleAdmin = $em->getRepository(Role::class)->findOneBy(['nom' => 'admin']);
        $adminCount = $em->getRepository(Utilisateur::class)->count(['role' => $roleAdmin]);
        
        if ($adminCount <= 1) {
            $this->addFlash('error', 'Impossible de supprimer le dernier administrateur.');
            return $this->redirectToRoute('admin_manage');
        }

        try {
            $em->remove($admin);
            $em->flush();
            $this->addFlash('success', 'Administrateur supprimé.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la suppression.');
        }

        return $this->redirectToRoute('admin_manage');
    }

    #[Route('/admin/edit-admin/{id}', name: 'admin_edit')]
    public function editAdmin(
        Utilisateur $admin,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $errors = [];

        if ($request->isMethod('POST')) {
            $prenom = trim($request->request->get('prenom', ''));
            $nom = trim($request->request->get('nom', ''));
            $password = $request->request->get('password', '');
            $confirm = $request->request->get('confirm_password', '');

            if (!empty($password)) {
                if (strlen($password) < 6) {
                    $errors[] = 'Mot de passe trop court (>= 6).';
                } elseif ($password !== $confirm) {
                    $errors[] = 'Les mots de passe ne correspondent pas.';
                } else {
                    $admin->setMotDePasse($passwordHasher->hashPassword($admin, $password));
                }
            }

            if (empty($errors)) {
                $admin->setPrenom($prenom ?: null);
                $admin->setNom($nom ?: null);
                $admin->setDateModification(new \DateTime());

                try {
                    $em->flush();
                    $this->addFlash('success', 'Administrateur modifié.');
                    return $this->redirectToRoute('admin_manage');
                } catch (\Exception $e) {
                    $errors[] = 'Erreur lors de la modification.';
                }
            }
        }

        return $this->render('admin/edit_admin.html.twig', [
            'admin' => $admin,
            'errors' => $errors,
        ]);
    }
}
