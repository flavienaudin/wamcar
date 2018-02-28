<?php

namespace AppBundle\Command;

use AppBundle\Elasticsearch\Type\VehicleInfo;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader as CsvReader;
use Novaway\ElasticsearchClient\Aggregation\Aggregation;
use Novaway\ElasticsearchClient\Filter\TermFilter;
use Novaway\ElasticsearchClient\Query\QueryBuilder;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wamcar\Vehicle\Fuel;

class ImportCityCommand extends BaseCommand
{
    /** @var EntityManager */
    private $em;

    public function __construct(
        EntityManagerInterface $em
    )
    {
        parent::__construct();
        $this->em = $em;
    }

    /**
     * Configure command
     *
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:populate:city')
            ->setDescription('Populate the city info table with data from an CSV file')
            ->addArgument(
                'file',
                InputArgument::OPTIONAL,
                'The path of the CSV file to load (default: fixture data)',
                __DIR__ . '/../../../database/fixtures/cities.csv'
            );
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $now = new \DateTime();
        $output->writeln('<comment>Start : ' . $now->format('d-m-Y G:i:s') . ' ---</comment>');

        // Importing CSV on DB via Doctrine ORM
        $this->import($input, $output);

        $now = new \DateTime();
        $output->writeln('<comment>End : ' . $now->format('d-m-Y G:i:s') . ' ---</comment>');

    }

    private function import(InputInterface $input, OutputInterface $output)
    {

        // Turning off doctrine default logs queries for saving memory
        $connection = $this->em->getConnection();
        $connection->getConfiguration()->setSQLLogger(null);

        $filename = $input->getArgument('file');

        $data = $this->convertCsvToArray($filename);

        $size      = count($data);
        $batchSize = 500;
        $i         = 1;

        // Starting progress
        $progress = new ProgressBar($output, $size);
        $progress->start();

        $stmt = $connection->prepare("TRUNCATE TABLE city");
        $stmt->execute();

        foreach($data as $row) {
            list($lat, $lon) = explode(',', $row[5]);
            $insertValues[] = "(\"$row[0]\", \"$row[1]\", \"$row[2]\", \"$lat\", \"$lon\", \"$row[6]\", \"$row[7]\", \"$row[3]\", \"$row[4]\")";

            // Each 20 cities persisted we flush everything
            if (($i % $batchSize) === 0) {

                $sql = 'REPLACE INTO city (insee, city_postal_code, city_name, city_latitude, city_longitude, code_departement, code_region, departement, region) VALUES ';
                $sql .= implode(',', $insertValues);
                $stmt = $connection->prepare($sql);
                $stmt->execute();
                // Advancing for progress bar
                $progress->advance($batchSize);
                $insertValues = [];
                $now = new \DateTime();
                $output->writeln(' of cities imported ... | ' . $now->format('d-m-Y G:i:s'));
            }

            $i++;
        }

        $sql = 'REPLACE INTO city (insee, city_postal_code, city_name, city_latitude, city_longitude, code_departement, code_region, departement, region) VALUES ';
        $sql .= implode(',', $insertValues);
        $stmt = $connection->prepare($sql);
        $stmt->execute();

        $progress->finish();
    }

    /**
     * @param        $filename
     * @param array  $header
     * @param string $delimiter
     * @return array|bool
     */
    private function convertCsvToArray($filename, $delimiter = ';', $skipFirstLine = true)
    {
        if(!file_exists($filename) || !is_readable($filename)) {
            return false;
        }

        $data = [];

        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 10000, $delimiter)) !== false) {
                if ($skipFirstLine) {
                    $skipFirstLine = !$skipFirstLine;
                    continue;
                }

                $data[] = $row;
            }

            fclose($handle);
        }

        return $data;
    }

}
