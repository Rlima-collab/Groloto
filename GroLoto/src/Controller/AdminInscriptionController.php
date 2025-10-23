<?php
namespace App\Controller;

use App\Entity\InscriptionMecene;
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
            $inscription->setStatut('accepte');
            
            // Créer automatiquement l'entrée de stock
            $stock = new Stock();
            $stock->setNom($inscription->getNomDon());
            $stock->setCategorie($inscription->getCategorie());
            $stock->setQuantite($inscription->getQuantite());
            $stock->setUnite('pièces');
            $stock->setValeurUnitaire($inscription->getValeurUnitaire() ?? 0.0);
            $stock->setSeuil(0); // Seuil par défaut
            
            // Ajouter une remarque avec la provenance
            $remarque = 'Don de ' . $inscription->getMecene()->getOrganisation();
            $remarque .= ' pour l\'événement ' . $inscription->getEvenement()->getNom();
            if ($inscription->getDescriptionDon()) {
                $remarque .= ' - ' . $inscription->getDescriptionDon();
            }
            $stock->setRemarque($remarque);
            $stock->setDerniereModif(new \DateTime());
            
            $em->persist($stock);
            $em->flush();

            $this->addFlash('success', 'L\'inscription de ' . $inscription->getMecene()->getOrganisation() . ' a été acceptée et l\'article "' . $stock->getNom() . '" a été ajouté au stock.');
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
            
            $inscription->setStatut('refuse');
            if ($remarqueRefus) {
                $inscription->setRemarqueRefus($remarqueRefus);
            }
            $em->flush();

            $this->addFlash('success', 'L\'inscription de ' . $inscription->getMecene()->getOrganisation() . ' a été refusée.');
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
                
                // Passer l'inscription en refusée au lieu de la supprimer
                $inscription->setStatut('refuse');
                $inscription->setRemarqueRefus('Demande annulée par l\'administrateur après acceptation. L\'article a été retiré du stock.');
                $em->flush();

                $this->addFlash('success', 'L\'inscription a été annulée et passée en statut "Refusée". L\'article a été retiré du stock.');
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
