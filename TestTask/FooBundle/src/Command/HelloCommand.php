<?php

namespace TestTask\FooBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class HelloCommand
 *
 * The command prints "Hello from Foo!"
 */
class HelloCommand extends Command
{
    /**
     * @return void
     */
    protected function configure():void
    {
        $this
            ->setName('foo:hello')
            ->setDescription('Some description test')
            ->setHelp('Some help text');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Hello from Foo!');

        return Command::SUCCESS;
    }
}