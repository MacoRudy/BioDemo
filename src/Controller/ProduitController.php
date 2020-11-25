<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Producteur;
use App\Entity\Produit;
use App\Entity\User;
use App\Form\ProduitFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class ProduitController extends AbstractController
{
    /**
     * @Route("/produit", name="produit")
     */
    public function produit()
    {
        $producteur = $this->getDoctrine()
            ->getRepository(Producteur::class)
            ->findAll();

        $categorie = $this->getDoctrine()
            ->getRepository(Categorie::class)
            ->findSousCategorie();


        $produit = $this->getDoctrine()
            ->getRepository(Produit::class)
            ->findProduitByProducteur();

        return $this->render('produit/produit.html.twig',
            ['produit' => $produit, 'producteur' => $producteur, 'categorie' => $categorie]
        );
    }


    /**
     * @Route("/produit/add", name="creation_produit")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return RedirectResponse|Response
     */
    public function add(Request $request, EntityManagerInterface $em)
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitFormType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $produit->setReference(strtoupper(substr($form->get('nom')->getData(), 0, 3)));
            $em->persist($produit);
            $em->flush();

            $this->addFlash('success', 'Produit crée avec succès');
            return $this->redirectToRoute("produit");
        }

        return $this->render('produit/creationProduit.html.twig', [
                'produitForm' => $form->createView()]
        );
    }

    /**
     * @Route("/produit/detail/{id}", name="detail_produit", requirements={"id":"\d+"})
     * @param $id
     * @return Response
     */
    public function detail($id)
    {
        $produitRepo = $this->getDoctrine()->getRepository(Produit::class);
        $detail = $produitRepo->find($id);

        return $this->render('produit/detail.html.twig', [
            "detail" => $detail
        ]);
    }

    /**
     * @Route("/produit/edit/{id}", name="edit_produit", requirements={"id":"\d+"})
     * @param $id
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return RedirectResponse|Response
     */
    public function edit($id, Request $request, EntityManagerInterface $em)
    {
        $produitRepo = $this->getDoctrine()->getRepository(Produit::class);
        $produit = $produitRepo->find($id);
        $form = $this->createForm(ProduitFormType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($produit);
            $em->flush();

            $this->addFlash('success', 'Produit mis à jour avec succès');
            return $this->redirectToRoute("produit");
        }

        return $this->render('produit/edit.html.twig', [
                'produitForm' => $form->createView(),
                'id' => $id]
        );
    }

    /**
     * @Route ("/produit/delete/{id}", name="delete_produit", requirements={"id":"\d+"})
     * @param $id
     * @param EntityManagerInterface $em
     * @return RedirectResponse|Response
     */
    public function delete($id, EntityManagerInterface $em)
    {
        $produitRepo = $this->getDoctrine()->getRepository(Produit::class);
        $produit = $produitRepo->find($id);

        if ($produit != null) {
            $em->remove($produit);
            $em->flush();
            $this->addFlash('success', 'Produit supprimer avec succès');

        }
        return $this->redirectToRoute("produit");
    }


    /**
     * @Route("/produit/tri/producteur", name="tri_produit_producteur")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function listeParProducteur(Request $request)
    {

        $idProd = $request->query->get('productor');

        if ($idProd != 0) {
            $producteur = $this->getDoctrine()
                ->getRepository(Producteur::class)
                ->findAll();


            $productor = array_filter($producteur, function ($item) use ($idProd) {
                return $item->getId() == $idProd;
            });


            $categorie = $this->getDoctrine()
                ->getRepository(Categorie::class)
                ->findSousCategorie();

            $produit = $this->getDoctrine()
                ->getRepository(Produit::class)
                ->findBy(array('producteur' => $productor));

            return $this->render('produit/produit.html.twig',
                ['produit' => $produit, 'producteur' => $producteur, 'categorie' => $categorie]
            );
        } else {
            return $this->redirectToRoute("produit");
        }
    }


    /**
     * @Route("/produit/tri/categorie", name="tri_produit_categorie")
     * @param Request $request
     * @return RedirectResponse|Response
     */

    public function listeParCategorie(Request $request)
    {

        $idCat = $request->query->get('categorie');

        if ($idCat != 0) {
            $producteur = $this->getDoctrine()
                ->getRepository(Producteur::class)
                ->findAll();

            $categorie = $this->getDoctrine()
                ->getRepository(Categorie::class)
                ->findSousCategorie();

            $cat = array_filter($categorie, function ($item) use ($idCat) {
                return $item->getId() == $idCat;
            });

            $produit = $this->getDoctrine()
                ->getRepository(Produit::class)
                ->findBy(array('categorie' => $cat));

            return $this->render('produit/produit.html.twig',
                ['produit' => $produit, 'producteur' => $producteur, 'categorie' => $categorie]
            );
        } else {
            return $this->redirectToRoute("produit");
        }
    }


    /**
     * @Route("/produit/categorie", name="produit_categorie")
     * @return RedirectResponse|Response
     */
    public function trierParCategorie()
    {

        $producteur = $this->getDoctrine()
            ->getRepository(Producteur::class)
            ->findAll();

        $categorie = $this->getDoctrine()
            ->getRepository(Categorie::class)
            ->findSousCategorie();


        $produit = $this->getDoctrine()
            ->getRepository(Produit::class)
            ->findProduitByCategorie();

        return $this->render('produit/produit.html.twig',
            ['produit' => $produit, 'producteur' => $producteur, 'categorie' => $categorie]
        );
    }

    /**
     * @Route("/produit/vosproduits", name="vos_produits")
     * @return RedirectResponse|Response
     */
    public function vosProduits()
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $this->getUser()->getUsername()]);
        $produit = $this->getDoctrine()->getRepository(Produit::class)->findBy(['producteur' => $user->getProducteur()]);

        return $this->render('produit/vosProduits.html.twig',
            ['produit' => $produit]
        );
    }


}