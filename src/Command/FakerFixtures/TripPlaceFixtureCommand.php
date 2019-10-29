<?php
namespace App\Command\FakerFixtures;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Bridge\Doctrine\RegistryInterface;

use App\Entity\TripPlace;
use App\Entity\City;

class TripPlaceFixtureCommand extends Command
{
    protected static $defaultName = 'app:fixtures:tripplace';

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
        ->setDescription('Load fresh dummy data in trip_place table')
        ->addArgument('num', InputArgument::OPTIONAL, 'Load how many?', 10)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $num = $input->getArgument('num');

        $io = new SymfonyStyle($input, $output);

        $this->truncateTable();

        $allCityEntities = $this->doctrine->getRepository(City::class)->findAll();

        for($i=0; $i<$num; $i++){
            $tripPlace = new TripPlace();

            $tripPlace->setName(
                $this->faker->name()
            );
            $tripPlace->setStreet(
                $this->faker->text(100)
            );
            $tripPlace->setLatitude(
                $this->faker->optional($chancesOfValue = 0.5, $default = null)->randomFloat($nbMaxDecimals = NULL, $min = 0, $max = NULL)
            );
            $tripPlace->setLongitude(
                $this->faker->optional($chancesOfValue = 0.5, $default = null)->randomFloat($nbMaxDecimals = NULL, $min = 0, $max = NULL)
            );
            $tripPlace->setCity(
                $this->faker->randomElement($allCityEntities)
            );

            $this->manager->persist($tripPlace);
        }

        $this->manager->flush();
        $io->writeln($num . ' "TripPlace" loaded!');
        return 0;
    }

    protected function truncateTable()
    {
        $connection = $this->doctrine->getConnection();
        $connection->query("SET FOREIGN_KEY_CHECKS = 0");
        $connection->query("TRUNCATE TABLE trip_place");
        $connection->query("SET FOREIGN_KEY_CHECKS = 1");
    }
}