<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BenevoleController extends AbstractController
{
    #[Route('/benevoles', name: 'benevoles')]
    public function index(): Response
    {
        // Données temporaires pour l'affichage - 15 bénévoles (3 lignes de 5)
        $benevoles = [
            // Ligne 1
            [
                'nom' => 'Marie Dubois',
                'email' => 'marie.dubois@email.com',
                'telephone' => '06 12 34 56 78',
                'statut' => 'confirme',
                'competences' => ['Accueil', 'Logistique'],
                'disponibilites' => ['Samedi matin', 'Dimanche après-midi']
            ],
            [
                'nom' => 'Pierre Martin',
                'email' => 'pierre.martin@email.com',
                'telephone' => '06 98 76 54 32',
                'statut' => 'attente',
                'competences' => ['Technique', 'Montage'],
                'disponibilites' => ['Vendredi soir', 'Samedi toute la journée']
            ],
            [
                'nom' => 'Sophie Laurent',
                'email' => 'sophie.laurent@email.com',
                'telephone' => '06 11 22 33 44',
                'statut' => 'confirme',
                'competences' => ['Communication', 'Animation'],
                'disponibilites' => ['Samedi après-midi', 'Dimanche matin']
            ],
            [
                'nom' => 'Luc Bernard',
                'email' => 'luc.bernard@email.com',
                'telephone' => '07 55 66 77 88',
                'statut' => 'confirme',
                'competences' => ['Sécurité', 'Premiers secours'],
                'disponibilites' => ['Vendredi soir', 'Samedi soir']
            ],
            [
                'nom' => 'Emma Rousseau',
                'email' => 'emma.rousseau@email.com',
                'telephone' => '06 33 44 55 66',
                'statut' => 'attente',
                'competences' => ['Bar', 'Service'],
                'disponibilites' => ['Dimanche matin', 'Dimanche soir']
            ],
            // Ligne 2
            [
                'nom' => 'Thomas Girard',
                'email' => 'thomas.girard@email.com',
                'telephone' => '07 22 33 44 55',
                'statut' => 'confirme',
                'competences' => ['Sonorisation', 'Éclairage'],
                'disponibilites' => ['Samedi complet', 'Dimanche matin']
            ],
            [
                'nom' => 'Clara Mercier',
                'email' => 'clara.mercier@email.com',
                'telephone' => '06 77 88 99 00',
                'statut' => 'confirme',
                'competences' => ['Cuisine', 'Préparation'],
                'disponibilites' => ['Vendredi après-midi', 'Samedi matin']
            ],
            [
                'nom' => 'David Moreau',
                'email' => 'david.moreau@email.com',
                'telephone' => '07 66 77 88 99',
                'statut' => 'attente',
                'competences' => ['Transport', 'Manutention'],
                'disponibilites' => ['Samedi après-midi', 'Dimanche complet']
            ],
            [
                'nom' => 'Julie Blanchard',
                'email' => 'julie.blanchard@email.com',
                'telephone' => '06 88 99 11 22',
                'statut' => 'confirme',
                'competences' => ['Enfants', 'Activités'],
                'disponibilites' => ['Samedi matin', 'Dimanche après-midi']
            ],
            [
                'nom' => 'Nicolas Garnier',
                'email' => 'nicolas.garnier@email.com',
                'telephone' => '07 11 22 33 44',
                'statut' => 'confirme',
                'competences' => ['Photographie', 'Vidéo'],
                'disponibilites' => ['Week-end complet']
            ],
            // Ligne 3
            [
                'nom' => 'Isabelle Faure',
                'email' => 'isabelle.faure@email.com',
                'telephone' => '06 44 55 66 77',
                'statut' => 'attente',
                'competences' => ['Décoration', 'Créativité'],
                'disponibilites' => ['Vendredi soir', 'Samedi matin']
            ],
            [
                'nom' => 'Antoine Roux',
                'email' => 'antoine.roux@email.com',
                'telephone' => '07 00 11 22 33',
                'statut' => 'confirme',
                'competences' => ['Nettoyage', 'Rangement'],
                'disponibilites' => ['Dimanche soir', 'Lundi matin']
            ],
            [
                'nom' => 'Camille Lambert',
                'email' => 'camille.lambert@email.com',
                'telephone' => '06 99 00 11 22',
                'statut' => 'confirme',
                'competences' => ['Caisse', 'Comptabilité'],
                'disponibilites' => ['Samedi complet']
            ],
            [
                'nom' => 'Léa Fontaine',
                'email' => 'lea.fontaine@email.com',
                'telephone' => '07 33 44 55 66',
                'statut' => 'attente',
                'competences' => ['Réseaux sociaux', 'Communication'],
                'disponibilites' => ['Vendredi soir', 'Dimanche matin']
            ],
            [
                'nom' => 'Paul Dubois',
                'email' => 'paul.dubois@email.com',
                'telephone' => '06 22 33 44 55',
                'statut' => 'confirme',
                'competences' => ['Coordination', 'Management'],
                'disponibilites' => ['Week-end complet']
            ]
        ];

        return $this->render('benevoles.html.twig', [
            'benevoles' => $benevoles
        ]);
    }
}