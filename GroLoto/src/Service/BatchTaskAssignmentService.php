<?php

namespace App\Service;

use App\Entity\Benevole;
use App\Entity\Tache;
use App\Entity\AffectationTache;
use App\Repository\AffectationTacheRepository;
use Doctrine\ORM\EntityManagerInterface;

class BatchTaskAssignmentService
{
    public function __construct(
        private EntityManagerInterface $em,
        private AffectationTacheRepository $affectationRepo,
        private NotificationService $notificationService
    ) {}

    /**
     * Détecte les conflits horaires pour un bénévole
     * 
     * @param Benevole $benevole
     * @param Tache $nouvelleTache
     * @return bool true si conflit détecté
     */
    public function hasTimeConflict(Benevole $benevole, Tache $nouvelleTache): bool
    {
        // Récupérer toutes les tâches déjà assignées au bénévole
        $affectations = $this->affectationRepo->findBy([
            'benevole' => $benevole,
            'statut' => 'assigne'
        ]);

        $nouvelleDebut = $nouvelleTache->getDebut();
        $nouvelleFin = $nouvelleTache->getFin();

        if (!$nouvelleDebut || !$nouvelleFin) {
            return false;
        }

        foreach ($affectations as $affectation) {
            $tache = $affectation->getTache();
            $debut = $tache->getDebut();
            $fin = $tache->getFin();

            if (!$debut || !$fin) {
                continue;
            }

            // Vérifier chevauchement : (debut1 < fin2) && (debut2 < fin1)
            if ($nouvelleDebut < $fin && $debut < $nouvelleFin) {
                return true; // Conflit détecté
            }
        }

        return false;
    }

    /**
     * Vérifie si deux tâches se chevauchent dans le temps
     * 
     * @param Tache $tache1
     * @param Tache $tache2
     * @return bool true si les tâches se chevauchent
     */
    private function tachesSeChevauche(Tache $tache1, Tache $tache2): bool
    {
        $debut1 = $tache1->getDebut();
        $fin1 = $tache1->getFin();
        $debut2 = $tache2->getDebut();
        $fin2 = $tache2->getFin();

        if (!$debut1 || !$fin1 || !$debut2 || !$fin2) {
            return false;
        }

        // Vérifier chevauchement : (debut1 < fin2) && (debut2 < fin1)
        return ($debut1 < $fin2 && $debut2 < $fin1);
    }

    /**
     * Obtient toutes les tâches disponibles en marquant celles en conflit
     * 
     * @param Benevole $benevole
     * @param array $taches Liste des tâches à vérifier
     * @return array ['tache' => Tache, 'hasConflict' => bool]
     */
    public function getAvailableTasksWithConflicts(Benevole $benevole, array $taches): array
    {
        $result = [];
        
        foreach ($taches as $tache) {
            $result[] = [
                'tache' => $tache,
                'hasConflict' => $this->hasTimeConflict($benevole, $tache)
            ];
        }

        return $result;
    }

    /**
     * Assigne ou propose plusieurs tâches à un bénévole en une seule fois
     * 
     * @param Benevole $benevole
     * @param array $tasksData Format: [['tache_id' => 123, 'action' => 'assigner'|'proposer'], ...]
     * @param Utilisateur $admin L'administrateur qui fait l'assignation
     * @return array ['success' => int, 'conflicts' => int, 'errors' => array]
     */
    public function batchAssignTasks(Benevole $benevole, array $tasksData, $admin): array
    {
        $stats = [
            'success' => 0,
            'conflicts' => 0,
            'errors' => []
        ];

        $assignedTasks = [];
        $proposedTasks = [];
        $tasksEnCoursAssignation = []; // Tâches en cours d'assignation dans ce batch

        foreach ($tasksData as $taskData) {
            try {
                $tache = $this->em->getRepository(Tache::class)->find($taskData['tache_id']);
                
                if (!$tache) {
                    $stats['errors'][] = "Tâche #{$taskData['tache_id']} introuvable";
                    continue;
                }

                $action = $taskData['action']; // 'assigner' ou 'proposer'

                // Pour assignation, vérifier conflit avec les tâches déjà assignées
                if ($action === 'assigner' && $this->hasTimeConflict($benevole, $tache)) {
                    $stats['conflicts']++;
                    $stats['errors'][] = "Conflit horaire pour : {$tache->getTitre()}";
                    continue;
                }

                // Vérifier conflit avec les autres tâches du batch en cours d'assignation
                if ($action === 'assigner') {
                    $conflitDansBatch = false;
                    foreach ($tasksEnCoursAssignation as $tacheEnCours) {
                        if ($this->tachesSeChevauche($tache, $tacheEnCours)) {
                            $stats['conflicts']++;
                            $stats['errors'][] = "Conflit horaire entre '{$tache->getTitre()}' et '{$tacheEnCours->getTitre()}'";
                            $conflitDansBatch = true;
                            break;
                        }
                    }
                    if ($conflitDansBatch) {
                        continue;
                    }
                }

                // Vérifier si déjà affecté
                $existingAffectation = $this->affectationRepo->findOneBy([
                    'benevole' => $benevole,
                    'tache' => $tache
                ]);

                if ($existingAffectation) {
                    // Mettre à jour le statut si différent
                    $newStatut = $action === 'assigner' ? 'assigne' : 'proposee';
                    if ($existingAffectation->getStatut() !== $newStatut) {
                        $existingAffectation->setStatut($newStatut);
                        $existingAffectation->setDateAffectation(new \DateTime());
                    }
                } else {
                    // Créer nouvelle affectation
                    $affectation = new AffectationTache();
                    $affectation->setTache($tache);
                    $affectation->setBenevole($benevole);
                    $affectation->setUtilisateur($admin);
                    $affectation->setStatut($action === 'assigner' ? 'assigne' : 'proposee');
                    $affectation->setDateAffectation(new \DateTime());
                    
                    $this->em->persist($affectation);
                }

                $stats['success']++;

                // Ajouter aux tâches en cours d'assignation dans ce batch
                if ($action === 'assigner') {
                    $tasksEnCoursAssignation[] = $tache;
                }

                // Collecter pour notification groupée
                if ($action === 'assigner') {
                    $assignedTasks[] = $tache->getTitre();
                } else {
                    $proposedTasks[] = $tache->getTitre();
                }

            } catch (\Exception $e) {
                $stats['errors'][] = $e->getMessage();
            }
        }

        // Sauvegarder en base
        if ($stats['success'] > 0) {
            $this->em->flush();

            // Envoyer UNE SEULE notification groupée
            $this->sendBatchNotification($benevole, $assignedTasks, $proposedTasks);
        }

        return $stats;
    }

    /**
     * Envoie une notification groupée au bénévole
     */
    private function sendBatchNotification(Benevole $benevole, array $assignedTasks, array $proposedTasks): void
    {
        $user = $benevole->getUtilisateur();
        if (!$user) {
            return;
        }

        $message = '';
        
        if (count($assignedTasks) > 0) {
            $message .= sprintf(
                "✅ %d tâche(s) vous ont été assignée(s) :\n- %s\n\n",
                count($assignedTasks),
                implode("\n- ", $assignedTasks)
            );
        }

        if (count($proposedTasks) > 0) {
            $message .= sprintf(
                "💡 %d tâche(s) vous ont été proposée(s) :\n- %s",
                count($proposedTasks),
                implode("\n- ", $proposedTasks)
            );
        }

        if ($message) {
            $this->notificationService->createNotification(
                $user,
                'Nouvelles tâches',
                trim($message),
                '/benevole/mon-planning'
            );
        }
    }

    /**
     * Supprime plusieurs affectations d'un bénévole
     */
    public function batchRemoveTasks(Benevole $benevole, array $tacheIds): int
    {
        $removed = 0;

        foreach ($tacheIds as $tacheId) {
            $tache = $this->em->getRepository(Tache::class)->find($tacheId);
            if (!$tache) continue;

            $affectation = $this->affectationRepo->findOneBy([
                'benevole' => $benevole,
                'tache' => $tache
            ]);

            if ($affectation) {
                $this->em->remove($affectation);
                $removed++;
            }
        }

        if ($removed > 0) {
            $this->em->flush();
            
            // Notification de suppression
            $user = $benevole->getUtilisateur();
            if ($user) {
                $this->notificationService->createNotification(
                    $user,
                    'Tâches retirées',
                    sprintf('%d tâche(s) vous ont été retirée(s).', $removed),
                    'warning'
                );
            }
        }

        return $removed;
    }
}
