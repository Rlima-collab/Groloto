<?php

namespace App\Controller\Admin;

use App\Entity\ExcelData;
use App\Repository\ExcelDataRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use DateTime;

#[Route('/admin/excel')]
#[IsGranted('ROLE_ADMIN')]
class ExcelController extends AbstractController
{
    public function __construct(
        private ExcelDataRepository $excelDataRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'admin_excel_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $currentSheet = null;
        $sheets = $this->excelDataRepository->findAllSheets();

        if ($request->query->has('sheet')) {
            $currentSheet = $this->excelDataRepository->findBySheetName($request->query->get('sheet'));
        } elseif (!empty($sheets)) {
            $currentSheet = $sheets[0];
        }

        if ($request->isMethod('POST') && $request->files->has('excel_file')) {
            return $this->handleFileUpload($request);
        }

        return $this->render('admin/excel/index.html.twig', [
            'sheets' => $sheets,
            'current_sheet' => $currentSheet,
            'page_title' => 'Gestion Excel',
        ]);
    }

    #[Route('/upload', name: 'admin_excel_upload', methods: ['POST'])]
    public function upload(Request $request): Response
    {
        if (!$request->files->has('excel_file')) {
            $this->addFlash('error', 'Veuillez sélectionner un fichier');
            return $this->redirectToRoute('admin_excel_index');
        }

        $file = $request->files->get('excel_file');

        try {
            $reader = new XlsxReader();
            $spreadsheet = $reader->load($file->getPathname());

            foreach ($spreadsheet->getSheetNames() as $sheetName) {
                $sheet = $spreadsheet->getSheetByName($sheetName);
                $data = [];
                $columns = [];

                $iterator = $sheet->getRowIterator(1, 1);
                foreach ($iterator as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    foreach ($cellIterator as $cell) {
                        if ($cell->getValue() !== null) {
                            $columns[] = $cell->getValue();
                        }
                    }
                    break;
                }

                $iterator = $sheet->getRowIterator(2);
                foreach ($iterator as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    $rowData = [];
                    $colIndex = 0;
                    $hasData = false;

                    foreach ($cellIterator as $cell) {
                        if (isset($columns[$colIndex])) {
                            $value = $cell->getValue();
                            $rowData[$columns[$colIndex]] = $value;
                            if ($value !== null) {
                                $hasData = true;
                            }
                        }
                        $colIndex++;
                    }

                    if ($hasData) {
                        $data[] = $rowData;
                    }
                }

                if (!empty($columns)) {
                    // Toujours créer une nouvelle entrée, même si le nom existe
                    $excelData = new ExcelData();
                    $excelData->setSheetName($sheetName);
                    $excelData->setColumns($columns);
                    $excelData->setData($data);
                    $excelData->setImportedAt(new DateTime());
                    $excelData->setOriginalFilename($file->getClientOriginalName());
                    $this->entityManager->persist($excelData);
                }
            }

            $this->entityManager->flush();
            $this->addFlash('success', 'Fichier Excel importé avec succès !');

            return $this->redirectToRoute('admin_excel_index');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'import: ' . $e->getMessage());
            return $this->redirectToRoute('admin_excel_index');
        }
    }

    #[Route('/export', name: 'admin_excel_export', methods: ['GET'])]
    public function export(Request $request): StreamedResponse
    {
        $sheets = $this->excelDataRepository->findAllSheets();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        foreach ($sheets as $excelData) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($excelData->getSheetName());

            $columns = $excelData->getColumns();
            $data = $excelData->getData();

            // Headers
            foreach ($columns as $index => $columnName) {
                $columnLetter = $this->getColumnLetter($index + 1);
                $sheet->getCell($columnLetter . '1')->setValue($columnName);
            }

            // Data
            $row = 2;
            foreach ($data as $rowData) {
                foreach ($columns as $index => $columnName) {
                    $columnLetter = $this->getColumnLetter($index + 1);
                    $value = $rowData[$columnName] ?? '';
                    $sheet->getCell($columnLetter . $row)->setValue($value);
                }
                $row++;
            }
        }

        $filename = 'export_' . date('Y-m-d_H-i-s') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filename . '"');

        return $response;
    }

    #[Route('/{id}/delete', name: 'admin_excel_delete', methods: ['POST'])]
    public function deleteSheet(ExcelData $excelData, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete' . $excelData->getId(), $request->request->get('_token'))) {
            $this->excelDataRepository->remove($excelData, true);
            $this->addFlash('success', 'Feuille supprimée avec succès');
        }

        return $this->redirectToRoute('admin_excel_index');
    }

    private function handleFileUpload(Request $request): Response
    {
        return $this->upload($request);
    }

    /**
     * Convert numeric column index (1-based) to Excel column letter (A, B, ... Z, AA, AB, etc.)
     */
    private function getColumnLetter(int $index): string
    {
        $letter = '';
        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)) . $letter;
            $index = (int)($index / 26);
        }
        return $letter;
    }
}
