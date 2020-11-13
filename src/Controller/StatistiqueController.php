<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Depot;
use App\Entity\Detail;
use App\Entity\Producteur;
use App\Entity\Produit;
use App\Entity\User;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class StatistiqueController extends AbstractController
{
    const mois = [1 => 'Janvier', 2 => 'Fevrier', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'];

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

//        if (in_array("annees", $listeDecode)) {
//
//        }


        return new JsonResponse($listeDecode);
    }

    /**
     * @Route("/etat", name="etat")
     */
    public function etat()
    {
        $annees = $this->getDoctrine()->getRepository(Commande::class)->findAnneesAvecCommande();

        return $this->render('statistique/etats.html.twig', ['annees' => $annees, 'mois' => self::mois]);
    }

    /**
     * @Route("/statistique/semaines", name="semaines_statistique", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function moisEtSemainesSelonAnnee(Request $request)
    {
        setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
        $annee = $request->get('annee');

        $semaines = $this->getDoctrine()->getRepository(Commande::class)->findSemaineDesCommandesSelonAnnee($annee);
        $mois = $this->getDoctrine()->getRepository(Commande::class)->findMoisDesCommandesSelonAnnee($annee);

        $listeMois = [];
        foreach ($mois as $m) {
            foreach ($m as $key => $value) {
                $nom = utf8_encode(strftime('%B', mktime(0, 0, 0, $value, 1)));
                $listeMois[$value] = $nom;
            }


        }

        $reponse = ['semaines' => $semaines, 'mois' => $listeMois];
        json_encode($reponse);
        return new JsonResponse($reponse);
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
        $mois = $request->get('mois');

        $depot = $this->getDoctrine()->getRepository(Commande::class)->findDepotSelonAnneeEtMoisOuSemaine($annee, $mois, $semaine);

        return new JsonResponse($depot);
    }

    /**
     * @Route("/statistique/producteurs", name="producteurs_statistique", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function producteursSelonSemaineEtAnnee(Request $request)
    {
        $annee = $request->get('annee');
        $semaine = $request->get('semaine');
        $mois = $request->get('mois');

        $producteur = $this->getDoctrine()->getRepository(Commande::class)->findProducteurSelonAnneeEtSemaineOuMois($annee, $mois, $semaine);
        return new JsonResponse($producteur);
    }

    //    Creation des fichiers

    /**
     * @Route("/statistique/commande/semaine", name="commande_par_semaine_statistique", methods={"POST"})
     * @param Request $request
     * @return BinaryFileResponse
     * @throws Exception
     */
    public function commandeParSemaineOuMois(Request $request)
    {
        $annee = $request->request->get('annee1');
        $semaine = $request->request->get('semaine1');
        $mois = $request->request->get('mois1');

        if ($request->request->has('pdf')) {
            $typeFichier = 'pdf';
        } else {
            $typeFichier = 'excel';
        }

        $commandes = $this->getDoctrine()->getRepository(Commande::class)->findCommandesSelonSemaineEtAnnee($annee, $mois, $semaine);

        $spreadsheet = new Spreadsheet();

// donner des valeurs a la page et au titre
        $sheet = $spreadsheet->getActiveSheet()->setShowGridlines(false);

// Titre en haut des colonnes
        if ($semaine == 0 and $mois == 0) {
            $title = 'commande_' . $annee;
            $texte = "Commandes de l'année " . $annee;
        } else if ($semaine == 0 and $mois != 0) {
            $title = 'commande_' . $annee . '_' . self::mois[$mois];
            $texte = 'Commandes du mois de ' . self::mois[$mois] . ' de ' . $annee;
        } else {
            $title = 'commande_' . $annee . '_' . $semaine;
            $texte = 'Commandes de la semaine ' . $semaine . ' de ' . $annee;
        }

        $sheet->setTitle($title);
        $sheet->getStyle('A:F')->getAlignment()->setHorizontal('center');

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
        $this->remplirFeuilleCommandeParDate($commandes, $sheet, $typeFichier);

// Création d'une page pour les details de chaque commande si excel
        if ($request->request->has('excel')) {
            $x = 4;
            $y = 1;
            foreach ($commandes as $commande) {
                $subSheet = $spreadsheet->createSheet();
                $sheetTitle = $sheet->getTitle();
                $this->remplirDetailsCommande($subSheet, $commande, $sheetTitle);
                $subSheet->setTitle($y . $commande->getUser()->getNom());
                $subTitle = $subSheet->getTitle();

                $sheet->setCellValue('G' . $x, '->');
                $sheet->getCell('G' . $x)->getHyperlink()->setUrl("sheet://" . $subTitle . "!A1");
                $x++;
                $y++;
            }
        }


// Envoi de la feuille a l'utilisateur
        return $this->sendFile($request, $spreadsheet, $title, $this);
    }

    /**
     * @Route("/statistique/commande/depot", name="commande_par_depot_statistique", methods={"POST"})
     * @param Request $request
     * @return BinaryFileResponse
     * @throws Exception
     */
    public function commandeParDepot(Request $request)
    {
        $annee = $request->request->get('annee2');
        $semaine = $request->request->get('semaine2');
        $mois = $request->request->get('mois2');
        $idDepot = $request->request->get('depot2');

        $depot = $this->getDoctrine()->getRepository(Depot::class)->find($idDepot);

        $commandes = $this->getDoctrine()->getRepository(Commande::class)->findCommandesSelonDepot($annee, $mois, $semaine, $depot);
        $spreadsheet = new Spreadsheet();

// donner des valeurs a la page et au titre
        $sheet = $spreadsheet->getActiveSheet()->setShowGridlines(false);
        if ($semaine != 0) {
            $title = $annee . '-' . $semaine . '-' . strtok($depot->getNom(), ' ');
            $subtitre = 'Commandes de la semaine ' . $semaine . ' de ' . $annee;
        } else {
            $title = $annee . '-' . self::mois[$mois] . '-' . strtok($depot->getNom(), ' ');
            $subtitre = 'Commandes du mois de ' . self::mois[$mois] . ' de ' . $annee;
        }
        $sheet->setTitle($title);
        $sheet->getStyle('A:E')->getAlignment()->setHorizontal('center');

        // Affichage du depot avec adresse
        $texte = "Depot : " . $depot->getNom() . "\n"
            . $depot->getAdresse() . "\n"
            . $depot->getCodePostal() . " "
            . $depot->getVille() . "\n"
            . $depot->getTelephone() . " "
            . $depot->getEmail();

        $sheet->mergeCells('A1:E4');
        $richText = new RichText();
        $richText->createTextRun($texte)->getFont()->setBold(true);
        $sheet->setCellValue('A1', $richText);

// Sous titre
        $sheet->mergeCells('A5:E5');
        $sheet->setCellValue('A5', $subtitre);

// nom des colonnes
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
        $this->remplirFeuilleCommandeParDepot($commandes, $sheet);

// Envoi de la feuille a l'utilisateur
        return $this->sendFile($request, $spreadsheet, $title, $this);
    }

    /**
     * @Route("/statistique/commande/producteur", name="commande_par_producteur_statistique", methods={"POST"})
     * @param Request $request
     * @return BinaryFileResponse
     * @throws Exception
     */
    public function commandeParProducteur(Request $request)
    {
        $annee = $request->request->get('annee3');
        $mois = $request->request->get('mois3');
        $semaine = $request->request->get('semaine3');
        $idProducteur = $request->request->get('producteur3');
        $producteur = $this->getDoctrine()->getRepository(Producteur::class)->find($idProducteur);

        $detail = $this->getDoctrine()->getRepository(Detail::class)->findDetailSelonProducteur($annee, $mois, $semaine, $producteur);

        $spreadsheet = new Spreadsheet();

// donner des valeurs a la page et au titre
        $sheet = $spreadsheet->getActiveSheet()->setShowGridlines(false);

        if ($semaine != 0) {
            $title = $annee . '-' . $semaine . '-' . strtok($producteur->getNom(), ' ');
            $subtitre = 'Produits de la semaine ' . $semaine . ' de ' . $annee;
        } else {
            $title = $annee . '-' . self::mois[$mois] . '-' . strtok($producteur->getNom(), ' ');
            $subtitre = 'Produits du mois de ' . self::mois[$mois] . ' de ' . $annee;
        }

        $sheet->setTitle($title);
        $sheet->getStyle('A:E')->getAlignment()->setHorizontal('center');

        // Affichage du depot avec adresse
        $texte = "Producteur : " . $producteur->getNom() . "\n"
            . $producteur->getAdresse() . "\n"
            . $producteur->getCodePostal() . " "
            . $producteur->getVille() . "\n"
            . $producteur->getTelephone() . " "
            . $producteur->getEmail();

        $sheet->mergeCells('A1:E4');
        $richText = new RichText();
        $richText->createTextRun($texte)->getFont()->setBold(true);
        $sheet->setCellValue('A1', $richText);
        $sheet->getStyle('A1:E4')->getAlignment()->setWrapText(true);

        $sheet->mergeCells('A5:E5');

        $sheet->setCellValue('A5', $subtitre);
        $sheet->getStyle('A5:E5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('90ee90');

        $sheet->setCellValue('A6', '# commande');
        $sheet->setCellValue('B6', 'Dépôt');
        $sheet->setCellValue('C6', 'Client');
        $sheet->setCellValue('D6', 'Produit');
        $sheet->setCellValue('E6', 'Quantité');

        $sheet->getStyle('A6:' . $sheet->getHighestColumn() . '6')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ff6347');

        $sheet->getStyle('A1:E6')->getBorders()->getHorizontal()->setBorderStyle(Border::BORDER_THICK);

        $this->remplirFeuilleCommandeParProducteur($detail, $sheet);

        return $this->sendFile($request, $spreadsheet, $title, $this);
    }

    /**
     * @Route("/statistique/vente/producteur", name="vente_par_producteur_statistique", methods={"POST"})
     * @param Request $request
     * @return BinaryFileResponse
     * @throws Exception
     */
    public function venteParProducteur(Request $request)
    {
        $annee = $request->request->get('annee4');
        $mois = $request->request->get('mois4');
        $semaine = $request->request->get('semaine4');
        $idProducteur = $request->request->get('producteur4');
        $producteur = $this->getDoctrine()->getRepository(Producteur::class)->find($idProducteur);

        $detail = $this->getDoctrine()->getRepository(Detail::class)->findProduitSelonProducteur($annee, $mois, $semaine, $producteur);
//dd($detail);
        $spreadsheet = new Spreadsheet();

// donner des valeurs a la page et au titre
        $sheet = $spreadsheet->getActiveSheet()->setShowGridlines(false);

        if ($semaine != 0) {
            $title = $annee . '-' . $semaine . '-' . strtok($producteur->getNom(), ' ');
            $subtitre = 'Vente de la semaine ' . $semaine . ' de ' . $annee;
        } else {
            $title = $annee . '-' . self::mois[$mois] . '-' . strtok($producteur->getNom(), ' ');
            $subtitre = 'Vente du mois de ' . self::mois[$mois] . ' de ' . $annee;

        }
        $sheet->setTitle($title);
        $sheet->getStyle('A:E')->getAlignment()->setHorizontal('center');

        // Affichage du depot avec adresse
        $texte = "Producteur : " . $producteur->getNom() . "\n"
            . $producteur->getAdresse() . "\n"
            . $producteur->getCodePostal() . " "
            . $producteur->getVille() . "\n"
            . $producteur->getTelephone() . " "
            . $producteur->getEmail();

        $sheet->mergeCells('A1:E4');
        $richText = new RichText();
        $richText->createTextRun($texte)->getFont()->setBold(true);
        $sheet->setCellValue('A1', $richText);
        $sheet->getStyle('A1:E4')->getAlignment()->setWrapText(true);

        $sheet->mergeCells('A5:E5');

        $sheet->setCellValue('A5', $subtitre);
        $sheet->getStyle('A5:E5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('90ee90');

        $sheet->setCellValue('A6', 'Description');
        $sheet->setCellValue('B6', 'Produit');
        $sheet->setCellValue('C6', 'Quantité');
        $sheet->setCellValue('D6', 'Prix Unitaire');
        $sheet->setCellValue('E6', 'Total');

        $sheet->getStyle('A6:' . $sheet->getHighestColumn() . '6')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ff6347');

        $sheet->getStyle('A1:E6')->getBorders()->getHorizontal()->setBorderStyle(Border::BORDER_THICK);

        $this->remplirFeuilleVenteParProducteur($detail, $sheet);

        return $this->sendFile($request, $spreadsheet, $title, $this);
    }

    /**
     * @Route("/statistique/commande/categorie", name="vente_par_categorie_statistique", methods={"POST"})
     * @param Request $request
     * @return BinaryFileResponse
     * @throws Exception
     */
    public function commandeParCategorie(Request $request)
    {
        $annee = $request->request->get('annee5');
        $semaine = $request->request->get('semaine5');
        $mois = $request->request->get('mois5');

        $produits = $this->getDoctrine()->getRepository(Produit::class)->findProduitSelonCategorie($annee, $mois, $semaine);
//        dd($produits);
        $spreadsheet = new Spreadsheet();

// donner des valeurs a la page et au titre
        $sheet = $spreadsheet->getActiveSheet()->setShowGridlines(false);

// Titre en haut des colonnes
        if ($semaine == 0 and $mois == 0) {
            $title = 'Vente_' . $annee;
            $texte = "Ventes par catégories de l'année " . $annee;
        } else if ($semaine == 0 and $mois != 0) {
            $title = 'Vente_' . $annee . '_' . self::mois[$mois];
            $texte = 'Ventes par catégories du mois de ' . self::mois[$mois] . ' de ' . $annee;
        } else {
            $title = 'Vente_' . $annee . '_' . $semaine;
            $texte = 'Ventes par catégories de la semaine ' . $semaine . ' de ' . $annee;
        }

        $sheet->setTitle($title);
        $sheet->getStyle('A:F')->getAlignment()->setHorizontal('center');

        $sheet->getStyle('A1')->getAlignment()->setVertical('top');
        $sheet->mergeCells('A1:F1');
        $richText = new RichText();
        $richText->createTextRun($texte)->getFont()->setBold(true);
        $sheet->setCellValue('A1', $richText);
        $sheet->getStyle('A1:F1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('90ee90');


        $sheet->setCellValue('A2', 'Produit');
        $sheet->setCellValue('B2', 'Sous-Catégorie');
        $sheet->setCellValue('C2', 'Description');
        $sheet->setCellValue('D2', 'Prix');
        $sheet->setCellValue('E2', 'Quantité');
        $sheet->setCellValue('F2', 'Total');

        $sheet->getStyle('A2:F2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ff6347');

// remplissage de la feuille
        $this->remplirFeuilleVenteParCategorie($produits, $sheet);

// Envoi de la feuille a l'utilisateur
        return $this->sendFile($request, $spreadsheet, $title, $this);
    }

    /**
     * @Route("/statistique/clients", name="clients_statistique", methods={"POST"})
     * @param Request $request
     * @return BinaryFileResponse
     * @throws Exception
     */
    public function listeClients(Request $request)
    {
        if ($request->request->has('pdf')) {
            $typeFichier = 'pdf';
        } else {
            $typeFichier = 'excel';
        }
        $clients = $this->getDoctrine()->getRepository(User::class)->findClientsStatistiques();

        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet()->setShowGridlines(false);
        $title = 'Liste des Clients';
        $sheet->setTitle($title);
        $sheet->getStyle('A:F')->getAlignment()->setHorizontal('center');

        $date = date('d/m/Y');
        $sheet->mergeCells('A1:F1');
        $texte = 'Liste des clients au ' . $date;
        $richText = new RichText();
        $richText->createTextRun($texte)->getFont()->setBold(true);
        $sheet->setCellValue('A1', $richText);
        $sheet->getStyle('A1:F1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('90ee90');

        $sheet->setCellValue('A2', 'Nom');
        $sheet->setCellValue('B2', 'Prénom');
        $sheet->setCellValue('C2', 'Email');

        $sheet->setCellValue('D2', 'Première commande');
        $sheet->setCellValue('E2', 'Dernière commande');
        $sheet->setCellValue('F2', 'Commandes');

        $sheet->getStyle('A2:' . $sheet->getHighestColumn() . '2')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ff6347');

        $sheet->getStyle('A1:F2')->getBorders()->getHorizontal()->setBorderStyle(Border::BORDER_THICK);

        $this->remplirFeuilleListeClients($clients, $sheet, $typeFichier);

        return $this->sendFile($request, $spreadsheet, $title, $this);
    }

    private function remplirFeuilleCommandeParDate($commandes, Worksheet $sheet, $typeFichier)
    {

        $j = $sheet->getHighestDataRow() + 1;
        foreach ($commandes as $com) {
            $sheet->setCellValue('A' . $j, $com->getId());
            $sheet->setCellValue('B' . $j, $com->getUser()->getNom());
            $sheet->setCellValue('C' . $j, $com->getDepot()->getNom());
            if ($typeFichier == 'pdf') {
                $sheet->setCellValue('D' . $j, ($com->getDateCreation())->format('d-m-Y'));
                $sheet->setCellValue('E' . $j, ($com->getDateLivraison())->format('d-m-Y'));
            } else {
                $sheet->setCellValue('D' . $j, (strftime('%A %d %B %Y', $com->getDateCreation()->getTimeStamp())));
                $sheet->setCellValue('E' . $j, (strftime('%A %d %B %Y', $com->getDateLivraison()->getTimeStamp())));
            }
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

    private function remplirFeuilleCommandeParDepot($commandes, Worksheet $sheet)
    {
        $j = $sheet->getHighestDataRow() + 1;
        foreach ($commandes as $com) {

            $sheet->mergeCells('A' . $j . ':E' . $j);
            $sheet->setCellValue('A' . $j, 'Commande n°' . $com->getId() . ' pour ' . $com->getUser()->getNom() . ' ' . $com->getUser()->getPrenom());
            $sheet->getStyle('A' . $j . ':E' . $j)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('90ee90');
            $sheet->getStyle('A' . $j . ':E' . $j)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK);
            $j++;
            foreach ($com->getDetails() as $detail) {
                $sheet->setCellValue('A' . $j, $detail->getProduit()->getNom());
                $sheet->setCellValue('B' . $j, $detail->getProduit()->getProducteur()->getNom());
                $sheet->setCellValue('C' . $j, $detail->getQuantite());
                $sheet->setCellValue('D' . $j, $detail->getPrix());
                $sheet->setCellValue('E' . $j, ($detail->getQuantite() * $detail->getPrix()));


                if ($j % 2 == 1) {
                    $sheet->getStyle('A' . $j . ':' . $sheet->getHighestDataColumn() . $j)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('e5e5e5');
                }
                $j++;
            }
            $sheet->mergeCells('A' . $j . ':D' . $j);
            $sheet->setCellValue('A' . $j, 'Montant ');
            $sheet->setCellValue('E' . $j, $com->getMontant());
            $sheet->getStyle('A' . $j . ':E' . $j)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('add8e6');
            $sheet->getStyle('A' . $j . ':E' . $j)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
            $j++;

        }

        $sheet->getStyle('A1:' . $sheet->getHighestDataColumn() . $sheet->getHighestDataRow())->getBorders()->getVertical()->setBorderStyle(Border::BORDER_THICK);
        $sheet->getStyle('A1:' . $sheet->getHighestDataColumn() . $sheet->getHighestDataRow())->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK);


        // met toutes les colonnes en auto width pour s'adapter au texte
        foreach (range('A', $sheet->getHighestDataColumn()) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

    }

    private function remplirFeuilleCommandeParProducteur($detail, Worksheet $sheet)
    {

        $j = $sheet->getHighestDataRow() + 1;
        foreach ($detail as $det) {
            $sheet->setCellValue('A' . $j, $det->getCommande()->getId());
            $sheet->setCellValue('B' . $j, $det->getCommande()->getDepot()->getNom());
            $sheet->setCellValue('C' . $j, $det->getCommande()->getUser()->getNom());
            $sheet->setCellValue('D' . $j, $det->getProduit()->getNom());
            $sheet->setCellValue('E' . $j, $det->getQuantite());

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

    private function remplirFeuilleVenteParProducteur($detail, Worksheet $sheet)
    {
        $row = 0;
        $total = 0;
        $quantite = 0;
        $idProduit = 0;
        $j = $sheet->getHighestDataRow() + 1;

        foreach ($detail as $det) {
// check si le prochain produit est le meme que le precedent
            if ($det->getProduit()->getId() != $idProduit) {
                $row = $j;
                $sheet->setCellValue('A' . $j, $det->getProduit()->getDescription());
                $sheet->setCellValue('B' . $j, $det->getProduit()->getNom());
                $quantite = $det->getQuantite();
                $sheet->setCellValue('C' . $j, $quantite);
                $sheet->setCellValue('D' . $j, $det->getProduit()->getPrix());
                $total = ($det->getQuantite() * $det->getPrix());
                $sheet->setCellValue('E' . $j, $total);
                $idProduit = $det->getProduit()->getId();

                if ($j % 2 == 1) {
                    $sheet->getStyle('A' . $j . ':' . $sheet->getHighestDataColumn() . $j)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('e5e5e5');
                }
                $j++;
// si meme produit on modifie la quantité et le total
            } else {
                $total = $total + ($det->getQuantite() * $det->getPrix());
                $quantite = $quantite + $det->getQuantite();
                $sheet->setCellValue('C' . $row, $quantite);
                $sheet->setCellValue('E' . $row, $total);
            }
        }


        $sheet->mergeCells('A' . $j . ':D' . $j);
        $sheet->setCellValue('A' . $j, 'Montant Total');
        $sheet->setCellValue('E' . $j, '=SUM(E7:E' . ($j - 1) . ')');
        $sheet->getStyle('A' . $j . ':E' . $j)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('add8e6');
        $sheet->getStyle('A' . $j . ':E' . $j)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);


        $sheet->getStyle('A1:' . $sheet->getHighestDataColumn() . $sheet->getHighestDataRow())->getBorders()->getVertical()->setBorderStyle(Border::BORDER_THICK);
        $sheet->getStyle('A1:' . $sheet->getHighestDataColumn() . $sheet->getHighestDataRow())->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK);
        $sheet->getStyle('A1:' . $sheet->getHighestDataColumn() . $sheet->getHighestDataRow())->getBorders()->getHorizontal()->setBorderStyle(Border::BORDER_THIN);

        // met toutes les colonnes en auto width pour s'adapter au texte
        foreach (range('A', $sheet->getHighestDataColumn()) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        return $sheet;
    }

    private function remplirDetailsCommande(Worksheet $sheet, $commande, $sheetTitle)
    {
        $sheet->setShowGridlines(false);

        $sheet->getStyle('A:E')->getAlignment()->setHorizontal('center');

        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'Retour à la liste');
        $sheet->getCell('A1')->getHyperlink()->setUrl("sheet://" . $sheetTitle . "!A1");
        $sheet->getStyle('A1:E1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('033379');
        $sheet->getStyle('A1:E1')->getFont()->setColor(new Color(Color::COLOR_WHITE))->setBold(true);

        $texte = 'Commandes du client ' . $commande->getUser()->getNom();
        $sheet->mergeCells('A2:E2');
        $richText = new RichText();
        $richText->createTextRun($texte)->getFont()->setBold(true);
        $sheet->setCellValue('A2', $richText);
        $sheet->getStyle('A2:E2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('90ee90');

        $sheet->setCellValue('A3', 'Produit');
        $sheet->setCellValue('B3', 'Producteur');
        $sheet->setCellValue('C3', 'Quantite');
        $sheet->setCellValue('D3', 'Prix');
        $sheet->setCellValue('E3', 'Total');

        $sheet->getStyle('A3:' . $sheet->getHighestColumn() . '3')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('ff6347');

        $details = $commande->getDetails();

        $j = $sheet->getHighestDataRow() + 1;
        foreach ($details as $detail) {
            $sheet->setCellValue('A' . $j, $detail->getProduit()->getNom());
            $sheet->setCellValue('B' . $j, $detail->getProduit()->getProducteur()->getNom());
            $sheet->setCellValue('C' . $j, $detail->getQuantite());
            $sheet->setCellValue('D' . $j, $detail->getPrix());
            $sheet->setCellValue('E' . $j, ($detail->getQuantite() * $detail->getPrix()));
            if ($j % 2 == 1) {
                $sheet->getStyle('A' . $j . ':' . $sheet->getHighestDataColumn() . $j)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('e5e5e5');
            }
            $j++;
        }
        $sheet->mergeCells('A' . $j . ':D' . $j);
        $sheet->setCellValue('A' . $j, 'Montant ');
        $sheet->setCellValue('E' . $j, $commande->getMontant());
        $sheet->getStyle('A' . $j . ':E' . $j)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('add8e6');
        $sheet->getStyle('A' . $j . ':E' . $j)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle('A1:' . $sheet->getHighestDataColumn() . $sheet->getHighestDataRow())->getBorders()->getVertical()->setBorderStyle(Border::BORDER_THICK);
        $sheet->getStyle('A1:' . $sheet->getHighestDataColumn() . $sheet->getHighestDataRow())->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK);
        $sheet->getStyle('A1:' . $sheet->getHighestDataColumn() . $sheet->getHighestDataRow())->getBorders()->getHorizontal()->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A', $sheet->getHighestDataColumn()) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }


    }

    private function remplirFeuilleVenteParCategorie($produits, Worksheet $sheet)
    {
        $montant = 0;
        $idCatParent = 0;
        foreach ($produits as $produit) {
            $j = $sheet->getHighestDataRow() + 1;
            // si produit pas de la meme categorie que le precedent
            if ($produit->getCategorie()->getCatParent()->getId() != $idCatParent) {

                if ($idCatParent != 0) {
                    $sheet->mergeCells('A' . $j . ':D' . $j);
                    $sheet->setCellValue('A' . $j, 'Montant Total : ');
                    $sheet->setCellValue('F' . $j, $montant.' €');
                    $sheet->getStyle('A' . $j . ':E' . $j)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('033379');
                    $sheet->getStyle('A' . $j . ':F' . $j)->getFont()->setColor(new Color(Color::COLOR_WHITE))->setBold(true);
                    $sheet->getStyle('F' . $j)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ff0000');
                    $j++;
                }

                $idCatParent = $produit->getCategorie()->getCatParent()->getId();
                // Creation de l'intitulé de la categorie
                $sheet->mergeCells('A' . $j . ':F' . $j);
                $sheet->setCellValue('A' . $j, 'Catégorie :' . $produit->getCategorie()->getCatParent()->getNom());
                $sheet->getStyle('A' . $j . ':F' . $j)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('add8e6');
                $j++;

            }
            $sheet->setCellValue('A' . $j, $produit->getNom());
            $sheet->setCellValue('B' . $j, $produit->getCategorie()->getNom());
            $sheet->setCellValue('C' . $j, $produit->getDescription());
            $sheet->setCellValue('D' . $j, $produit->getPrix());
            $details = $produit->getDetails()->toArray();
            $quantite = 0;
            foreach ($details as $detail) {
                $quantite = $quantite + $detail->getQuantite();
            }
            $sheet->setCellValue('E' . $j, $quantite);
            $total = $quantite * $produit->getPrix();
            $sheet->setCellValue('F' . $j, $total);
            $montant = $montant + $total;

            if ($j % 2 == 1) {
                $sheet->getStyle('A' . $j . ':' . $sheet->getHighestDataColumn() . $j)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('e5e5e5');
            }
        }

        $sheet->getStyle('A1:' . $sheet->getHighestDataColumn() . $sheet->getHighestDataRow())->getBorders()->getVertical()->setBorderStyle(Border::BORDER_THICK);
        $sheet->getStyle('A1:' . $sheet->getHighestDataColumn() . $sheet->getHighestDataRow())->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK);
        $sheet->getStyle('A1:' . $sheet->getHighestDataColumn() . $sheet->getHighestDataRow())->getBorders()->getHorizontal()->setBorderStyle(Border::BORDER_THIN);

        for ($i = 'A'; $i !=  $sheet->getHighestColumn(); $i++) {
            $sheet->getColumnDimension($i)->setAutoSize(TRUE);
        }

            return $sheet;

    }

    private function remplirFeuilleListeClients($clients, Worksheet $sheet, $typeFichier)
    {

        setlocale(LC_ALL, 'fr_FR');
        $j = $sheet->getHighestDataRow() + 1;
        foreach ($clients as $client) {
            $sheet->setCellValue('A' . $j, $client->getNom());
            $sheet->setCellValue('B' . $j, $client->getPrenom());
            $sheet->setCellValue('C' . $j, $client->getEmail());
            if ($typeFichier == 'pdf') {
                $sheet->setCellValue('D' . $j, $client->getCommandes()->first()->getDateCreation()->format('d-m-Y'));
                $sheet->setCellValue('E' . $j, $client->getCommandes()->last()->getDateCreation()->format('d-m-Y'));
            } else {
                $sheet->setCellValue('D' . $j, strftime('%A %d %B %Y', $client->getCommandes()->first()->getDateCreation()->getTimeStamp()));
                $sheet->setCellValue('E' . $j, strftime('%A %d %B %Y', $client->getCommandes()->last()->getDateCreation()->getTimeStamp()));
            }
            $sheet->setCellValue('F' . $j, count($client->getCommandes()));
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


            $spreadsheet->getActiveSheet()->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
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
