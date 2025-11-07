<?php
namespace App\Controller;

use App\Entity\InscriptionMecene;
use App\Entity\Mecene;
use App\Entity\Notification;
use App\Entity\Role;
use App\Entity\Utilisateur;
use App\Form\InscriptionMeceneType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MECENE')]
class InscriptionMeceneController extends AbstractController
{
    #[Route('/mecene/inscription', name: 'mecene_inscription')]
    public function inscription(Request $request, EntityManagerInterface $em): Response
    {
        // Récupérer le mécène connecté
        $utilisateur = $this->getUser();
        $mecene = $em->getRepository(Mecene::class)->findOneBy(['utilisateur' => $utilisateur]);
        
        if (!$mecene) {
            $this->addFlash('error', 'Aucun profil mécène trouvé pour cet utilisateur.');
            return $this->redirectToRoute('app_evenements');
        }

        $inscription = new InscriptionMecene();
        $inscription->setMecene($mecene);
        $inscription->setDateInscription(new \DateTime());

        $form = $this->createForm(InscriptionMeceneType::class, $inscription);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em->persist($inscription);
                
                // Créer une notification pour tous les administrateurs
                $roleAdmin = $em->getRepository(Role::class)->findOneBy(['nom' => 'admin']);
                if ($roleAdmin) {
                    $admins = $em->getRepository(Utilisateur::class)->findBy(['role' => $roleAdmin]);
                    
                    foreach ($admins as $admin) {
                        $notificationAdmin = new Notification();
                        $notificationAdmin->setDestinataire($admin);
                        $notificationAdmin->setType('nouvelle_inscription');
                        $notificationAdmin->setMessage('Nouvelle inscription de ' . $mecene->getOrganisation() . ' pour l\'événement "' . $inscription->getEvenement()->getNom() . '"');
                        $notificationAdmin->setLien($this->generateUrl('admin_inscriptions'));
                        $em->persist($notificationAdmin);
                    }
                }
                
                $em->flush();

                $this->addFlash('success', 'Votre demande d\'inscription a été envoyée avec succès ! Un administrateur va la traiter prochainement.');
                return $this->redirectToRoute('mecene_mes_inscriptions');
            } else {
                $this->addFlash('error', 'Le formulaire contient des erreurs. Veuillez vérifier les champs.');
            }
        }

        return $this->render('inscription_mecene/form.html.twig', [
            'form' => $form->createView(),
            'mecene' => $mecene
        ]);
    }

    #[Route('/mecene/mes-inscriptions', name: 'mecene_mes_inscriptions')]
    public function mesInscriptions(EntityManagerInterface $em): Response
    {
        $utilisateur = $this->getUser();
        $mecene = $em->getRepository(Mecene::class)->findOneBy(['utilisateur' => $utilisateur]);
        
        if (!$mecene) {
            $this->addFlash('error', 'Aucun profil mécène trouvé pour cet utilisateur.');
            return $this->redirectToRoute('app_evenements');
        }

        $inscriptions = $em->getRepository(InscriptionMecene::class)->findBy(
            ['mecene' => $mecene],
            ['date_inscription' => 'DESC']
        );

        return $this->render('inscription_mecene/mes_inscriptions.html.twig', [
            'inscriptions' => $inscriptions,
            'mecene' => $mecene
        ]);
    }
}
