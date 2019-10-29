<?php
namespace App\Command\FakerFixtures;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Bridge\Doctrine\RegistryInterface;

use App\Entity\Participant;
use App\Entity\ParticipantArea;
use App\Entity\Trip;

class ParticipantFixtureCommand extends Command
{
    protected static $defaultName = 'app:fixtures:participant';

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
        ->setDescription('Load fresh dummy data in participant table')
        ->addArgument('num', InputArgument::OPTIONAL, 'Load how many?', 10)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $num = $input->getArgument('num');

        $io = new SymfonyStyle($input, $output);

        $this->truncateTable();

        $allParticipantAreaEntities = $this->doctrine->getRepository(ParticipantArea::class)->findAll();
        $allTripEntities = $this->doctrine->getRepository(Trip::class)->findAll();

        for($i=0; $i<$num; $i++){
            $participant = new Participant();

            $participant->setEmail(
                $this->faker->unique()->email
            );
            //no faker method found!
            //$participant->setRoles(
            //    $this->faker->
            //);
            $participant->setPassword(
                $this->faker->password()
            );
            $participant->setLastName(
                $this->faker->lastName
            );
            $participant->setFirstName(
                $this->faker->firstName
            );
            $participant->setPhoneNumber(
                $this->faker->text(10)
            );
            $participant->setIsActive(
                $this->faker->boolean($chanceOfGettingTrue = 50)
            );
            $participant->setImageUrl(
                $this->faker->optional($chancesOfValue = 0.5, $default = null)->text(255)
            );
            $participant->setParticipantArea(
                $this->faker->randomElement($allParticipantAreaEntities)
            );
            /*
            uncomment below to add more than one
            (you might need to increase the total number of participatingTrips to load in LoadAllFixturesCommand.php
            */
            //$numberOfparticipatingTrips = $this->faker->numberBetween($min = 0, $max = 5);
            //for($n = 0; $n < $numberOfparticipatingTrips; $n++){
                $participant->addParticipatingTrip(
                    $this->faker->unique()->randomElement($allTripEntities)
                );
            //}

            $this->manager->persist($participant);
        }

        $this->manager->flush();
        $io->writeln($num . ' "Participant" loaded!');
        return 0;
    }

    protected function truncateTable()
    {
        $connection = $this->doctrine->getConnection();
        $connection->query("SET FOREIGN_KEY_CHECKS = 0");
        $connection->query("TRUNCATE TABLE participant");
        $connection->query("TRUNCATE TABLE participant_trip");
        $connection->query("SET FOREIGN_KEY_CHECKS = 1");
    }
}