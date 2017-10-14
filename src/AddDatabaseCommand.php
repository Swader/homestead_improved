<?php

namespace Laravel\Homestead;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class AddDatabaseCommand extends Command
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
            ->setName('add-db')
            ->setDescription('Add a new database to the Homestead.yaml file')
            ->addArgument('name', InputArgument::REQUIRED, 'the database name to be added');
    }

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     *
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->homesteadYamlContents = Yaml::parse(file_get_contents('./Homestead.yaml'));
        $this->output = $output;

        $name = $input->getArgument('name');

        $canAddItem = $this->canItemBeAdded($name);

        if($canAddItem) {
            $this->addItemAndSaveToYamlFile($name);
        }
    }

    /**
     * @param $name
     *
     * @return bool
     */
    private function canItemBeAdded($name)
    {
        $canAddItem = true;
        foreach($this->homesteadYamlContents['databases'] as $database) {
            if($database === $name) {
                $this->output->writeln(sprintf('<error>Database %s already defined.</error>', $name));
                $canAddItem = false;
                break;
            }
        }

        return $canAddItem;
    }

    /**
     * @param $name
     */
    private function addItemAndSaveToYamlFile($name)
    {
        $this->homesteadYamlContents['databases'][] = $name;

        file_put_contents('./Homestead.yaml', Yaml::dump($this->homesteadYamlContents));
        
        $this->output->writeln('<info>New database successfully added.</info>');
        $this->output->write('<info>Don\'t forget to re-provision your VM.</info>');
    }
}
