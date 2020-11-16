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
        if ($request->request->has('mois')) {
            $chart = $chart->commandesParMoisBarre($annee);
            return $this->render('graphique/barGraph.html.twig', ['chart' => $chart]);
        } else if ($request->request->has('semaine')) {
            $chart = $chart->commandesParSemaineBarre($annee);
            return $this->render('graphique/barGraph.html.twig', ['chart' => $chart]);
        } else {
            return $this->render('graphique/3dPieChart.html.twig', ['annee' => $annee]);
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
        $annee = $request->get('annee');
        $semaine = $request->get('semaine');
        if ($semaine != -1) {
            $arrayToDataTable = $chart->ventesProducteurCamembert($annee, $semaine);
        }else {
            $arrayToDataTable = $chart->commandesParMoisCamembert($annee);
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
        $annee = $request->request->get('annee');
        $semaine = $request->request->get('semaine');
        if ($request->request->has('producteur-barre')) {
            $chart = $chart->ventesParProducteur($annee, $semaine);
            return $this->render('graphique/barGraph.html.twig', ['chart' => $chart]);
        } else {
            return $this->render('graphique/3dPieChart.html.twig', ['annee' => $annee, 'semaine'=>$semaine]);
        }
    }




}
