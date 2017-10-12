<?php

namespace Laravel\Homestead;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class AddSiteCommand extends Command
{
    /**
     * @var array
     */
    private $homesteadYamlContents;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('add-site')
            ->setDescription('Add a new site to the Homestead.yaml file')
            ->addArgument('hostname', InputArgument::REQUIRED, 'the hostname to add')
            ->addArgument('path', InputArgument::REQUIRED, 'the path for the given hostname');
    }

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->homesteadYamlContents = Yaml::parse(file_get_contents('./Homestead.yaml'));
        $this->output = $output;

        $hostname = $input->getArgument('hostname');
        $path = $input->getArgument('path');

        $canAddItem = $this->canItemBeAdded($hostname, $path);

        if($canAddItem) {
            $this->addItemAndSaveToYamlFile($hostname, $path);
        }
    }

    /**
     * @param $currentSites
     * @param $hostname
     * @param $path
     * @param OutputInterface $output
     *
     * @return bool
     */
    private function canItemBeAdded($hostname, $path)
    {
        $canAddItem = true;
        foreach($this->homesteadYamlContents['sites'] as $site) {
            if($site['map'] === $hostname) {
                $this->output->writeln(sprintf('<error>Hostname %s already used.</error>', $hostname));
                $canAddItem = false;
                break;
            }

            if($site['to'] === $path) {
                $this->output->writeln(sprintf('<error>Path %s already mapped to %s</error>', $path, $site['to']));
                $canAddItem = false;
                break;
            }
        }

        return $canAddItem;
    }

    private function addItemAndSaveToYamlFile($hostname, $path)
    {
        $this->homesteadYamlContents['sites'][] = [
            'map'   => $hostname,
            'to'    => $path
        ];

        file_put_contents('./Homestead.yaml', Yaml::dump($this->homesteadYamlContents));
        
        $this->output->writeln('<info>New site successfully added.</info>');
        $this->output->write('<info>Don\'t forget to re-provision your VM.</info>');
    }
}
