<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, EntityManagerInterface $em) {
        $participant = $this->getUser();

        if ($participant == null) {
            throw $this->createNotFoundException('Utilisateur inconnu');
        }

        $participantForm = $this->createForm(ParticipantType::class, $participant);
        $participantForm->handleRequest($request);
        if ($participantForm->isSubmitted() && $participantForm->isValid()) {
            $em->persist($participant);
            $em->flush();
            return $this->redirectToRoute("profile_detail",
                ['id' => $participant->getId()]);
        }

        return $this->render("profile/edit.html.twig", [
            "participant_form" => $participantForm->createView()
        ]);
    }
}
