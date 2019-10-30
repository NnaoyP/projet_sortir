<?php


namespace App\Command;

use App\Entity\Trip;
use App\Entity\TripStatus;
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


        $output->writeln('Script exécuté le '. $today->format('Y-m-d H:i:s'));
        $output->writeln('');

        foreach ($trips as $trip) {

            //si la date de la sortie est antérieur à aujourd'hui alors on fait du traitement
            if ($trip->getStartDate() < $today) {
                $output->writeln('Id de la sortie: ' . $trip->getId() .' | Date du début de la sortie: ' . $trip->getStartDate()->format('Y-m-d H:i:s') . ' | Ancienneté: ' . $today->diff($trip->getStartDate())->format('%d'));

                // on ferme la sortie après 1 mois d'existence
                if ($trip->getStartDate()->diff($today)->format('%d') > 30) {
                    $trip->setStatus(TripStatus::CLOSED);
                }
                //sinon on met juste l'état de la sortie à terminée
                else {
                    $trip->setStatus(TripStatus::DONE);
                }

                $this->entityManager->persist($trip);
            }
        }

        $this->entityManager->flush();

        $output->writeln('Script terminé!');
    }
}