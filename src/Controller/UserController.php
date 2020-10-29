<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
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
class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user")
     */
    public function user()
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findBy([],['nom'=> 'ASC']);


        return $this->render('user/user.html.twig',
            ['user' => $user]
        );
    }

    /**
     * @Route("/user/add", name="creation_user")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return RedirectResponse|Response
     */
    public function add(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setDateInscription(new DateTime('now'));
            $this->addFlash('success', 'Profil crée avec succès');

            $em->persist($user);
            $em->flush();
            $this->addFlash("success", "Utilisateur ajouté avec succès");
            return $this->redirectToRoute("user");
        }

        return $this->render('user/creationUser.html.twig', [
                'registrationForm' => $form->createView()]
        );
    }

    /**
     * @Route("/user/detail/{id}", name="detail_user", requirements={"id":"\d+"})
     * @param $id
     * @return Response
     */
    public function detail($id)
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $detail = $userRepo->find($id);
// si l'utilisateur a modifié est un Producteur
        if ($detail->getProducteur() != null) {
            return $this->redirectToRoute('detail_producteur', ['id' => $detail->getId()]);
        }

        return $this->render('user/detail.html.twig', [
            "detail" => $detail
        ]);


    }

    /**
     * @Route("/user/edit/{id}", name="edit_user", requirements={"id":"\d+"})
     * @param $id
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return RedirectResponse|Response
     */
    public function edit($id, Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {

        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->find($id);

        // si l'utilisateur a modifié est un Producteur
        if ($user->getProducteur() != null) {

            return $this->redirectToRoute('edit_producteur', ['id' => $user->getId()]);
        }

        // Verification de l'autorisation de modification
        if ($this->isGranted("ROLE_ADMIN") or ($this->isGranted('ROLE_PRODUCTEUR') and $this->getUser() == $user)) {

            $form = $this->createForm(RegistrationFormType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                $em->persist($user);
                $em->flush();
                $this->addFlash("success", "Utilisateur modifié avec succès");

                return $this->redirectToRoute("user");

            }

            return $this->render('user/edit.html.twig', [
                    'registrationForm' => $form->createView(),
                    'id' => $id]
            );
            // si l'utilisateur a modifier a un ROLE_USER
        } else {
            $this->addFlash("warning", "Vous n'avez pas le droit de modifier cet utilisateur");
            return $this->redirectToRoute("home");
        }
    }


    /**
     * @Route ("/user/delete/{id}", name="delete_user", requirements={"id":"\d+"})
     * @param $id
     * @param EntityManagerInterface $em
     * @return RedirectResponse|Response
     */
    public
    function delete($id, EntityManagerInterface $em)
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepo->find($id);

        if ($user != null) {
            $em->remove($user);
            $em->flush();
        }
        return $this->redirectToRoute("user");
    }
}
