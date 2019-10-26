<?php

namespace isleshocky77\HoverCom\Command;

use isleshocky77\HoverCom\Api\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DnsUpdateCommand extends Command
{
    protected static $defaultName = 'hover-com:dns:update';

    protected function configure()
    {
        $this->addArgument('dns-ids', InputArgument::IS_ARRAY, 'The dns id(s) to update');
        $this->addOption('content', null, InputOption::VALUE_REQUIRED, 'The content of the entry');
        $this->addOption('ttl', null, InputOption::VALUE_OPTIONAL, 'The ttl to use for the entry');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $api = new Client();

        $table = new Table($output);
        $table->setHeaders(['ID', 'Name', 'Type', 'Content', 'TTL', 'Is Default']);

        $dnsRecordIds = $input->getArgument('dns-ids');
        $content = $input->getOption('content');
        $ttl = $input->getOption('ttl');

        foreach ($dnsRecordIds as $dnsRecordId) {
            $api->updateDnsEntry($dnsRecordId, $content, $ttl);
        }

        $table->render();
    }
}
