<?php

namespace App\Controller\Admin;

use App\Entity\Membre;
use App\Repository\MembreRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use DateTime;

#[Route('/admin/membres')]
#[IsGranted('ROLE_ADMIN')]
class MembreController extends AbstractController
{
    public function __construct(
        private MembreRepository $membreRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'admin_membres_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        if ($request->isMethod('POST') && $request->files->has('excel_file')) {
            return $this->handleFileUpload($request);
        }

        $membres = $this->membreRepository->findAll();
        $totalMembres = count($membres);

        return $this->render('admin/membres/index.html.twig', [
            'membres' => $membres,
            'total_membres' => $totalMembres,
            'page_title' => 'Gestion des Membres',
        ]);
    }

    #[Route('/export', name: 'admin_membres_export', methods: ['GET'])]
    public function export(): StreamedResponse
    {
        $membres = $this->membreRepository->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = ['Nom', 'Prénom', 'Email', 'Numéro Billet', 'Tarif', 'Date Création', 'Date Séance', 'Montant Tarif', 'Code Promo', 'Montant Code Promo'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        
        foreach ($headers as $index => $header) {
            $sheet->getCell($cols[$index] . '1')->setValue($header);
        }

        // Data
        $row = 2;
        foreach ($membres as $membre) {
            $sheet->getCell('A' . $row)->setValue($membre->getNom());
            $sheet->getCell('B' . $row)->setValue($membre->getPrenom());
            $sheet->getCell('C' . $row)->setValue($membre->getEmail());
            $sheet->getCell('D' . $row)->setValue($membre->getNumeroBillet());
            $sheet->getCell('E' . $row)->setValue($membre->getTarif());
            $sheet->getCell('F' . $row)->setValue($membre->getDateCreation()?->format('d/m/Y'));
            $sheet->getCell('G' . $row)->setValue($membre->getDateSeance()?->format('d/m/Y'));
            $sheet->getCell('H' . $row)->setValue($membre->getMontantTarif());
            $sheet->getCell('I' . $row)->setValue($membre->getCodePromo());
            $sheet->getCell('J' . $row)->setValue($membre->getMontantCodePromo());
            $row++;
        }

        // Auto-size columns
        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'membres_' . date('Y-m-d_H-i-s') . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filename . '"');

        return $response;
    }

    #[Route('/import-preview', name: 'admin_membres_import_preview', methods: ['POST'])]
    public function importPreview(Request $request): Response
    {
        if (!$request->files->has('excel_file')) {
            $this->addFlash('error', 'Veuillez sélectionner un fichier Excel');
            return $this->redirectToRoute('admin_membres_index');
        }

        $file = $request->files->get('excel_file');

        try {
            $reader = new XlsxReader();
            $spreadsheet = $reader->load($file->getPathname());
            $sheet = $spreadsheet->getActiveSheet();

            $previewData = [];
            $errors = [];

            foreach ($sheet->getRowIterator(2) as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                $rowData = [];
                $colIndex = 1;

                foreach ($cellIterator as $cell) {
                    $value = $cell->getValue();
                    
                    switch ($colIndex) {
                        case 1: $rowData['nom'] = $value; break;
                        case 2: $rowData['prenom'] = $value; break;
                        case 3: $rowData['email'] = $value; break;
                        case 4: $rowData['numero_billet'] = $value; break;
                        case 5: $rowData['tarif'] = $value; break;
                        case 6: $rowData['date_creation'] = $this->parseDate($value); break;
                        case 7: $rowData['date_seance'] = $this->parseDate($value); break;
                        case 8: $rowData['montant_tarif'] = is_numeric($value) ? (int)$value : null; break;
                        case 9: $rowData['code_promo'] = $value; break;
                        case 10: $rowData['montant_code_promo'] = $value; break;
                    }
                    $colIndex++;
                }

                if (!empty($rowData['nom']) && !empty($rowData['email'])) {
                    $previewData[] = $rowData;
                }
            }

            return $this->render('admin/membres/import_preview.html.twig', [
                'preview_data' => $previewData,
                'total_rows' => count($previewData),
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la lecture du fichier: ' . $e->getMessage());
            return $this->redirectToRoute('admin_membres_index');
        }
    }

    #[Route('/import-confirm', name: 'admin_membres_import_confirm', methods: ['POST'])]
    public function importConfirm(Request $request): Response
    {
        if (!$request->request->has('members_json')) {
            $this->addFlash('error', 'Données manquantes');
            return $this->redirectToRoute('admin_membres_index');
        }

        $membersData = json_decode($request->request->get('members_json'), true);
        $importedCount = 0;
        $updatedCount = 0;

        foreach ($membersData as $data) {
            $membre = $this->membreRepository->findByEmail($data['email']);

            if (!$membre) {
                $membre = new Membre();
                $membre->setEmail($data['email']);
                $importedCount++;
            } else {
                $updatedCount++;
            }

            $membre->setNom($data['nom']);
            $membre->setPrenom($data['prenom']);
            $membre->setNumeroBillet($data['numero_billet'] ?? null);
            $membre->setTarif($data['tarif'] ?? null);
            
            if ($data['date_creation']) {
                $membre->setDateCreation(new DateTime($data['date_creation']));
            }
            if ($data['date_seance']) {
                $membre->setDateSeance(new DateTime($data['date_seance']));
            }
            
            $membre->setMontantTarif($data['montant_tarif'] ?? null);
            $membre->setCodePromo($data['code_promo'] ?? null);
            $membre->setMontantCodePromo($data['montant_code_promo'] ?? null);

            $this->entityManager->persist($membre);
        }

        $this->entityManager->flush();

        $this->addFlash('success', "Import réussi! $importedCount nouveaux membres ajoutés, $updatedCount mis à jour.");
        return $this->redirectToRoute('admin_membres_index');
    }

    #[Route('/{id}/delete', name: 'admin_membres_delete', methods: ['POST'])]
    public function delete(Membre $membre, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete' . $membre->getId(), $request->request->get('_token'))) {
            $this->membreRepository->remove($membre, true);
            $this->addFlash('success', 'Membre supprimé avec succès');
        } else {
            $this->addFlash('error', 'Erreur de sécurité');
        }

        return $this->redirectToRoute('admin_membres_index');
    }

    private function parseDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof DateTime) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value)) {
            // Excel date format (days since 1900-01-01)
            $excelDate = intval($value);
            $date = new DateTime('1900-01-01');
            $date->modify('+' . ($excelDate - 2) . ' days');
            return $date->format('Y-m-d');
        }

        try {
            $date = new DateTime($value);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function handleFileUpload(Request $request): Response
    {
        return $this->importPreview($request);
    }
}
