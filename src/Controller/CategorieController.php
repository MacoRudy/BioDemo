<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class CategorieController extends AbstractController
{
    /**
     * @Route("/categorie", name="categorie")
     */
    public function categorie()
    {
        $categorie = $this->getDoctrine()
            ->getRepository(Categorie::class)
            ->findAll();

        if (!$categorie) {
            throw $this->createNotFoundException(
                'Aucune categorie trouvée'
            );
        }

        return $this->render('categorie/categorie.html.twig',
            ['categorie' => $categorie]
        );
    }

    /**
     * @Route("/categorie/add", name="add_categorie")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return RedirectResponse|Response
     */
    public function add(Request $request, EntityManagerInterface $em)
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieFormType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!$categorie->getCatParent()) {
                $categorie->setNom(strtoupper($categorie->getNom()));
            }
            $em->persist($categorie);
            $em->flush();
            $this->addFlash('success', 'Catégorie ajoutée avec succès');

            return $this->redirectToRoute("categorie");
        }

        return $this->render('categorie/creationCategorie.html.twig', [
                'categorieForm' => $form->createView()]
        );
    }

    /**
     * @Route("/categorie/edit/{id}", name="edit_categorie", requirements={"id":"\d+"})
     * @param $id
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return RedirectResponse|Response
     */
    public function edit($id, Request $request, EntityManagerInterface $em)
    {
        $categorieRepo = $this->getDoctrine()->getRepository(Categorie::class);
        $categorie = $categorieRepo->find($id);
        $form = $this->createForm(CategorieFormType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($categorie);
            $em->flush();

            $this->addFlash('success', 'Catégorie mise à jour avec succès');
            return $this->redirectToRoute("categorie");
        }

        return $this->render('categorie/edit.html.twig', [
                'categorieForm' => $form->createView(),
                'id' => $id]
        );
    }


    /**
     * @Route("/categorie/delete/{id}", name="delete_categorie", requirements={"id":"\d+"})
     * @param $id
     * @param EntityManagerInterface $em
     * @return RedirectResponse
     */
    public function delete($id, EntityManagerInterface $em)
    {
        $categorieRepo = $this->getDoctrine()->getRepository(Categorie::class);
        $categorie = $categorieRepo->find($id);

        if ($categorie != null) {
            $em->remove($categorie);
            $em->flush();
        }

        $this->addFlash('success', 'Catégorie supprimée avec succès');
        return $this->redirectToRoute("categorie");
    }


}

