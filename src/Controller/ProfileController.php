<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Form\PasswordType;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Cast\Object_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
     * @throws \ErrorException
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
            /** @var UploadedFile $imageFile */

            $imageFile = $participantForm['imageUrl']->getData();
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                $imageFile->move(
                    $this->getParameter('image_profile_directory'),
                    $newFilename
                );

            $em->persist($participant);
            $em->flush();
            return $this->redirectToRoute("profile_detail",
                ['id' => $participant->getId()]);
        }

        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $oldPassword = $participant->setPassword($passwordEncoder->encodePassword($participant, $passwordForm->get('password')->getData()));

            if ($oldPassword == $participant->getPassword()) {
                $participant->setPassword(
                    $passwordEncoder->encodePassword(
                        $participant,
                        $passwordForm->get('password')->getData()
                    )
                );
            } else {
                throw new \ErrorException("L'ancien mot de passe ne correspond pas.");
            }


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
