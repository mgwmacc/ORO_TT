<?php

namespace TestTask\ChainCommandBundle\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use TestTask\ChainCommandBundle\Service\CommandChainManagerService;

class CommandChainManagerServiceTest extends TestCase
{
    private CommandChainManagerService $commandChainManagerService;

    /**
     * @var ParameterBag
     */
    private ParameterBag $paramsBag;

    /**
     * Setting up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->paramsBag = new ParameterBag();

        //Modeling Command Chain setting structure

        $this->paramsBag->add([
            'chain_command' => [
                'foo:hello' => [
                    'bar:hi',
                    'foo:hello',  //Wrong Setting as the same as Master
                    'cache:clear' //Symfony command
                ]
            ]
        ]);

        $this->commandChainManagerService = new CommandChainManagerService($this->paramsBag);
    }

    /**
     * Method testGetChainMembers
     *
     * @covers CommandChainManagerService::getChainMembers
     *
     * @return void
     */
    public function testGetChainMembers()
    {
        $chainMembers = $this->commandChainManagerService->getChainMembers('foo:hello');
        $this->assertEquals(['bar:hi', 'cache:clear'], $chainMembers, 'Chain members were NOT found for master command');

        $chainMembers = $this->commandChainManagerService->getChainMembers('foo:NON_EXISTENT_COMMAND');
        $this->assertEquals([], $chainMembers, 'Chain members WERE found for foo:NON_EXISTENT_COMMAND');
    }

    /**
     * Method testHasChainMembers
     *
     * @covers CommandChainManagerService::hasChainMembers
     *
     * @return void
     */
    public function testHasChainMembers()
    {
        $this->assertTrue(
            $this->commandChainManagerService->hasChainMembers('foo:hello'),
            'Chain members WERE NOT found for master command'
        );

        $this->assertFalse(
            $this->commandChainManagerService->hasChainMembers('foo:NON_EXISTENT_COMMAND'),
            'Chain members WERE found for master command'
        );
    }

    /**
     * Method isMasterCommand
     *
     * @covers CommandChainManagerService::isMasterCommand
     *
     * @return void
     */
    public function testIsMasterCommand()
    {
        $this->assertTrue(
            $this->commandChainManagerService->isMasterCommand('foo:hello'),
            'Master command was NOT defined as Main\Master command'
        );

        $this->assertFalse(
            $this->commandChainManagerService->hasChainMembers('foo:NON_EXISTENT_COMMAND'),
            'Non-existent command WAS defined as Main\Master command'
        );

        $this->assertFalse(
            $this->commandChainManagerService->hasChainMembers('bar:hi'),
            'Chain member command WAS defined as Main\Master command'
        );
    }

    /**
     * Method testGetMasterCommandForMember
     *
     * @covers CommandChainManagerService::getMasterCommandForMember
     *
     * @return void
     */
    public function testGetMasterCommandForMember()
    {
        $this->assertEquals(
            'foo:hello',
            $this->commandChainManagerService->getMasterCommandForMember('bar:hi'),
            'Master command was NOT defined as Main\Master for a chain member'
        );

        $this->assertEquals(
            '',
            $this->commandChainManagerService->getMasterCommandForMember('bar:NON_EXISTENT_COMMAND'),
            'Master command WAS defined as Main\Master for a non-existent chain member'
        );

        $this->assertEquals(
            '',
            $this->commandChainManagerService->getMasterCommandForMember('foo:hello'),
            'Master command WAS defined as Main\Master for another Master command'
        );
    }
}