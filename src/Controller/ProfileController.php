<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Form\PasswordType;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Cast\Object_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile/detail/{id}", name="profile_detail")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detail($id)
    {
        $participantRepository=$this->getDoctrine()->getRepository(Participant::class);
        $participant=$participantRepository->find($id);

        if ($participant==null) {
            throw $this->createNotFoundException("Utilisateur inconnu");
        }

        return $this->render("profile/detail.html.twig", [
            "participant"=>$participant
        ]);
    }

    /**
     * @Route("/profile/edit", name="profil_edit")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder) {
        $participant = $this->getUser();

        if ($participant == null) {
            throw $this->createNotFoundException('Utilisateur inconnu');
        }

        $participantForm = $this->createForm(RegistrationFormType::class, $participant);
        $participantForm->remove('plainPassword');
        $participantForm->remove('isActive');

        $passwordForm = $this->createForm(PasswordType::class, $participant);

        $participantForm->handleRequest($request);
        $passwordForm->handleRequest($request);

        if ($participantForm->isSubmitted() && $participantForm->isValid()) {
            $em->persist($participant);
            $em->flush();
            return $this->redirectToRoute("profile_detail",
                ['id' => $participant->getId()]);
        }

        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $participant->setPassword(
                $passwordEncoder->encodePassword(
                    $participant,
                    $passwordForm->get('password')->getData()
                )
            );
            $em->persist($participant);
            $em->flush();
            return $this->redirectToRoute("profile_detail",
                ['id' => $participant->getId()]);
        }


        return $this->render("profile/edit.html.twig", [
            "participant_form" => $participantForm->createView(),
            "password_form" => $passwordForm->createView()
        ]);
    }
}
