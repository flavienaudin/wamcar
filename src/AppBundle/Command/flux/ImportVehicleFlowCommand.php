<?php

namespace AppBundle\Command\flux;


use AppBundle\Command\BaseCommand;
use AutoData\Converter\ArrayToXMLConverter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportVehicleFlowCommand extends BaseCommand
{

    const SOURCE_AUTOBONPLAN = "autobonplan";
    const SOURCE_AUTOSMANUEL = "autosmanuel";

    /**
     * Configure command
     *
     */
    protected function configure()
    {
        $this
            ->setName('wamcar:vehicleFlow:import')
            ->setDescription('Import vehicles from file')
            ->addOption(
                'source',
                's',
                InputOption::VALUE_OPTIONAL,
                'The source of data : autobonplan, autosmanuel',
                'autobonplan'
            )
            ->addOption(
                'file',
                'f',
                InputOption::VALUE_OPTIONAL,
                'The file path of the data to import (default: fixture data)',
                __DIR__ . '/../../../../database/fixtures/autobonplan/magento_flux_products_extrait.xml'
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
        $this->output = $output;

        $source = $input->getOption('source');
        $output->writeln('Source : ' . $source);

        $dataFile = $input->getOption('file');
        $output->writeln('DatafFile : ' . $dataFile);

        switch ($source) {
            case self::SOURCE_AUTOBONPLAN:
                $xmlElement = simplexml_load_file($dataFile);
                $dataAsArray = ArrayToXMLConverter::revert($xmlElement->asXML());
                dump($dataAsArray);

                // TODO
                // create new ProVehicle
                // setGarage

                break;
            case self::SOURCE_AUTOSMANUEL:
                $row = 1;
                if (($handle = fopen($dataFile, "r")) !== FALSE) {
                    while (($data = fgetcsv($handle, 0, "|")) !== FALSE) {
                        $num = count($data);
                        //echo "<p> $num champs à la ligne $row: <br /></p>\n";

                        $this->log("info", "row n°" . $row . " - Id : " . $data[0] . ' - Nb données : ' . $num);
                        /*for ($c=0; $c < $num; $c++) {
                            $this->log("info", $data[$c].';', true );
                        }*/
                        $row++;
                    }
                    fclose($handle);
                }

                break;
            default:
                $this->log("error", printf("Source <%s>inconnue", $source));
        }
    }
}