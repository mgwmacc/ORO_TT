<?php

namespace TestTask\ChainCommandBundle\Tests\Functional\EventSubscriber;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ChainCommandTest extends KernelTestCase
{
    /**
     * @var Application
     */
    protected Application $application;

    /**
     * @var OutputInterface
     */
    protected OutputInterface $bufferedOutput;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();
        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);
        $this->bufferedOutput = new BufferedOutput();
    }

    /**
     * Method testCommandChain
     *
     * @param string $commandName
     * @param string $commandMessage
     * @param string $expectedMessage
     *
     * @dataProvider commandChainDataProvider
     *
     * @return void
     */
    public function testCommandChain(
        string $commandName = '',
        string $commandMessage = '',
        string $expectedMessage = ''
    )
    {
        $this->application->register($commandName)->setCode(function () use ($commandMessage) {
           $this->bufferedOutput->write($commandMessage);
        });

        $this->application->doRun(
            new ArrayInput(['command' => $commandName]),
            $this->bufferedOutput
        );

        $output = $this->bufferedOutput->fetch();

        $this->assertEquals($expectedMessage, trim($output), 'Wrong Command chain output');
    }

    /**
     * @return array[]
     */
    public static function commandChainDataProvider(): array
    {
        return [
            'Main Chain Command' => [
                'foo:hello',                   //Command name
                'Hello from Foo!',             //Command own output
                'Hello from Foo!Hi from Bar!'  //Expected output after execution
            ],
            'Chain Member Command' => [
                'bar:hi',
                'Hi from Bar!',
                'Error: bar:hi command is a member of foo:hello command chain and cannot be executed on its own.'
            ],
        ];
    }
}