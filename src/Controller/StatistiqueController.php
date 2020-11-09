<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Depot;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
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

/**
 * @Route("/admin")
 */
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
     * @Route("/statistique/depots", name="depots_statistique", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function depotSelonSemaineEtAnnee(Request $request)
    {
        $annee = $request->get('annee');
        $semaine = $request->get('semaine');

        $depot = $this->getDoctrine()->getRepository(Commande::class)->findDepotSelonSemaineEtAnnee($annee, $semaine);

        return new JsonResponse($depot);
    }


    /**
     * @Route("/statistique/commande/semaine", name="commande_par_semaine_statistique", methods={"POST"})
     * @param Request $request
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function commandeParSemaine(Request $request)
    {
        $annee = $request->request->get('annee1');
        $semaine = $request->request->get('semaine1');

        $commandes = $this->getDoctrine()->getRepository(Commande::class)->findCommandesSelonSemaineEtAnnee($annee, $semaine);

        $spreadsheet = new Spreadsheet();

// donner des valeurs a la page et au titre
        $sheet = $spreadsheet->getActiveSheet()->setShowGridlines(false);
        $title = 'commande' . $annee . '-' . $semaine;
        $sheet->setTitle($title);

        $sheet->getStyle('A:F')->getAlignment()->setHorizontal('center');

// Titre en haut des colonnes
        if ($semaine == 0) {
            $texte = "Commandes de l'année " . $annee;
        } else {
            $texte = 'Commandes de la semaine ' . $semaine . ' de ' . $annee;
        }
        $sheet->getStyle('A1')->getAlignment()->setVertical('top');
        $sheet->mergeCells('A1:F2');
        $richText = new RichText();
        $richText->createTextRun($texte)->getFont()->setBold(true);
        $sheet->setCellValue('A1', $richText);


        $sheet->setCellValue('A3', '#');
        $sheet->setCellValue('B3', 'Client');
        $sheet->setCellValue('C3', 'Depot');
        $sheet->setCellValue('D3', 'Date Creation');
        $sheet->setCellValue('E3', 'Date Paiement');
        $sheet->setCellValue('F3', 'Montant');

        $sheet->getStyle('A3:' . $sheet->getHighestColumn() . '3')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ff6347');

// remplissage de la feuille
        $this->remplirFeuille($commandes, $sheet);


// Envoi de la feuille a l'utilisateur
        return $this->sendFile($request, $spreadsheet, $title, $this);
    }

    /**
     * @Route("/statistique/commande/depot", name="commande_par_depot_statistique", methods={"POST"})
     * @param Request $request
     * @return BinaryFileResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function commandeParDepot(Request $request)
    {
        $annee = $request->request->get('annee2');
        $semaine = $request->request->get('semaine2');
        $idDepot = $request->request->get('depot2');

        $depot = $this->getDoctrine()->getRepository(Depot::class)->find($idDepot);

        $commandes = $this->getDoctrine()->getRepository(Commande::class)->findCommandesSelonDepot($annee, $semaine, $depot);
        $spreadsheet = new Spreadsheet();

// donner des valeurs a la page et au titre
        $sheet = $spreadsheet->getActiveSheet()->setShowGridlines(false);
        $title = 'commande' . $annee . '-' . $semaine . '-' . $depot->getNom();
        $sheet->setTitle($title);
        $sheet->getStyle('A:E')->getAlignment()->setHorizontal('center');

        // Affichage du depot avec adresse
        $texte = "Depot : " . $depot->getNom() . "\n"
            . $depot->getAdresse() . "\n"
            . $depot->getCodePostal() . " "
            . $depot->getVille() . "\n"
            . $depot->getTelephone() . " "
            . $depot->getEmail();

//        $sheet->getStyle('A1')->getAlignment()->setVertical('top');
        $sheet->mergeCells('A1:E4');
        $richText = new RichText();
        $richText->createTextRun($texte)->getFont()->setBold(true);
        $sheet->setCellValue('A1', $richText);


        $sheet->mergeCells('A5:E5');

        $subtitre = 'Commandes de la semaine ' . $semaine . ' de ' . $annee;
        $sheet->setCellValue('A5', $subtitre);

        $sheet->getStyle('A1:E4')->getAlignment()->setWrapText(true);

        $sheet->setCellValue('A6', 'Produit');
        $sheet->setCellValue('B6', 'Producteur');
        $sheet->setCellValue('C6', 'Quantite');
        $sheet->setCellValue('D6', 'Prix');
        $sheet->setCellValue('E6', 'Total');


        $sheet->getStyle('A6:' . $sheet->getHighestColumn() . '6')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ff6347');

        $sheet->getStyle('A1:E6')->getBorders()->getHorizontal()->setBorderStyle(Border::BORDER_THICK);


// remplissage de la feuille
        $this->remplirFeuilleDepot($commandes, $sheet);


// Envoi de la feuille a l'utilisateur
        return $this->sendFile($request, $spreadsheet, $title, $this);
    }


    private function remplirFeuille($commandes, $sheet) {

        $j = $sheet->getHighestDataRow() + 1;
        foreach ($commandes as $com) {
            $sheet->setCellValue('A' . $j, $com->getId());
            $sheet->setCellValue('B' . $j, $com->getUser()->getNom());
            $sheet->setCellValue('C' . $j, $com->getDepot()->getNom());
            $sheet->setCellValue('D' . $j, ($com->getDateCreation())->format('d-m-Y'));
            $sheet->setCellValue('E' . $j, ($com->getDateLivraison())->format('d-m-Y'));
            $sheet->setCellValue('F' . $j, $com->getMontant());
            if ($j % 2 == 1) {
                $sheet->getStyle('A' . $j . ':' . $sheet->getHighestDataColumn() . $j)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('e5e5e5');
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
        return $sheet;
    }


    private function remplirFeuilleDepot($commandes, $sheet)
    {
        $j = $sheet->getHighestDataRow() + 1;
        foreach ($commandes as $com) {

            $sheet->mergeCells('A'.$j.':E'.$j);
            $sheet->setCellValue('A'.$j,'Commande n°'.$com->getId().' pour '.$com->getUser()->getNom());
            $sheet->getStyle('A'.$j.':E'.$j)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK);
            $j++;
            foreach ($com->getDetails() as $detail) {

                $sheet->setCellValue('A' . $j, $detail->getProduit()->getNom());
                $sheet->setCellValue('B' . $j, $detail->getProduit()->getProducteur()->getNom());
                $sheet->setCellValue('C' . $j, $detail->getQuantite());
                $sheet->setCellValue('D' . $j, $detail->getPrix());
                $sheet->setCellValue('E' . $j, ($detail->getQuantite()*$detail->getPrix()));


                if ($j % 2 == 1) {
                    $sheet->getStyle('A' . $j . ':' . $sheet->getHighestDataColumn() . $j)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('e5e5e5');
                }
                $j++;
            }

        }

        $sheet->getStyle('A1:' . $sheet->getHighestDataColumn() . $sheet->getHighestDataRow())->getBorders()->getVertical()->setBorderStyle(Border::BORDER_THICK);
        $sheet->getStyle('A1:' . $sheet->getHighestDataColumn() . $sheet->getHighestDataRow())->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK);
//        $sheet->getStyle('A1:' . $sheet->getHighestDataColumn() . $sheet->getHighestDataRow())->getBorders()->getHorizontal()->setBorderStyle(Border::BORDER_THIN);

        // met toutes les colonnes en auto width pour s'adapter au texte
        foreach (range('A', $sheet->getHighestDataColumn()) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

    }


    private function sendFile(Request $request, Spreadsheet $spreadsheet, string $title, StatistiqueController $param)
    {
        // Envoi le fichier correspondant au bouton choisi
        if ($request->request->has('excel')) {
// Crée un fichier excel
            $writer = new XLsx($spreadsheet);

// Création d'un fichier temporaire
            $fileName = $title . '.xlsx';
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);

// Sauve le fichier excel dans le fichier temporaire
            $writer->save($temp_file);


            return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);

        } else {

// Crée le fichier PDF
            $pdf = IOFactory::createWriter($spreadsheet, 'Dompdf');

// Création d'un fichier temporaire
            $fileName = $title . '.pdf';
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);

// Sauve le fichier excel dans le fichier temporaire
            $pdf->save($temp_file);

            return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        }
    }




}
