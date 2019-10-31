<?php


namespace App\Command;

use App\Entity\Trip;
use App\Entity\TripStatus;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TripStatusManagement extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:manage:trip:status';

    private $entityManager;

    function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct(null);

        $this->entityManager = $entityManager;
    }

    protected function configure() {
        $this->setDescription('Manage the status trip.')
            ->setHelp('This command allows you to manage status trip automatically');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     * @var Trip[] $trips
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $today = (new \DateTime())->setTimezone(new \DateTimeZone('Europe/Paris'));
        $trips = $this->entityManager->getRepository(Trip::class)->findAllNotClosed();

        $tripsStatusDone = $this->entityManager->getRepository(TripStatus::class)->find(TripStatus::DONE);
        $tripsStatusClosed = $this->entityManager->getRepository(TripStatus::class)->find(TripStatus::CLOSED);
        $tripsStatusRunning = $this->entityManager->getRepository(TripStatus::class)->find(TripStatus::RUNNING);
        $tripsStatusFinished = $this->entityManager->getRepository(TripStatus::class)->find(TripStatus::FINISHED);

        $output->writeln('Script exécuté le '. $today->format('Y-m-d H:i:s'));
        $output->writeln('');

        foreach ($trips as $trip) {
            $tripDate = (new \DateTime($trip->getStartDate()->format('Y-m-d H:i:s')));
            $tripDeadLineDate = (new \DateTime($trip->getDeadLineDate()->format('Y-m-d H:i:s')));
            $minutesToAdd = $trip->getDuration();
            $tripEndDate = (new \DateTime($tripDate->format('Y-m-d H:i:s')))->modify("+{$minutesToAdd} minutes");

            $output->writeln($trip->getDuration());
            $output->write('Id de la sortie: ' . $trip->getId() .' | Date du début de la sortie: ' . $tripDate->format('Y-m-d H:i:s') . ' | Date de fin de la sortie: '. $tripEndDate->format('Y-m-d H:i:s') .' | Ancienneté: ' . $today->diff($tripDate)->format('%a') . ' | Date fin d\'inscription: ' . $tripDeadLineDate->format('Y-m-d H:i:s'));

            if ($trip->getStatus()->getId() != TripStatus::CANCELED && $trip->getStatus()->getId() != TripStatus::CREATION) {
                //si la date de la sortie est antérieur à aujourd'hui alors on fait du traitement (DONE ou CLOSED)

                //clôturé
                if (strtotime($tripDeadLineDate->format('Y-m-d H:i:s')) <= strtotime($today->format('Y-m-d H:i:s'))) {
                    $trip->setStatus($tripsStatusDone);
                    $output->write(' | DONE');
                }

                //terminée
                if (strtotime($tripDate->format('Y-m-d H:i:s')) <= strtotime($today->format('Y-m-d H:i:s'))) {
                    $trip->setStatus($tripsStatusFinished);
                    $output->write(' | Finished');
                }
                // on ferme la sortie après 1 mois d'existence
                if ($tripDate->diff($today)->format('%a') > 30) {
                    $trip->setStatus($tripsStatusClosed);
                    $output->write(' | CLOSED');
                }

                //(RUNNING)
                if (strtotime($today->format('Y-m-d H:i:s')) >= strtotime($tripDate->format('Y-m-d H:i:s')) && strtotime($today->format('Y-m-d H:i:s')) <= strtotime($tripEndDate->format('Y-m-d H:i:s'))) {
                    $trip->setStatus($tripsStatusRunning);
                    $output->write(' | EN COURS');
                }

            }


            $output->writeln('');
            $this->entityManager->persist($trip);
        }

        $this->entityManager->flush();

        $output->writeln('Script terminé!');
    }
}