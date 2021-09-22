<?php

declare(strict_types=1);

namespace isleshocky77\HoverCom\Command;

use isleshocky77\HoverCom\Api\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DnsDeleteCommand extends Command
{
    protected static $defaultName = 'hover-com:dns:delete';

    protected function configure() : void
    {
        $this->addArgument('dns-ids', InputArgument::IS_ARRAY, 'The dns id(s) to update');
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $api = new Client();

        $table = new Table($output);
        $table->setHeaders(['ID', 'Name', 'Type', 'Content', 'TTL', 'Is Default']);

        $dnsRecordIds = $input->getArgument('dns-ids');

        foreach ($dnsRecordIds as $dnsRecordId) {
            $api->deleteDnsEntry($dnsRecordId);
        }

        $table->render();

        return 0;
    }
}
