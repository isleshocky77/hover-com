<?php

declare(strict_types=1);

namespace isleshocky77\HoverCom\Command;

use isleshocky77\HoverCom\Api\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class LoginCommand extends Command
{
    protected static $defaultName = 'hover-com:login';

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $api = new Client();

        $helper = $this->getHelper('question');

        $question = new Question('hover.com username:');
        $question->setHidden(true);
        $question->setHiddenFallback(false);

        $username = $helper->ask($input, $output, $question);

        $question = new Question('hover.com password:');
        $question->setHidden(true);
        $question->setHiddenFallback(false);

        $password = $helper->ask($input, $output, $question);

        $question = new Question('hover.com MFA ToTP Code:');
        $question->setHidden(true);
        $question->setHiddenFallback(false);

        $totpCode = $helper->ask($input, $output, $question);

        try {
            $api->login($username, $password, $totpCode);
            $output->write("Success for user " . $username);
        } catch (\Exception $e) {
            $output->write("Login Failed for user " . $username . " : " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
