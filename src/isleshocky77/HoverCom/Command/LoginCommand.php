<?php

namespace isleshocky77\HoverCom\Command;

use isleshocky77\HoverCom\Api\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoginCommand extends Command
{
    protected static $defaultName = 'hover-com:login:test';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $api = new Client();

        try {
            $api->login(getenv('HOVER_USERNAME'), getenv('HOVER_PASSWORD'));
            $output->write("Success for user " . getenv('HOVER_USERNAME'));
        } catch (\Exception $e) {
            $output->write("Login Failed for user " . getenv('HOVER_USERNAME'));
        }
    }
}
