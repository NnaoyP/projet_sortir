<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\TripStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Tests\Encoder\PasswordEncoder;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index( )
    {
        $participants = $this->getDoctrine()->getRepository(Participant::class)->findAll();

        return $this->render('admin/index.html.twig', [
            'participants' => $participants
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

            case 'deban':
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
