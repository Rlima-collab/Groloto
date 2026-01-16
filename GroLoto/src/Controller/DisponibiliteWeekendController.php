<?php

namespace App\Controller;

use App\Entity\DisponibiliteWeekend;
use App\Repository\BenevoleRepository;
use App\Repository\DisponibiliteWeekendRepository;
use App\Repository\WeekendRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/benevole/disponibilites')]
class DisponibiliteWeekendController extends AbstractController
{
    #[Route('/', name: 'benevole_disponibilites_index')]
    #[IsGranted('ROLE_BENEVOLE')]
    public function index(
        Request $request,
        WeekendRepository $weekendRepo,
        BenevoleRepository $benevoleRepo,
        DisponibiliteWeekendRepository $dispoRepo
    ): Response {
        $user = $this->getUser();
        $benevole = $benevoleRepo->findOneBy(['utilisateur' => $user]);

        if (!$benevole) {
            $this->addFlash('error', 'Vous devez être bénévole pour accéder à cette page.');
            return $this->redirectToRoute('app_home');
        }

        $now = new \DateTime();
        $now->setTime(0, 0, 0);
        $filter = $request->query->get('filter', 'a_venir');

        // Récupérer les weekends selon le filtre
        $qb = $weekendRepo->createQueryBuilder('w');
        
        if ($filter === 'a_venir') {
            $qb->where('w.date_fin >= :now')
               ->setParameter('now', $now)
               ->orderBy('w.date_debut', 'ASC');
        } elseif ($filter === 'passes') {
            $qb->where('w.date_fin < :now')
               ->setParameter('now', $now)
               ->orderBy('w.date_debut', 'DESC');
        } else {
            // tous
            $qb->orderBy('w.date_debut', 'DESC');
        }
        
        $weekends = $qb->getQuery()->getResult();

        // Pour chaque weekend, vérifier si le bénévole a déjà renseigné ses disponibilités
        $weekendsData = [];
        foreach ($weekends as $weekend) {
            $hasDispos = $dispoRepo->hasDisponibilites($benevole, $weekend);
            $isPast = $weekend->getDateFin() < $now;
            $weekendsData[] = [
                'weekend' => $weekend,
                'hasDisponibilites' => $hasDispos,
                'isPast' => $isPast,
            ];
        }

        return $this->render('benevole/disponibilites/index.html.twig', [
            'weekendsData' => $weekendsData,
            'currentFilter' => $filter,
        ]);
    }

    #[Route('/weekend/{id}', name: 'benevole_disponibilites_weekend')]
    #[IsGranted('ROLE_BENEVOLE')]
    public function editDisponibilites(
        int $id,
        Request $request,
        WeekendRepository $weekendRepo,
        BenevoleRepository $benevoleRepo,
        DisponibiliteWeekendRepository $dispoRepo,
        EntityManagerInterface $em
    ): Response {
        $weekend = $weekendRepo->find($id);
        if (!$weekend) {
            throw $this->createNotFoundException('Weekend non trouvé');
        }

        $user = $this->getUser();
        $benevole = $benevoleRepo->findOneBy(['utilisateur' => $user]);

        if (!$benevole) {
            $this->addFlash('error', 'Vous devez être bénévole pour accéder à cette page.');
            return $this->redirectToRoute('app_home');
        }

        // Générer tous les jours dans la plage autorisée pour les tâches/disponibilités
        // Utiliser les offsets configurés sur le weekend (admin configurable)
        $before = $weekend->getTaskOffsetBefore() ?? 2; // jours avant
        $after = $weekend->getTaskOffsetAfter() ?? 3;   // jours après

        $dateDebut = clone $weekend->getDateDebut();
        $dateFin = clone $weekend->getDateFin();

        $minDate = (clone $dateDebut)->modify("-{$before} days");
        $maxDate = (clone $dateFin)->modify("+{$after} days");

        $jours = [];
        $current = clone $minDate;
        while ($current <= $maxDate) {
            $jours[] = clone $current;
            $current->modify('+1 day');
        }

        // Récupérer les disponibilités existantes
        $disposExistantes = $dispoRepo->findByBenevoleAndWeekend($benevole, $weekend);
        $disposParJour = [];
        foreach ($disposExistantes as $dispo) {
            $jourKey = $dispo->getJour()->format('Y-m-d');
            $disposParJour[$jourKey] = $dispo;
        }

        if ($request->isMethod('POST')) {
            $dispoData = $request->request->all('disponibilites') ?? [];
            $remarque = $request->request->get('remarque', '');

            // Supprimer les anciennes disponibilités
            $dispoRepo->deleteByBenevoleAndWeekend($benevole, $weekend);

            // Créer les nouvelles disponibilités
            foreach ($jours as $jour) {
                $jourKey = $jour->format('Y-m-d');
                $jourDispos = $dispoData[$jourKey] ?? [];

                $dispo = new DisponibiliteWeekend();
                $dispo->setBenevole($benevole);
                $dispo->setWeekend($weekend);
                $dispo->setJour($jour);
                $dispo->setMatin(in_array('matin', $jourDispos));
                $dispo->setApresMidi(in_array('apres_midi', $jourDispos));
                $dispo->setSoir(in_array('soir', $jourDispos));
                $dispo->setRemarque($remarque);

                $em->persist($dispo);
            }

            $em->flush();

            $this->addFlash('success', 'Vos disponibilités ont été enregistrées avec succès !');
            return $this->redirectToRoute('benevole_disponibilites_index');
        }

        return $this->render('benevole/disponibilites/edit.html.twig', [
            'weekend' => $weekend,
            'jours' => $jours,
            'disposParJour' => $disposParJour,
            'minDate' => $minDate,
            'maxDate' => $maxDate,
        ]);
    }

    /**
     * Vue admin : voir les disponibilités de tous les bénévoles pour un weekend
     */
    #[Route('/admin/weekend/{id}', name: 'admin_disponibilites_weekend')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminViewDisponibilites(
        int $id,
        WeekendRepository $weekendRepo,
        DisponibiliteWeekendRepository $dispoRepo,
        BenevoleRepository $benevoleRepo
    ): Response {
        $weekend = $weekendRepo->find($id);
        if (!$weekend) {
            throw $this->createNotFoundException('Weekend non trouvé');
        }

        // Générer les jours du weekend
        $dateDebut = clone $weekend->getDateDebut();
        $dateFin = clone $weekend->getDateFin();
        $jours = [];
        $current = clone $dateDebut;
        while ($current <= $dateFin) {
            $jours[] = clone $current;
            $current->modify('+1 day');
        }

        // Récupérer toutes les disponibilités pour ce weekend
        $disponibilites = $dispoRepo->findByWeekend($weekend);

        // Organiser par bénévole puis par jour
        $disposParBenevole = [];
        foreach ($disponibilites as $dispo) {
            $benevoleId = $dispo->getBenevole()->getId();
            if (!isset($disposParBenevole[$benevoleId])) {
                $disposParBenevole[$benevoleId] = [
                    'benevole' => $dispo->getBenevole(),
                    'jours' => [],
                ];
            }
            $jourKey = $dispo->getJour()->format('Y-m-d');
            $disposParBenevole[$benevoleId]['jours'][$jourKey] = $dispo;
        }

        // Compter les bénévoles n'ayant pas encore répondu
        $totalBenevoles = count($benevoleRepo->findBy(['actif' => true]));
        $benevolesAvecDispos = $dispoRepo->countBenevolesWithDisponibilites($weekend);
        $benevolesSansDispos = $totalBenevoles - $benevolesAvecDispos;

        return $this->render('benevole/disponibilites/admin_view.html.twig', [
            'weekend' => $weekend,
            'jours' => $jours,
            'disposParBenevole' => $disposParBenevole,
            'totalBenevoles' => $totalBenevoles,
            'benevolesAvecDispos' => $benevolesAvecDispos,
            'benevolesSansDispos' => $benevolesSansDispos,
        ]);
    }
}
