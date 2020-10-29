<?php

namespace App\Controller;

use App\Entity\Producteur;
use App\Entity\User;
use App\Form\ProducteurFormType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/admin")
 */
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
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return RedirectResponse|Response
     */
    public function add(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        if ($this->isGranted('ROLE_ADMIN')) {

            $producteur = new Producteur();
            $form = $this->createForm(ProducteurFormType::class, $producteur);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $user = new User();
                $user->setNom($form->get("nom")->getData());
                $user->setPrenom($form->get("prenom")->getData());
                $user->setAdresse($form->get("adresse")->getData());
                $user->setCodePostal($form->get("codePostal")->getData());
                $user->setRoles(['ROLE_PRODUCTEUR']);
                $user->setDateInscription(new DateTime('now'));
                $user->setEmail($form->get("email")->getData());
                $user->setVille($form->get("ville")->getData());
                $user->setTelephone($form->get("telephone")->getData());
                $user->setValide(1);
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                $em->persist($user);
                $producteur->setUser($user);
                $em->persist($producteur);
                $em->flush();

                return $this->redirectToRoute("producteur");
            }

            return $this->render('producteur/creationProducteur.html.twig', [
                    'producteurForm' => $form->createView()]
            );

        } else {
            $this->addFlash("warning", "Droit d'administrateur requis !");
            return $this->redirectToRoute("home");
        }
    }

    /**
     * @Route("/producteur/detail/{id}", name="detail_producteur", requirements={"id":"\d+"})
     * @param $id
     * @return Response
     */
    public function detail($id)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        $detail = $user->getProducteur();

        return $this->render('producteur/detail.html.twig', [
            "detail" => $detail
        ]);
    }

    /**
     * @Route("/producteur/edit/{id}", name="edit_producteur", requirements={"id":"\d+"})
     * @param $id
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return RedirectResponse|Response
     */
    public function edit($id, Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {

        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        $producteur = $user->getProducteur();

        if ($this->isGranted('ROLE_ADMIN') or ($this->isGranted('ROLE_PRODUCTEUR') and $this->getUser() == $user)) {

        $form = $this->createForm(ProducteurFormType::class, $producteur);
        // ajout du prenom nom present dans l'entoté producteur
        $form->get('prenom')->setData($user->getPrenom());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setNom($form->get("nom")->getData());
            $user->setPrenom($form->get("prenom")->getData());
            $user->setAdresse($form->get("adresse")->getData());
            $user->setCodePostal($form->get("codePostal")->getData());
            $user->setEmail($form->get("email")->getData());
            $user->setVille($form->get("ville")->getData());
            $user->setTelephone($form->get("telephone")->getData());
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $em->persist($user);
            $em->persist($producteur);
            $em->flush();

            $this->addFlash("success", "Mise à jour effectuée avec succés");
            return $this->redirectToRoute("producteur");
        }

        return $this->render('producteur/edit.html.twig', [
                'producteurForm' => $form->createView(),
                'id' => $id]
        );
    } else {
        $this->addFlash("warning", "Droit d'administrateur requis !");
        return $this->redirectToRoute("home");
    }
    }

    /**
     * @Route ("/producteur/delete/{id}", name="delete_producteur", requirements={"id":"\d+"})
     * @param $id
     * @param EntityManagerInterface $em
     * @return RedirectResponse|Response
     */
    public function delete($id, EntityManagerInterface $em)
    {
        if ($this->isGranted('ROLE_ADMIN')) {

            $user = $this->getDoctrine()->getRepository(User::class)->find($id);
            $producteur = $user->getProducteur();

            if ($producteur != null) {
                $em->remove($producteur);
                $em->flush();
            }

            $this->addFlash("success", "Suppression effectuée avec succés");
            return $this->redirectToRoute("producteur");
        } else {
            $this->addFlash("warning", "Droit d'administrateur requis !");
            return $this->redirectToRoute("home");
        }
    }
}