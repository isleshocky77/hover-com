<?php

namespace isleshocky77\HoverCom\Command;

use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DomainsCommand extends Command
{
    protected static $defaultName = 'hover-com:domains:list';

    public function configure()
    {
        $this->addOption('filter-name', null, InputOption::VALUE_OPTIONAL, 'Name to filter and show');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $api = new \isleshocky77\HoverCom\Api\Client();
        $api->login(getenv('HOVER_USERNAME'), getenv('HOVER_PASSWORD'));

        $domains = $api->getDomains();

        $table = new Table($output);
        $table->setHeaders(['ID', 'Domain Name', 'Status', 'Renewal Date', 'Auto Renew']);

        foreach ($domains as $domain) {

            $filterName = $input->getOption('filter-name');
            if ($filterName && stristr($domain['domain_name'], $filterName) === false) {
                continue;
            }
            $table->addRow([
                $domain['id'], $domain['domain_name'], $domain['status'], $domain['renewal_date'], $domain['auto_renew']
            ]);
        }
        $table->render();
    }
}
