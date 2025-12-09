<?php
namespace App\Controller;

use App\Entity\InscriptionMecene;
use App\Entity\Lot;
use App\Entity\Notification;
use App\Entity\Role;
use App\Entity\Stock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminInscriptionController extends AbstractController
{
    #[Route('/admin/inscriptions', name: 'admin_inscriptions')]
    public function index(EntityManagerInterface $em): Response
    {
        $inscriptions = $em->getRepository(InscriptionMecene::class)->findBy(
            [],
            ['date_inscription' => 'DESC']
        );

        // Statistiques
        $stats = [
            'total' => count($inscriptions),
            'en_attente' => count(array_filter($inscriptions, fn($i) => $i->getStatut() === 'en_attente')),
            'acceptees' => count(array_filter($inscriptions, fn($i) => $i->getStatut() === 'accepte')),
            'refusees' => count(array_filter($inscriptions, fn($i) => $i->getStatut() === 'refuse')),
        ];

        return $this->render('admin/inscriptions/index.html.twig', [
            'inscriptions' => $inscriptions,
            'stats' => $stats
        ]);
    }

    #[Route('/admin/inscriptions/{id}/accepter', name: 'admin_inscription_accepter', methods: ['POST'])]
    public function accepter(InscriptionMecene $inscription, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('accepter_inscription_' . $inscription->getId(), $request->request->get('_token'))) {
            $remarqueAcceptation = $request->request->get('remarque_acceptation');
            $statutPrecedent = $inscription->getStatut();
            $inscription->setStatut('accepte');
            
            // Enregistrer la remarque d'acceptation si fournie
            if ($remarqueAcceptation) {
                $inscription->setRemarqueAcceptation($remarqueAcceptation);
            }
            
            $messageSuccess = '';

            if ($inscription->getTypeDon() === 'lot') {
                $lot = new Lot();
                $lot->setTitre($inscription->getNomDon());
                $lot->setDescription($inscription->getDescriptionDon());
                $lot->setQuantite($inscription->getQuantite());
                $lot->setValeurEstimee($inscription->getValeurUnitaire() ?? 0.0);
                $lot->setMecene($inscription->getMecene());
                $lot->setWeekend($inscription->getEvenement()->getWeekend());
                $lot->setDateCreation(new \DateTime());
                
                $em->persist($lot);
                $messageSuccess = 'L\'inscription de ' . $inscription->getMecene()->getOrganisation() . ' a été acceptée et le lot "' . $lot->getTitre() . '" a été créé.';
            } else {
                // Créer automatiquement l'entrée de stock
                $stock = new Stock();
                $stock->setNom($inscription->getNomDon());
                $stock->setCategorie($inscription->getCategorie());
                $stock->setQuantite($inscription->getQuantite());
                $stock->setUnite('pièces');
                $stock->setValeurUnitaire($inscription->getValeurUnitaire() ?? 0.0);
                $stock->setSeuil(0); // Seuil par défaut
                $stock->setSource('don');
                
                // Ajouter une remarque avec la provenance
                $remarque = 'Don de ' . $inscription->getMecene()->getOrganisation();
                $remarque .= ' pour l\'événement ' . $inscription->getEvenement()->getNom();
                if ($inscription->getDescriptionDon()) {
                    $remarque .= ' - ' . $inscription->getDescriptionDon();
                }
                $stock->setRemarque($remarque);
                $stock->setDerniereModif(new \DateTime());
                
                $em->persist($stock);
                $messageSuccess = 'L\'inscription de ' . $inscription->getMecene()->getOrganisation() . ' a été acceptée et l\'article "' . $stock->getNom() . '" a été ajouté au stock.';
            }
            
            // Créer une notification pour le mécène
            $notificationMecene = new Notification();
            $notificationMecene->setDestinataire($inscription->getMecene()->getUtilisateur());
            $notificationMecene->setType('inscription_acceptee');
            
            // Message personnalisé selon le statut précédent
            if ($statutPrecedent === 'refuse') {
                $messageNotif = 'Mise à jour : Votre inscription pour l\'événement "' . $inscription->getEvenement()->getNom() . '" a été acceptée.';
            } else {
                $messageNotif = 'Votre inscription pour l\'événement "' . $inscription->getEvenement()->getNom() . '" a été acceptée.';
            }
            
            // Ajouter la remarque d'acceptation au message si fournie
            if ($remarqueAcceptation) {
                $messageNotif .= ' Message : ' . $remarqueAcceptation;
            }
            
            $notificationMecene->setMessage($messageNotif);
            $notificationMecene->setLien($this->generateUrl('mecene_mes_inscriptions'));
            $em->persist($notificationMecene);
            
            $em->flush();

            if ($statutPrecedent === 'refuse') {
                $this->addFlash('success', 'L\'inscription de ' . $inscription->getMecene()->getOrganisation() . ' a été reconsidérée et acceptée.');
            } else {
                $this->addFlash('success', $messageSuccess);
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('admin_inscriptions');
    }

    #[Route('/admin/inscriptions/{id}/refuser', name: 'admin_inscription_refuser', methods: ['POST'])]
    public function refuser(InscriptionMecene $inscription, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('refuser_inscription_' . $inscription->getId(), $request->request->get('_token'))) {
            $remarqueRefus = $request->request->get('remarque_refus');
            $statutPrecedent = $inscription->getStatut();
            
            // Si l'inscription était acceptée, supprimer le stock associé
            if ($statutPrecedent === 'accepte' && $inscription->getNomDon()) {
                if ($inscription->getTypeDon() === 'lot') {
                    $lotRepository = $em->getRepository(Lot::class);
                    $lots = $lotRepository->findBy([
                        'titre' => $inscription->getNomDon(),
                        'mecene' => $inscription->getMecene(),
                        'weekend' => $inscription->getEvenement()->getWeekend()
                    ]);
                    foreach ($lots as $lot) {
                        $em->remove($lot);
                    }
                } else {
                    $stockRepository = $em->getRepository(Stock::class);
                    $stocks = $stockRepository->createQueryBuilder('s')
                        ->where('s.nom = :nom')
                        ->andWhere('s.remarque LIKE :remarque')
                        ->setParameter('nom', $inscription->getNomDon())
                        ->setParameter('remarque', '%' . $inscription->getMecene()->getOrganisation() . '%' . $inscription->getEvenement()->getNom() . '%')
                        ->getQuery()
                        ->getResult();
                    
                    foreach ($stocks as $stock) {
                        $em->remove($stock);
                    }
                }
            }
            
            $inscription->setStatut('refuse');
            if ($remarqueRefus) {
                $inscription->setRemarqueRefus($remarqueRefus);
            }
            
            // Créer une notification pour le mécène
            $notificationMecene = new Notification();
            $notificationMecene->setDestinataire($inscription->getMecene()->getUtilisateur());
            $notificationMecene->setType('inscription_refusee');
            
            // Message personnalisé selon le statut précédent
            if ($statutPrecedent === 'accepte') {
                $messageNotif = 'Mise à jour concernant votre inscription pour l\'événement "' . $inscription->getEvenement()->getNom() . '".';
            } else {
                $messageNotif = 'Votre inscription pour l\'événement "' . $inscription->getEvenement()->getNom() . '" a été refusée.';
            }
            
            if ($remarqueRefus) {
                $messageNotif .= ' Raison : ' . $remarqueRefus;
            }
            $notificationMecene->setMessage($messageNotif);
            $notificationMecene->setLien($this->generateUrl('mecene_mes_inscriptions'));
            $em->persist($notificationMecene);
            
            $em->flush();

            if ($statutPrecedent === 'accepte') {
                $this->addFlash('success', 'L\'inscription de ' . $inscription->getMecene()->getOrganisation() . ' a été refusée et l\'article/lot a été retiré.');
            } else {
                $this->addFlash('success', 'L\'inscription de ' . $inscription->getMecene()->getOrganisation() . ' a été refusée.');
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('admin_inscriptions');
    }

    #[Route('/admin/inscriptions/{id}/supprimer', name: 'admin_inscription_supprimer', methods: ['POST'])]
    public function supprimer(InscriptionMecene $inscription, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_inscription_' . $inscription->getId(), $request->request->get('_token'))) {
            $stockSupprime = false;
            
            // Si l'inscription était acceptée, on refuse avec remarque et on supprime le stock
            if ($inscription->getStatut() === 'accepte') {
                // Rechercher et supprimer le stock créé pour cette inscription
                if ($inscription->getNomDon()) {
                    if ($inscription->getTypeDon() === 'lot') {
                        $lotRepository = $em->getRepository(Lot::class);
                        $lots = $lotRepository->findBy([
                            'titre' => $inscription->getNomDon(),
                            'mecene' => $inscription->getMecene(),
                            'weekend' => $inscription->getEvenement()->getWeekend()
                        ]);
                        foreach ($lots as $lot) {
                            $em->remove($lot);
                            $stockSupprime = true;
                        }
                    } else {
                        $stockRepository = $em->getRepository(Stock::class);
                        $stocks = $stockRepository->createQueryBuilder('s')
                            ->where('s.nom = :nom')
                            ->andWhere('s.remarque LIKE :remarque')
                            ->setParameter('nom', $inscription->getNomDon())
                            ->setParameter('remarque', '%' . $inscription->getMecene()->getOrganisation() . '%' . $inscription->getEvenement()->getNom() . '%')
                            ->getQuery()
                            ->getResult();
                        
                        // Supprimer le(s) stock(s) trouvé(s)
                        foreach ($stocks as $stock) {
                            $em->remove($stock);
                            $stockSupprime = true;
                        }
                    }
                }
                
                // Passer l'inscription en refusée au lieu de la supprimer
                $inscription->setStatut('refuse');
                $inscription->setRemarqueRefus('Demande annulée par l\'administrateur après acceptation. L\'article/lot a été retiré.');
                $em->flush();

                $this->addFlash('success', 'L\'inscription a été annulée et passée en statut "Refusée". L\'article/lot a été retiré.');
            } else {
                // Pour les inscriptions en attente ou déjà refusées, on peut les supprimer définitivement
                $em->remove($inscription);
                $em->flush();

                $this->addFlash('success', 'L\'inscription a été supprimée définitivement.');
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('admin_inscriptions');
    }
}
