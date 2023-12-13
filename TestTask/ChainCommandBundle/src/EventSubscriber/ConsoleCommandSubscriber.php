<?php

namespace TestTask\ChainCommandBundle\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TestTask\ChainCommandBundle\Service\CommandChainManagerService;

/**
 * Class ConsoleCommandSubscriber
 *
 * The class represents a subscriber for a command line Symfony commands being called.
 * After a command is called the subscriber will be called to check a called command for
 * being a part of a configured chain if any.
 */
class ConsoleCommandSubscriber implements EventSubscriberInterface
{
    /**
     * @var bool
     */
    public bool $chainExecuted = false;

    /**
     * @var OutputInterface
     */
    private ?OutputInterface $bufferedOutput = null;

    /**
     * ChainCommandEventSubscriber constructor
     *
     * @param CommandChainManagerService $commandChainManagerService
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected CommandChainManagerService $commandChainManagerService,
        protected LoggerInterface $logger,
    ) {
        $this->bufferedOutput = $this->getBufferedOutput();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND   => ['beforeCommand'],
            ConsoleEvents::TERMINATE => ['afterCommand']
        ];
    }

    /**
     * Before console command subscriber event
     *
     * @param ConsoleCommandEvent $event
     * @throws ExceptionInterface
     */
    public function beforeCommand(ConsoleCommandEvent $event): void
    {
        $command       = $event->getCommand();
        $commandName   = $command->getName();
        $masterCommand = $this->commandChainManagerService->getMasterCommandForMember($commandName);

        if ($masterCommand) {
            $event->getOutput()->writeln(sprintf(
                'Error: %s command is a member of %s command chain and cannot be executed on its own.',
                $commandName, $masterCommand
            ));

            $event->disableCommand();

            return;
        }

        if ($this->commandChainManagerService->isMasterCommand($commandName)) {
            $this->formatLog(
                '%s is a master command of a command chain that has registered member commands',
                [$commandName]
            );

            $chainMembers = $this->commandChainManagerService->getChainMembers($commandName);

            if ($chainMembers) {
                foreach ($chainMembers as $memberCommand) {
                    $this->formatLog(
                        '%s registered as a member of %s command chain', [
                            $memberCommand,
                            $commandName
                        ]
                    );
                }
            }

            $this->formatLog('Executing %s command itself first:', [$commandName]);
            $command->getApplication()->get($commandName)->run($this->getArrayInput(), $this->bufferedOutput);

            $outputMessage = $this->bufferedOutput->fetch();

            $event->getOutput()->write($outputMessage);
            $this->logger->info($outputMessage);
            $event->disableCommand();
        }
    }

    /**
     * After console command subscriber event
     *
     * @param ConsoleTerminateEvent $event
     */
    public function afterCommand(ConsoleTerminateEvent $event): void
    {
        $command = $event->getCommand();
        $hasChainMembers = $this->commandChainManagerService->hasChainMembers($command->getName());
        $this->chainExecuted = false;

        if (!$hasChainMembers) {
            return;
        }

        $this->formatLog('Executing %s chain members:', [$command->getName()]);

        try {
            $this->chainExecuted = $this->executeCommandChain($event);
        } catch (ExceptionInterface $e) {
            $event->getOutput()->writeln('Command chain members could not be loaded properly');
        }

        $this->formatLog('Execution of %s chain completed.', [$command->getName()]);
    }

    /**
     * Executes Command chain
     *
     * @param ConsoleTerminateEvent $event
     *
     * @return bool
     * @throws ExceptionInterface
     */
    protected function executeCommandChain(ConsoleTerminateEvent $event): bool
    {
        $command      = $event->getCommand();
        $application  = $command->getApplication();
        $chainMembers = $this->commandChainManagerService->getChainMembers($command->getName());

        if (!$chainMembers) {
            return false;
        }

        foreach ($chainMembers as $memberCommand) {
            $application->get($memberCommand)->run(
                $this->getArrayInput(['command' => $memberCommand]),
                $this->bufferedOutput
            );

            $outputMessage = $this->bufferedOutput->fetch();

            $event->getOutput()->write($outputMessage);
            $this->logger->info($outputMessage);
        }

        return true;
    }

    /**
     * Formats Log message
     *
     * @param string $message
     * @param array $arguments
     *
     * @return void
     */
    protected function formatLog(string $message, array $arguments): void
    {
        $this->logger->info(sprintf($message, ...$arguments));
    }

    /**
     * Returns BufferedOutput instance
     *
     * @return OutputInterface
     */
    protected function getBufferedOutput(): OutputInterface
    {
        return $this->bufferedOutput ?: new BufferedOutput();
    }

    /**
     * Returns ArrayInput instance
     *
     * @param array $options
     * @return InputInterface
     */
    protected function getArrayInput(array $options = []): InputInterface
    {
        return new ArrayInput($options);
    }

    /**
     * Returns chainExecuted
     *
     * @return bool
     */
    public function getChainExecuted(): bool
    {
        return $this->chainExecuted;
    }
}