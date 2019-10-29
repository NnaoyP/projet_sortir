<?php
namespace App\Command\FakerFixtures;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Bridge\Doctrine\RegistryInterface;

use App\Entity\ParticipantArea;

class ParticipantAreaFixtureCommand extends Command
{
    protected static $defaultName = 'app:fixtures:participantarea';

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
        ->setDescription('Load fresh dummy data in participant_area table')
        ->addArgument('num', InputArgument::OPTIONAL, 'Load how many?', 10)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $num = $input->getArgument('num');

        $io = new SymfonyStyle($input, $output);

        $this->truncateTable();


        for($i=0; $i<$num; $i++){
            $participantArea = new ParticipantArea();

            $participantArea->setName(
                $this->faker->name()
            );

            $this->manager->persist($participantArea);
        }

        $this->manager->flush();
        $io->writeln($num . ' "ParticipantArea" loaded!');
        return 0;
    }

    protected function truncateTable()
    {
        $connection = $this->doctrine->getConnection();
        $connection->query("SET FOREIGN_KEY_CHECKS = 0");
        $connection->query("TRUNCATE TABLE participant_area");
        $connection->query("SET FOREIGN_KEY_CHECKS = 1");
    }
}