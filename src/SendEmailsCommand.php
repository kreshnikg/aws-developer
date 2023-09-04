<?php

namespace App;

use Aws\Sqs\SqsClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendEmailsCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:send-emails')
            ->setDescription('Send emails from SQS messages');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client = new SqsClient([
            'version' => '2012-11-05',
            'region'  => $this->container->get('parameter_bag')->get('aws_region'),
            'credentials' => [
                'secret' => $this->container->get('parameter_bag')->get('aws_secret_key'),
                'key' => $this->container->get('parameter_bag')->get('aws_access_key'),
            ]
        ]);

        $output->write('Success running app:send-emails');

        return Command::SUCCESS;
    }
}