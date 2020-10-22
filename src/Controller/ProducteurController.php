<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Producteur;
use App\Form\ProducteurFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProducteurController extends AbstractController
{
    /**
     * @Route("/producteur", name="producteur")
     */
    public function producteur()
    {
        $producteur = $this->getDoctrine()
            ->getRepository(Producteur::class)
            ->findAll();

        return $this->render('producteur/producteur.html.twig',
            ['producteur' => $producteur]
        );
    }

    /**
     * @Route("/producteur/add", name="creation_producteur")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return RedirectResponse|Response
     */
    public function add(Request $request, EntityManagerInterface $em)
    {
        $producteur = new Producteur();
        $form = $this->createForm(ProducteurFormType::class, $producteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($producteur);
            $em->flush();

            return $this->redirectToRoute("producteur");
        }

        return $this->render('producteur/creationProducteur.html.twig', [
                'producteurForm' => $form->createView()]
        );
    }

    /**
     * @Route("/producteur/detail/{id}", name="detail_producteur", requirements={"id":"\d+"})
     * @param $id
     * @return Response
     */
    public function detail($id)
    {
        $producteurRepo = $this->getDoctrine()->getRepository(Producteur::class);
        $detail = $producteurRepo->find($id);

        return $this->render('producteur/detail.html.twig', [
            "detail" => $detail
        ]);
    }

    /**
     * @Route("/producteur/edit/{id}", name="edit_producteur", requirements={"id":"\d+"})
     * @param $id
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return RedirectResponse|Response
     */
    public function edit($id, Request $request, EntityManagerInterface $em)
    {
        $producteurRepo = $this->getDoctrine()->getRepository(Producteur::class);
        $producteur = $producteurRepo->find($id);
        $form = $this->createForm(ProducteurFormType::class, $producteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($producteur);
            $em->flush();

            return $this->redirectToRoute("producteur");
        }

        return $this->render('producteur/edit.html.twig', [
                'producteurForm' => $form->createView(),
                'id' => $id]
        );
    }

    /**
     * @Route ("/producteur/delete/{id}", name="delete_producteur", requirements={"id":"\d+"})
     * @param $id
     * @param EntityManagerInterface $em
     * @return RedirectResponse|Response
     */
    public function delete($id, EntityManagerInterface $em)
    {
        $producteurRepo = $this->getDoctrine()->getRepository(Producteur::class);
        $producteur = $producteurRepo->find($id);

        if ($producteur != null) {
            $em->remove($producteur);
            $em->flush();
        }
        return $this->redirectToRoute("producteur");
    }
}