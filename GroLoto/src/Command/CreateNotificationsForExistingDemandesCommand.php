<?php

namespace App\Command;

use App\Entity\Notification;
use App\Repository\DemandeTacheRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsCommand(
    name: 'app:create-notifications-demandes',
    description: 'Crée des notifications pour les demandes de tâches en attente existantes',
)]
class CreateNotificationsForExistingDemandesCommand extends Command
{
    public function __construct(
        private DemandeTacheRepository $demandeRepository,
        private UtilisateurRepository $utilisateurRepository,
        private EntityManagerInterface $entityManager,
        private UrlGeneratorInterface $urlGenerator
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $demandesEnAttente = $this->demandeRepository->findEnAttente();
        
        if (empty($demandesEnAttente)) {
            $io->success('Aucune demande en attente trouvée.');
            return Command::SUCCESS;
        }

        $admins = $this->utilisateurRepository->findByRoleName('admin');
        
        if (empty($admins)) {
            $io->error('Aucun administrateur trouvé.');
            return Command::FAILURE;
        }

        $count = 0;
        
        foreach ($demandesEnAttente as $demande) {
            $benevole = $demande->getBenevole();
            $tache = $demande->getTache();
            $benevoleNom = $benevole->getUtilisateur()->getPrenom() . ' ' . $benevole->getUtilisateur()->getNom();
            
            foreach ($admins as $admin) {
                $notification = new Notification();
                $notification->setDestinataire($admin);
                $notification->setMessage("{$benevoleNom} a demandé à rejoindre la tâche \"{$tache->getTitre()}\"");
                $notification->setType('demande_tache');
                $notification->setLien('/admin/demandes-taches');
                $notification->setCreatedAt($demande->getDateDemande());
                $notification->setLue(false);
                
                $this->entityManager->persist($notification);
                $count++;
            }
        }
        
        $this->entityManager->flush();
        
        $io->success("$count notification(s) créée(s) pour " . count($demandesEnAttente) . " demande(s) en attente.");

        return Command::SUCCESS;
    }
}
