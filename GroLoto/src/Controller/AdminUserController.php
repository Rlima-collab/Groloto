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
        ]);
    }
}
