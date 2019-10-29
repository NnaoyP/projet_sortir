<?php
namespace App\Command\FakerFixtures;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Bridge\Doctrine\RegistryInterface;

use App\Entity\Trip;
use App\Entity\ParticipantArea;
use App\Entity\TripStatus;
use App\Entity\Participant;
use App\Entity\TripPlace;

class TripFixtureCommand extends Command
{
    protected static $defaultName = 'app:fixtures:trip';

    protected $manager = null;
    protected $doctrine = null;
    protected $faker = null;

    public function __construct(RegistryInterface $doctrine, $name = null)
    {
        parent::__construct($name);
        $this->manager = $doctrine->getManager();
        $this->doctrine = $doctrine;
        $this->faker = \Faker\Factory::create($locale = 'en_US');
    }

    protected function configure()
    {
        $this
        ->setDescription('Load fresh dummy data in trip table')
        ->addArgument('num', InputArgument::OPTIONAL, 'Load how many?', 10)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $num = $input->getArgument('num');

        $io = new SymfonyStyle($input, $output);

        $this->truncateTable();

        $allParticipantAreaEntities = $this->doctrine->getRepository(ParticipantArea::class)->findAll();
        $allTripStatusEntities = $this->doctrine->getRepository(TripStatus::class)->findAll();
        $allParticipantEntities = $this->doctrine->getRepository(Participant::class)->findAll();
        $allTripPlaceEntities = $this->doctrine->getRepository(TripPlace::class)->findAll();

        for($i=0; $i<$num; $i++){
            $trip = new Trip();

            $trip->setName(
                $this->faker->name()
            );
            $trip->setStartDate(
                $this->faker->dateTimeBetween($startDate = "- 3 months", $endDate = "now")
            );
            $trip->setDuration(
                $this->faker->numberBetween($min = 1000, $max = 9000)
            );
            $trip->setDeadlineDate(
                $this->faker->dateTimeBetween($startDate = "- 3 months", $endDate = "now")
            );
            $trip->setMaxRegistrationNumber(
                $this->faker->numberBetween($min = 1000, $max = 9000)
            );
            $trip->setDescription(
                $this->faker->paragraphs($nb = $this->faker->randomDigit, $asText = true)
            );
            $trip->setParticipantArea(
                $this->faker->optional($chancesOfValue = 0.5, $default = null)->randomElement($allParticipantAreaEntities)
            );
            $trip->setStatus(
                $this->faker->optional($chancesOfValue = 0.5, $default = null)->randomElement($allTripStatusEntities)
            );
            $trip->setOrganizer(
                $this->faker->randomElement($allParticipantEntities)
            );
            $trip->setPlace(
                $this->faker->randomElement($allTripPlaceEntities)
            );

            $this->manager->persist($trip);
        }

        $this->manager->flush();
        $io->writeln($num . ' "Trip" loaded!');
        return 0;
    }

    protected function truncateTable()
    {
        $connection = $this->doctrine->getConnection();
        $connection->query("SET FOREIGN_KEY_CHECKS = 0");
        $connection->query("TRUNCATE TABLE trip");
        $connection->query("SET FOREIGN_KEY_CHECKS = 1");
    }
}