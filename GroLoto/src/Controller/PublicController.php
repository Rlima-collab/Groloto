<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PublicController extends AbstractController
{
    #[Route('/public', name: 'app_public')]
    public function index(
        UtilisateurRepository $utilisateurRepository,
        RoleRepository $roleRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Métriques de base
        $totalInscrits = $utilisateurRepository->count([]);
        $nouveauxUtilisateurs = $this->getNouveauxUtilisateurs($utilisateurRepository);
        $utilisateursFideles = $this->getUtilisateursFideles($utilisateurRepository);
        
        // Calcul des revenus simulés (en attendant d'avoir une entité Event/Payment)
        $revenus = $this->calculerRevenus($totalInscrits);
        
        // Liste complète des participants
        $participants = $utilisateurRepository->findAll();
        
        // Données pour les analytics
        $analyticsData = $this->getAnalyticsData($utilisateurRepository, $roleRepository);
        
        return $this->render('public/index.html.twig', [
            'total_inscrits' => $totalInscrits,
            'revenus' => $revenus,
            'nouveaux' => $nouveauxUtilisateurs,
            'fideles' => $utilisateursFideles,
            'participants' => $participants,
            'analytics' => $analyticsData,
        ]);
    }
    
    private function getNouveauxUtilisateurs(UtilisateurRepository $repository): int
    {
        // Utilisateurs créés dans les 30 derniers jours
        $dateLimit = new \DateTime('-30 days');
        
        return $repository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.dateCreation >= :dateLimit')
            ->setParameter('dateLimit', $dateLimit)
            ->getQuery()
            ->getSingleScalarResult();
    }
    
    private function getUtilisateursFideles(UtilisateurRepository $repository): int
    {
        // Utilisateurs inscrits depuis plus de 6 mois
        $dateLimit = new \DateTime('-6 months');
        
        return $repository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.dateCreation <= :dateLimit')
            ->setParameter('dateLimit', $dateLimit)
            ->getQuery()
            ->getSingleScalarResult();
    }
    
    private function calculerRevenus(int $totalInscrits): int
    {
        // Simulation : 25€ par participant en moyenne
        return $totalInscrits * 25;
    }
    
    private function getAnalyticsData(UtilisateurRepository $utilisateurRepository, RoleRepository $roleRepository): array
    {
        // Répartition par rôle
        $roles = $roleRepository->findAll();
        $repartitionRoles = [];
        
        foreach ($roles as $role) {
            $count = $utilisateurRepository->count(['role' => $role]);
            $repartitionRoles[] = [
                'nom' => $role->getNom(),
                'count' => $count,
                'couleur' => $this->getCouleurRole($role->getNom())
            ];
        }
        
        // Évolution des inscriptions par mois (simulation)
        $evolutionInscriptions = $this->getEvolutionInscriptions($utilisateurRepository);
        
        return [
            'repartition_roles' => $repartitionRoles,
            'evolution_inscriptions' => $evolutionInscriptions,
        ];
    }
    
    private function getCouleurRole(string $roleNom): string
    {
        return match($roleNom) {
            'admin' => '#dc3545',      // Rouge
            'benevole' => '#0d6efd',   // Bleu
            'mecene' => '#198754',     // Vert
            default => '#6c757d'       // Gris
        };
    }
    
    private function getEvolutionInscriptions(UtilisateurRepository $repository): array
    {
        // Derniers 6 mois
        $mois = [];
        $donnees = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = new \DateTime("-$i months");
            $moisNom = $date->format('M');
            $mois[] = $moisNom;
            
            // Compter les inscriptions de ce mois
            $debutMois = clone $date;
            $debutMois->modify('first day of this month')->setTime(0, 0, 0);
            $finMois = clone $date;
            $finMois->modify('last day of this month')->setTime(23, 59, 59);
            
            $count = $repository->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->where('u.dateCreation >= :debut')
                ->andWhere('u.dateCreation <= :fin')
                ->setParameter('debut', $debutMois)
                ->setParameter('fin', $finMois)
                ->getQuery()
                ->getSingleScalarResult();
                
            $donnees[] = $count;
        }
        
        return [
            'mois' => $mois,
            'donnees' => $donnees
        ];
    }
}