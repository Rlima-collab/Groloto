<?php

namespace App\Command;

use App\Entity\Role;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Créer un utilisateur administrateur',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Vérifier si le rôle admin existe
        $adminRole = $this->entityManager->getRepository(Role::class)->findOneBy(['nom' => 'admin']);
        if (!$adminRole) {
            $io->error('Le rôle "admin" n\'existe pas dans la base de données. Veuillez d\'abord exécuter les migrations.');
            return Command::FAILURE;
        }

        $email = $io->ask('Email de l\'administrateur', 'admin@groloto.com');
        $password = $io->askHidden('Mot de passe', null, function ($value) {
            if (empty($value)) {
                throw new \Exception('Le mot de passe ne peut pas être vide');
            }
            if (strlen($value) < 6) {
                throw new \Exception('Le mot de passe doit contenir au moins 6 caractères');
            }
            return $value;
        });

        // Vérifier si l'utilisateur existe déjà
        $existingUser = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $io->error('Un utilisateur avec cet email existe déjà.');
            return Command::FAILURE;
        }

        $prenom = $io->ask('Prénom (optionnel)', 'Admin');
        $nom = $io->ask('Nom (optionnel)', 'Système');

        // Créer l'utilisateur admin
        $admin = new Utilisateur();
        $admin->setEmail($email);
        $admin->setMotDePasse($this->passwordHasher->hashPassword($admin, $password));
        $admin->setRole($adminRole);
        $admin->setPrenom($prenom);
        $admin->setNom($nom);
        $admin->setDateCreation(new \DateTime());
        $admin->setDateModification(new \DateTime());

        $this->entityManager->persist($admin);
        $this->entityManager->flush();

        $io->success([
            'Utilisateur administrateur créé avec succès !',
            'Email: ' . $email,
            'Vous pouvez maintenant vous connecter sur l\'application.'
        ]);

        return Command::SUCCESS;
    }
}