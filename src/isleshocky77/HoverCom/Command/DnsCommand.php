<?php

declare(strict_types=1);

namespace isleshocky77\HoverCom\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DnsCommand extends Command
{
    protected static $defaultName = 'hover-com:dns:list';

    protected function configure() : void
    {
        $this->addArgument('domains', InputArgument::IS_ARRAY, 'The domain(s) to list DNS for');
        $this->addOption('all-domains', null, InputOption::VALUE_NONE, 'Look through all domains');
        $this->addOption('filter-type', null, InputOption::VALUE_OPTIONAL, 'The types of records to filter and show');
        $this->addOption('filter-name', null, InputOption::VALUE_OPTIONAL, 'The record names to filter and show');
        $this->addOption('filter-content', null, InputOption::VALUE_OPTIONAL, 'The record content to filter and show');
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $api = new \isleshocky77\HoverCom\Api\Client();

        $table = new Table($output);
        $table->setHeaders(['ID', 'Name', 'Type', 'Content', 'TTL', 'Is Default']);

        $domains = $input->getArgument('domains');
        if ($input->getOption('all-domains') === true) {
            $domains = array_column($api->getDomains(), 'id');
        }
        foreach ($domains as $domain) {
            $dnss = $api->getDns($domain);
            $table->addRow(new TableSeparator());
            $table->addRow([new TableCell(sprintf('<info>%s</info>', $dnss['domain_name']), ['colspan' => 6]),]);

            foreach ($dnss['entries'] as $dns) {

                $filterType = $input->getOption('filter-type');
                if($filterType && $dns['type'] !== $filterType) {
                    continue;
                }

                $filterName = $input->getOption('filter-name');
                if($filterName && $dns['name'] !== $filterName) {
                    continue;
                }

                $filterContent = $input->getOption('filter-content');
                if($filterContent && $dns['content'] !== $filterContent) {
                    continue;
                }

                $table->addRow([
                    $dns['id'], $dns['name'], $dns['type'], $dns['content'], $dns['ttl'], $dns['is_default'],
                ]);
            }
        }

        $table->render();

        return 0;
    }
}
