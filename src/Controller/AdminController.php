<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\TripStatus;
use App\Form\UploadCsvType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Tests\Encoder\PasswordEncoder;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $participants = $this->getDoctrine()->getRepository(Participant::class)->findAll();
        $csvForm = $this->createForm(UploadCsvType::class);
        $csvForm->handleRequest($request);

        if ($csvForm->isSubmitted() && $csvForm->isValid() ) {
            $csvFile = $csvForm['brochure']->getData();

            if ($csvFile) {
                $originalFilename = pathinfo($csvFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$csvFile->guessExtension();

                try {
                    $csvFile->move(
                        $this->getParameter('csv_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
            }
        }


        return $this->render('admin/index.html.twig', [
            'participants' => $participants,
            'csv_form' => $csvForm
        ]);
    }

    /**
     * @Route("/admin/action/{participantId}/{action}", name="admin_action")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function action(Request $request, EntityManagerInterface $em,UserPasswordEncoderInterface $passwordEncoder) {
        $participant = $this->getDoctrine()->getRepository(Participant::class)->find($request->attributes->get('participantId'));

        switch ($request->attributes->get('action')) {
            case 'ban':
                $participant->setIsActive(false);
                break;

            case 'unban':
                $participant->setIsActive(true);
                break;

            case 'delete':
                //hasher les données de l'utilisateur
                $participant->setIsActive(false);
                $participant->setImageUrl('deleted');
                $participant->setRoles([]);
                $participant->setPhoneNumber("");
                $participant->setPassword($passwordEncoder->encodePassword($participant, $participant->getPassword()));
                $participant->setEmail($passwordEncoder->encodePassword($participant, $participant->getEmail()));
                $participant->setLastName("");
                $participant->setFirstName("");
                $participant->setIsDeleted(1);

                //annuler les sorties organiser par l'utilisateur
                foreach($participant->getOrganizedTrips() as $trip) {
                    $trip->setStatus($this->getDoctrine()->getRepository(TripStatus::class)->find(TripStatus::CANCELED));

                    //désinscrire les utilisateurs des sorties de l'utilisateur
                    foreach($trip->getParticipants() as $current_participant) {
                        $trip->removeParticipant($current_participant);
                    }
                }

                break;

            default:
                break;
        }

        $em->persist($participant);
        $em->flush();

        return $this->redirectToRoute('admin');

    }

}
