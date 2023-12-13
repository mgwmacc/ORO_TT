<?php

namespace TestTask\ChainCommandBundle\Tests\Unit\EventSubscriber;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use TestTask\ChainCommandBundle\EventSubscriber\ConsoleCommandSubscriber;
use TestTask\ChainCommandBundle\Service\CommandChainManagerService;

class ConsoleCommandSubscriberTest extends TestCase
{
    /**
     * @var ConsoleCommandSubscriber
     */
    private ConsoleCommandSubscriber $subscriber;

    /**
     * @var ParameterBag
     */
    private ParameterBag $paramsBag;

    /**
     * Method setUp
     *
     * @return void
     */
    protected function setUp(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $this->paramsBag  = new ParameterBag();

        //Modeling params
        $this->paramsBag->add([
            'chain_command' => [
                'foo:hello' => [
                    'bar:hi',
                    'foo:hello',
                ]
            ]
        ]);

        $this->subscriber = new ConsoleCommandSubscriber(new CommandChainManagerService($this->paramsBag), $logger);
    }

    /**
     * Method testGetSubscribedEvents
     *
     * @return void
     */
    public function testGetSubscribedEvents()
    {
        $expectedEvents = [
            ConsoleEvents::COMMAND   => ['beforeCommand'],
            ConsoleEvents::TERMINATE => ['afterCommand']
        ];

        $this->assertSame(
            $expectedEvents,
            ConsoleCommandSubscriber::getSubscribedEvents(),
            'Actual Subscribed events ara different from expected'
        );
    }

    /**
     * Method testBeforeCommandChain
     *
     * @param string $commandName
     * @param string $commandMessage
     * @param bool $commandShouldRun
     *
     * @covers       ConsoleCommandSubscriber::beforeCommand
     * @dataProvider beforeCommandChainDataProvider
     *
     * @return void
     */
    public function testBeforeCommandChain(
        string $commandName = '',
        string $commandMessage = '',
        bool $commandShouldRun = true
    ) {
        $application = new Application();
        $command = $application->register($commandName)->setCode(function () use ($commandMessage) {
            print $commandMessage;
        });

        $command->setApplication($application);

        $event = new ConsoleCommandEvent(
            $command,
            new ArrayInput([]),
            new BufferedOutput()
        );

        $this->subscriber->beforeCommand($event);

        $this->assertEquals($commandShouldRun, $event->commandShouldRun());
    }

    /**
     * Method testAfterCommandChain
     *
     * @param string $commandName
     * @param string $commandMessage
     * @param bool $executeCommandChain
     *
     * @covers       ConsoleCommandSubscriber::afterCommand
     * @dataProvider afterCommandChainDataProvider
     *
     * @return void
     */
    public function testAfterCommandChain(
        string $commandName = '',
        string $commandMessage = '',
        bool $executeCommandChain = false
    ) {
        $application = new Application();
        $command = $application->register($commandName)->setCode(function () use ($commandMessage) {
            print $commandMessage;
        });

        $application->register('bar:hi')->setCode(function () {
            print 'Hi from Bar!';
        });

        $command->setApplication($application);

        $event = new ConsoleTerminateEvent(
            $command,
            new ArrayInput([]),
            new BufferedOutput(),
            1
        );

        $this->subscriber->afterCommand($event);

        $this->assertEquals(
            $this->subscriber->getChainExecuted(),
            $executeCommandChain,
            'Chain was not executed properly'
        );
    }

    /**
     * Method testAfterCommandChain
     *
     * @param string $commandName
     * @param string $commandMessage
     * @param bool $executeCommandChain
     *
     * @covers       ConsoleCommandSubscriber::afterCommand
     * @dataProvider afterCommandChainDataProvider
     *
     * @return void
     */

    /**
     * Method testGetArrayInput.
     *
     * @covers ConsoleCommandSubscriber::getArrayInput
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testGetArrayInput()
    {
        $yourClassReflection = new \ReflectionClass(ConsoleCommandSubscriber::class);
        $method = $yourClassReflection->getMethod('getArrayInput');
        // Use next if your PHP version < 8.1.0
        //$method->setAccessible(true);

        $result = $method->invoke($this->subscriber, ['option' => 'value']);
        $this->assertEquals(
            'value',
            $result->getParameterOption('option'),
            'Options provied to getArrayInput method are NOT passe further to ArrayInput');
        $this->assertInstanceOf(
            InputInterface::class,
            $result,
            'Object of InputInterface type is to be return'
        );
    }

    /**
     * Method testGetChainExecuted.
     *
     * @covers ConsoleCommandSubscriber::getChainExecuted
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testGetChainExecuted()
    {
        $this->assertIsBool($this->subscriber->getChainExecuted(), 'getChainExecuted method must return Bool');
    }

    /**
     * @return array[]
     */
    public static function beforeCommandChainDataProvider(): array
    {
        return [
            'Main Chain Command'     => ['foo:hello', 'Hello from Foo!', false],
            'Chain Member Command'   => ['bar:hi', 'Hi from Bar!', false],
            'Some 3rd party Command' => ['some:command', 'Hi from Some Command!', true],
        ];
    }

    /**
     * @return array[]
     */
    public static function afterCommandChainDataProvider(): array
    {
        return [
            'Main Chain Command'     => ['foo:hello', 'Hello from Foo!', true],
            'Chain Member Command'   => ['bar:hi', 'Hi from Bar!', false],
            'Some 3rd party Command' => ['some:command', 'Hi from Some Command!', false],
        ];
    }
}