<?php

namespace App\Command;

use App\Entity\Creneau;
use App\Entity\Evenement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:insert-weekend-slots',
    description: 'Insert sample weekend slots for testing the planning feature'
)]
class InsertWeekendSlotsCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Insertion des créneaux de week-end pour les tests...');

        // Récupérer quelques événements existants
        $evenements = $this->entityManager->getRepository(Evenement::class)->findAll();
        
        if (empty($evenements)) {
            $output->writeln('<error>Aucun événement trouvé. Créez d\'abord des événements.</error>');
            return Command::FAILURE;
        }

        // Créer des créneaux pour les prochains week-ends
        $creneaux = [];
        
        // Prochains vendredis, samedis et dimanches
        $dates = [
            // Vendredi prochain
            ['2025-10-11', 'vendredi'],
            // Samedi prochain
            ['2025-10-12', 'samedi'],
            // Dimanche prochain
            ['2025-10-13', 'dimanche'],
            // Weekend suivant
            ['2025-10-18', 'vendredi'],
            ['2025-10-19', 'samedi'],
            ['2025-10-20', 'dimanche'],
        ];

        $postes = ['Accueil', 'Caisse', 'Animation', 'Logistique', 'Buvette', 'Sécurité'];
        $compteur = 0;

        foreach ($dates as [$date, $jour]) {
            $evenement = $evenements[$compteur % count($evenements)];
            
            // Créneaux matin
            $creneauMatin = new Creneau();
            $creneauMatin->setEvenement($evenement);
            $creneauMatin->setTitre("$jour matin - " . $postes[$compteur % count($postes)]);
            $creneauMatin->setPosteRequis($postes[$compteur % count($postes)]);
            $creneauMatin->setDebut(new \DateTime("$date 09:00:00"));
            $creneauMatin->setFin(new \DateTime("$date 13:00:00"));
            $creneauMatin->setMaxPersonnes(rand(2, 5));
            $creneauMatin->setNotes("Créneau du $jour matin - Arriver 15 minutes avant l'ouverture");
            $creneauMatin->setDateCreation(new \DateTime());
            
            $this->entityManager->persist($creneauMatin);
            $compteur++;

            // Créneaux après-midi
            $creneauApresmidi = new Creneau();
            $creneauApresmidi->setEvenement($evenement);
            $creneauApresmidi->setTitre("$jour après-midi - " . $postes[$compteur % count($postes)]);
            $creneauApresmidi->setPosteRequis($postes[$compteur % count($postes)]);
            $creneauApresmidi->setDebut(new \DateTime("$date 14:00:00"));
            $creneauApresmidi->setFin(new \DateTime("$date 18:00:00"));
            $creneauApresmidi->setMaxPersonnes(rand(2, 5));
            $creneauApresmidi->setNotes("Créneau du $jour après-midi - Prévoir pause à 16h");
            $creneauApresmidi->setDateCreation(new \DateTime());
            
            $this->entityManager->persist($creneauApresmidi);
            $compteur++;

            // Pour les samedis, ajouter un créneau soirée
            if ($jour === 'samedi') {
                $creneauSoiree = new Creneau();
                $creneauSoiree->setEvenement($evenement);
                $creneauSoiree->setTitre("$jour soirée - Animation");
                $creneauSoiree->setPosteRequis('Animation');
                $creneauSoiree->setDebut(new \DateTime("$date 19:00:00"));
                $creneauSoiree->setFin(new \DateTime("$date 23:00:00"));
                $creneauSoiree->setMaxPersonnes(3);
                $creneauSoiree->setNotes("Soirée du $jour - Animation et ambiance");
                $creneauSoiree->setDateCreation(new \DateTime());
                
                $this->entityManager->persist($creneauSoiree);
                $compteur++;
            }
        }

        // Sauvegarder en base
        $this->entityManager->flush();
        
        $output->writeln("<info>✅ {$compteur} créneaux de week-end créés avec succès !</info>");
        
        return Command::SUCCESS;
    }
}