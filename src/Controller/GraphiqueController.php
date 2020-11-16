<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Detail;
use App\Service\Chart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GraphiqueController extends AbstractController
{
    /**
     * @Route("/graphique", name="graphique")
     * @return Response
     */
    public function index()
    {
        $annees = $this->getDoctrine()->getRepository(Commande::class)->findAnneesAvecCommande();
        return $this->render('graphique/index.html.twig', ['annees' => $annees]);
    }


    /**
     * @Route("/graphique/commandes/date", name="commandes_par_date_graphique", methods={"POST"})
     * @param Chart $chart
     * @param Request $request
     * @return Response
     */
    public function commandesParDate(Chart $chart, Request $request)
    {
        $annee = $request->request->get('annee');
        $type = 'date';
        if ($request->request->has('mois-barre')) {
            $chart = $chart->commandesParMoisBarre($annee);
            return $this->render('graphique/barGraph.html.twig', ['chart' => $chart]);
        } else if ($request->request->has('semaine-barre')) {
            $chart = $chart->commandesParSemaineBarre($annee);
            return $this->render('graphique/barGraph.html.twig', ['chart' => $chart]);
        } else {
            return $this->render('graphique/3dPieChart.html.twig', ['annee' => $annee, 'type' => $type]);
        }
    }


    /**
     * @Route("/graphique/commandes/date/3d", name="commandes_3d_graphique", methods={"POST"})
     * @param Request $request
     * @param Chart $chart
     * @return Response
     */
    public function Ajax3DPieChartDate(Request $request, Chart $chart)
    {
        $annee = $request->request->get('annee');
        $semaine = $request->request->get('semaine');
        $type = $request->request->get('type');

        if ($type == 'producteurs') {
            $arrayToDataTable = $chart->ventesProducteurCamembert($annee, $semaine);
        } elseif ($type == 'date'){
            $arrayToDataTable = $chart->commandesParMoisCamembert($annee);
        } elseif ($type == 'dépôts') {
            $arrayToDataTable = $chart->ventesDepotCamembert($annee, $semaine);
        }
        return new JsonResponse($arrayToDataTable);
    }


    /**
     * @Route("/graphique/ventes/producteur", name="ventes_par_producteur_graphique", methods={"POST"})
     * @param Chart $chart
     * @param Request $request
     * @return Response
     */
    public function VentesParProducteur(Chart $chart, Request $request)
    {
        $annee = $request->request->get('annee1');
        $semaine = $request->request->get('semaine1');
        $type = 'producteurs';
        if ($request->request->has('producteur-barre')) {
            $chart = $chart->ventesParProducteur($annee, $semaine);
            return $this->render('graphique/barGraph.html.twig', ['chart' => $chart]);
        } else {
            return $this->render('graphique/3dPieChart.html.twig', ['annee' => $annee, 'semaine' => $semaine, 'type' => $type]);
        }
    }


    /**
     * @Route("/graphique/ventes/depot", name="ventes_par_depot_graphique", methods={"POST"})
     * @param Chart $chart
     * @param Request $request
     * @return Response
     */
    public function VentesParDepot(Chart $chart, Request $request)
    {
        $annee = $request->request->get('annee2');
        $semaine = $request->request->get('semaine2');
        $type = 'dépôts';
        if ($request->request->has('depot-barre')) {
            $chart = $chart->ventesParDepot($annee, $semaine);
            return $this->render('graphique/barGraph.html.twig', ['chart' => $chart]);
        } else {
            return $this->render('graphique/3dPieChart.html.twig', ['annee' => $annee, 'semaine' => $semaine, 'type' => $type]);
        }
    }

}
