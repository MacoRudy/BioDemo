<?php

namespace App\Controller;

use App\Entity\Detail;
use App\Form\DetailFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class DetailController extends AbstractController
{
    /**
     * @Route("/detail", name="detail")
     */
    public function index()
    {
        return $this->render('detail/index.html.twig', [
            'controller_name' => 'DetailController',
        ]);
    }


    /**
     * @Route("/detail/add", name="creation_detail")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return RedirectResponse|Response
     */
    public function add(Request $request, EntityManagerInterface $em)
    {
        $detail = new Detail();
        $form = $this->createForm(DetailFormType::class, $detail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Pour l'ajout au montant de la commande
            $prix = $form->get('produit')->getData()->getPrix();
            $quantite = $form->get('quantite')->getData();
            $commande = $detail->getCommande();
            $commande->setMontant($commande->getMontant() + ($prix * $quantite));

            // Ajout des données en recuperant les valeurs du produit ajouté
            $detail->setPrix($form->get('produit')->getData()->getPrix());
            $detail->setProducteur($form->get('produit')->getData()->getProducteur());

            // Ajout du detail et mise a jour de la commande correspondante
            $em->persist($commande);
            $em->persist($detail);

            $em->flush();

            $this->addFlash('success', 'Produit ajouté a la commande avec succès');
            return $this->redirectToRoute("detail");
        }

        return $this->render('detail/creationDetail.html.twig', [
                'detailForm' => $form->createView()]
        );
    }

    /**
     * @Route("/detail/detail", name="detail_detail")
     */
    public function detail()
    {
        return $this->render('detail/detail.html.twig', [
            'controller_name' => 'DetailController',
        ]);
    }

}
