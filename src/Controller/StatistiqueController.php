<?php

namespace App\Controller;

use App\Entity\Commande;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class StatistiqueController extends AbstractController
{
    /**
     * @Route("/statistique", name="statistique")
     */
    public function index()
    {
        $annees = $this->getDoctrine()->getRepository(Commande::class)->findAnneesAvecCommande();


        $param = [
            'annee' => 'annees',
            'producteur' => 'producteurs',
            'depot' => 'depots',
            'commande' => 'commandes',
            'client' => 'clients'
        ];

        return $this->render('statistique/index.html.twig', [
            'param' => $param, 'anneeCommande' => $annees
        ]);
    }

    /**
     * @Route("/statistique/filtre", name="filtre_statistique", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function traitementFiltres(Request $request)
    {
        $listePHP = $request->getContent();
        $listeDecode = json_decode($listePHP);

        if (in_array("annees", $listeDecode)) {

        }


        return new JsonResponse($listeDecode);
    }

    /**
     * @Route("/etat", name="etat")
     */
    public function etat()
    {
        $annees = $this->getDoctrine()->getRepository(Commande::class)->findAnneesAvecCommande();

        return $this->render('statistique/etats.html.twig', ['annees' => $annees]);
    }

    /**
     * @Route("/statistique/semaines", name="semaines_statistique", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function semainesSelonAnnee(Request $request)
    {
        $annee = $request->get('annee');
        $semaines = $this->getDoctrine()->getRepository(Commande::class)->findSemaineDesCommandesSelonAnnee($annee);
        return new JsonResponse($semaines);
    }

    /**
     * @Route("/statistique/commande/semaine", name="commande_par_semaine_statistique", methods={"POST"})
     * @param Request $request
     * @return BinaryFileResponse|RedirectResponse
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function commandeParSemaine(Request $request)
    {

        $annee = $request->request->get('annee1');
        $semaine = $request->request->get('semaine1');

        $commandes = $this->getDoctrine()->getRepository(Commande::class)->findCommandesSelonSemaineEtAnnee($annee, $semaine);

//        dd($commandes);



        $spreadsheet = new Spreadsheet();
// donner des valeurs a la page et au titre
        $sheet = $spreadsheet->getActiveSheet()->setShowGridlines(false);
//                ->setShowGridlines(true);
        $title = 'commande' . $annee . '-' . $semaine;
        $sheet->setTitle($title);

// Mise en place des titres de colonnes
        $sheet->getStyle('A:F')->getAlignment()->setHorizontal('center');

        $sheet->setCellValue('A1', '#');
        $sheet->setCellValue('B1', 'Client');
        $sheet->setCellValue('C1', 'Depot');
        $sheet->setCellValue('D1', 'Date Creation');
        $sheet->setCellValue('E1', 'Date Paiement');
        $sheet->setCellValue('F1', 'Montant');

        $sheet->getStyle('A1:F1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ff6347');


// remplissage de la feuille
        $j = 2;
        foreach ($commandes as $com) {

            $sheet->setCellValue('A' . $j, $com->getId());
            $sheet->setCellValue('B' . $j, $com->getUser()->getNom());
            $sheet->setCellValue('C' . $j, $com->getDepot()->getNom());
            $sheet->setCellValue('D' . $j, ($com->getDateCreation())->format('d-m-Y'));
            $sheet->setCellValue('E' . $j, ($com->getDateLivraison())->format('d-m-Y'));
            $sheet->setCellValue('F' . $j, $com->getMontant());
            if ($j % 2 == 1) {
                $sheet->getStyle('A' . $j . ':F' . $j)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('e5e5e5');
            }
            $j++;
        }

        $sheet->getStyle('A1:' . $sheet->getHighestDataColumn() . $sheet->getHighestDataRow())->getBorders()->getVertical()->setBorderStyle(Border::BORDER_THICK);
        $sheet->getStyle('A1:' . $sheet->getHighestDataColumn() . $sheet->getHighestDataRow())->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK);
        $sheet->getStyle('A1:' . $sheet->getHighestDataColumn() . $sheet->getHighestDataRow())->getBorders()->getHorizontal()->setBorderStyle(Border::BORDER_THIN);

// met toutes les colonnes en auto width pour s'adapter au texte
        foreach (range('A', $sheet->getHighestDataColumn()) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        if ($request->request->has('excel')) {
// Crée un fichier excel
            $writer = new XLsx($spreadsheet);


// Create a Temporary file in the system
            $fileName = 'commande-' . $annee . '-' . $semaine . '.xlsx';
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);

// Create the excel file in the tmp directory of the system
            $writer->save($temp_file);

            return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);

        } else {
            $pdf = IOFactory::createWriter($spreadsheet, 'Dompdf');
            $fileName = 'commande-' . $annee . '-' . $semaine . '.pdf';
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);

            $pdf->save($temp_file);

            return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        }
    }


}
