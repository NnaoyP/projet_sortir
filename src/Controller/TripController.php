<?php

namespace App\Controller;

use App\CustomServices\EmailSender;
use App\Entity\Participant;
use App\Entity\Trip;
use App\Entity\TripPlace;
use App\Entity\TripStatus;
use App\Form\TripType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

class TripController extends AbstractController
{

    /**
     * @Route("/trip", name="trip", methods={"GET"})
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function searchTripWithFilter(Request $request, PaginatorInterface $paginator)
    {

        // récupération des sorties publiées
        $parameterBag = $request->query;
        if ($this->getUser()) {
            $parameterBag->add(['userId' => $this->getUser()->getId()]);
         }

        // récupération du querybuild pour le paginator
        $queryBuilder = $this->getDoctrine()->getRepository(Trip::class)->findByFilter($parameterBag);

        $pagination = $paginator->paginate(
            $queryBuilder, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        //$trips =
        $places = $this->getDoctrine()->getRepository(TripPlace::class)->findAll();

        return $this->render("trip/index.html.twig", [
            //'trips' => $trips,
            'pagination' => $pagination,
            'places' => $places
        ]);
    }

    /**
     * @Route("/trip/add/", name="trip_add")
     * @method Participant getUser()
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param \Swift_Mailer $mailer
     * @return Response
     */
    public function addTrip(Request $request, EntityManagerInterface $em, \Swift_Mailer $mailer) {
        // création de la sortie a mapper avec le formulaire
        $trip = new Trip();

        // récupération des données non séléctionnables par l'utilisateur
        $organizer = $this->getUser();

        $statusOpen = $em->getRepository(TripStatus::class)->find(TripStatus::OPEN);
        $statusCreation = $em->getRepository(TripStatus::class)->find(TripStatus::CREATION);
        $places = $em->getRepository(TripPlace::class)->findAll();

        if ($request->request->get('save') =="") {
            $trip->setStatus($statusCreation);
        } elseif ($request->request->get('publish' == "")) {
            $trip->setStatus($statusOpen);
        }
        $trip->setParticipantArea($organizer->getParticipantArea());
        $trip->setOrganizer($organizer);
        $trip->addParticipant($organizer);

        // création du formulaire et association de la sortie au formulaire
        $tripType = $this->createForm(TripType::class, $trip);

        $tripType->handleRequest($request);
        if ($tripType->isSubmitted() && $tripType->isValid()) {
            $em->persist($trip);
            $em->flush();

            $usersEmail = $this->getDoctrine()->getRepository(Participant::class)->findAllEmail();

            foreach ($usersEmail as $userEmail) {
                try {
                    $message = new \Swift_Message('Une nouvelle sortie ENI est disponible!');
                    $message->setFrom('eni.sortir@gmail.com');
                    $message->setTo($userEmail['email']);
                    $message->setBody($this->renderView('emails/registration.html.twig', ['name' => $trip->getName(),'text/html']));

                    //$mailer->send($message);
                } catch (Exception $e){
                    //le mail ne s'est pas envoyé, créer un log
                }
            }

            return $this->redirectToRoute('trip');
        }

        return $this->render("trip/add.html.twig", [
            'tripType' => $tripType->createView(),
            'tripOrganizer' => $organizer,
            'places' => $places
        ]);
    }

    /**
     * @Route("/trip/add-participant/{tripId}", name="trip_add_participant")
     * @method Participant getUser()
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param \Swift_Mailer $mailer
     * @return RedirectResponse
     * @throws Exception
     */
    public function addParticipant(Request $request, EntityManagerInterface $em, \Swift_Mailer $mailer) {
        $trip = $this->getDoctrine()->getRepository(Trip::class)->find($request->attributes->get('tripId'));
        $today = (new \DateTime())->setTimezone(new \DateTimeZone('Europe/Paris'));

        if (sizeof($trip->getParticipants()) < $trip->getMaxRegistrationNumber()
            and !$trip->getParticipants()->contains($this->getUser())
            and $today < $trip->getDeadlineDate()
            and ($trip->getStatus()->getId() == TripStatus::OPEN)) {
            $trip->addParticipant($this->getUser());

            if (sizeof($trip->getParticipants()) == $trip->getMaxRegistrationNumber()) {
                $trip->setStatus($this->getDoctrine()->getRepository(TripStatus::class)->find(TripStatus::FULL));
            }

            $em->persist($trip);
            $em->flush();

            try {
                $message = new \Swift_Message('Une nouvelle sortie ENI est disponible!');
                $message->setFrom('eni.sortir@gmail.com');
                $message->setTo($trip->getOrganizer()->getEmail());
                $message->setBody($this->renderView('emails/add_participant.html.twig', ['tripName' => $trip->getName(),'text/html']));

                //$mailer->send($message);
            } catch (Exception $e){
                //le mail ne s'est pas envoyé, créer un log
            }
        }

        return $this->redirectToRoute('trip');
    }

    /**
     * @Route("/trip/rem-participant/{tripId}", name ="trip_remove_participant")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param \Swift_Mailer $mailer
     * @return RedirectResponse
     * @throws Exception
     */
    public function removeParticipant(Request $request, EntityManagerInterface $em, \Swift_Mailer $mailer) {
        $trip = $this->getDoctrine()->getRepository(Trip::class)->find($request->attributes->get('tripId'));
        $today = (new \DateTime())->setTimezone(new \DateTimeZone('Europe/Paris'));

        if ($trip->getParticipants()->contains($this->getUser())
            and $trip->getOrganizer() != $this->getUser()
            and ($trip->getStatus()->getId() == TripStatus::OPEN or $trip->getStatus()->getId() == TripStatus::FULL)
            and $today < $trip->getDeadlineDate()) {
            $trip->removeParticipant($this->getUser());

            if ( $trip->getStatus()->getId() == TripStatus::FULL) {
                $trip->setStatus($this->getDoctrine()->getRepository(TripStatus::class)->find(TripStatus::OPEN));
            }

            $em->persist($trip);
            $em->flush();

            try {
                $message = new \Swift_Message('Un utilisateur s\'est désinscrit de votre sortie.');
                $message->setFrom('eni.sortir@gmail.com');
                $message->setTo($trip->getOrganizer()->getEmail());
                $message->setBody($this->renderView('emails/remove_participant.html.twig', ['tripName' => $trip->getName(),'text/html']));

                //$mailer->send($message);
            } catch (Exception $e){
                //le mail ne s'est pas envoyé, créer un log
            }


        }
        return $this->redirectToRoute('trip');
    }

    /**
     * @Route("/trip/edit/{tripId}", name="trip_edit")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function edit(Request $request, EntityManagerInterface $em) {
        $trip = $this->getDoctrine()->getRepository(Trip::class)->find($request->attributes->get('tripId'));

        if ($trip == null) {
            throw new AccessDeniedHttpException();
        }

        if ($this->getUser() != $trip->getOrganizer() or $trip->getStatus()->getId() != TripStatus::CREATION ) {
            throw new AccessDeniedHttpException();
        }
        // récupération du trip

        // création du formulaire et association de la sortie au formulaire
        $tripType = $this->createForm(TripType::class, $trip);
        $places = $em->getRepository(TripPlace::class)->findAll();

        // récupération des données non séléctionnables par l'utilisateur
        $organizer = $this->getUser();

        $tripType->handleRequest($request);
        if ($tripType->isSubmitted() && $tripType->isValid()) {

            $em->persist($trip);
            $em->flush();
        }

        return $this->render("trip/add.html.twig", [
            'tripType' => $tripType->createView(),
            'tripOrganizer' => $organizer,
            'places' => $places,
            'tripStatus' => $trip->getStatus()
        ]);
    }

    /**
     * @Route("/trip/action/{tripId}/{action}", name="trip_actions")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return RedirectResponse
     */
    public function actions(Request $request,EntityManagerInterface $em) {
        $trip = $this->getDoctrine()->getRepository(Trip::class)->find($request->attributes->get('tripId'));

        switch ($request->attributes->get('action')) {
            case 'publish' :
                $status = $this->getDoctrine()->getRepository(TripStatus::class)->find(TripStatus::OPEN);
                $trip->setStatus($status);
                break;
            case 'cancel' :

                $status = $this->getDoctrine()->getRepository(TripStatus::class)->find(TripStatus::CANCELED);
                if ($trip->getStatus()->getId() != TripStatus::CANCELED) {
                    $trip->setStatus($status);
                    foreach ($trip->getParticipants() as $tripParticipant) {
                        $trip->removeParticipant($tripParticipant);
                    }
                }
                break;

            default:
                throw $this->createNotFoundException("L'url demandée n'est pas attribuée.");
        }

        $em->persist($trip);
        $em->flush();

        return $this->redirectToRoute('trip');
    }

    /**
     * @Route("trip/details/{tripId}", name="trip_details")
     * @param Request $request
     * @return Response
     */
    public function details(Request $request) {
        $trip = $this->getDoctrine()->getRepository(Trip::class)->find($request->attributes->get('tripId'));

        return $this->render("trip/details.html.twig", [
            'trip' => $trip
        ]);
    }
}
