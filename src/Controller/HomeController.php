<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Service\Chart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param Chart $chart
     * @return Response
     */
    public function index(Chart $chart)
    {
        $semaine = date('W');
        $annee = date('Y');
        $commande = [];
       while (empty($commande)) {
           $commande = $commande = $this->getDoctrine()->getRepository(Commande::class)->findBy(['annee'=>$annee, 'semaine'=>$semaine]);
           $semaine--;
       }


        $type = 'dépôts';
        $chartProd = $chart->ventesParProducteur($annee, $semaine);
        $chartCat = $chart->ventesParCategorie($annee,$semaine);

        return $this->render('home/home.html.twig', ['annee' => $annee, 'semaine' => $semaine, 'type' => $type , 'chartProd' => $chartProd, 'chartCat' => $chartCat]);
    }
}
