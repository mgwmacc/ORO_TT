<?php

namespace TestTask\ChainCommandBundle\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class CommandChainManagerService
 *
 * Manages console command chains
 */
class CommandChainManagerService
{
    /**
     * PARAMETER_NAME_CONST parameter being searched in the service.yaml file for
     *
     * With its help commands are combined in command chains.
     */
    const PARAMETER_NAME_CONST = 'chain_command';

    /**
     * @var array
     */
    private array $commandChains = [];

    /**
     * CommandChainManagerService constructor
     *
     * @param ParameterBagInterface $params
     */
    public function __construct(
        private ParameterBagInterface $params
    ) {
        $this->registerChainMembers();
    }

    /**
     * Register chain members
     *
     * @return void
     */
    private function registerChainMembers(): void
    {
        if (!$this->params->has(self::PARAMETER_NAME_CONST)) {
            return;
        }

        $chainMembers = $this->params->get(self::PARAMETER_NAME_CONST);

        if (!$chainMembers) {
            return;
        }

        foreach ($chainMembers as $masterCommand => $memberCommands) {
            foreach ($memberCommands as $memberCommand) {

                //Special case (Master command name == Member command name)-> skipping:

                if ($memberCommand == $masterCommand) {
                    continue;
                }

                $this->commandChains[$masterCommand][] = $memberCommand;
            }
        }
    }

    /**
     * @param string $masterCommandName
     * @return array
     */
    public function getChainMembers(string $masterCommandName): array
    {
        return $this->hasChainMembers($masterCommandName) ? $this->commandChains[$masterCommandName] : [];
    }

    /**
     * @param string $masterCommandName
     * @return bool
     */
    public function hasChainMembers(string $masterCommandName): bool
    {
        return isset($this->commandChains[$masterCommandName]);
    }

    /**
     * Check if command is a Master command of a chain
     *
     * @param string $commandName Current command name
     *
     * @return bool
     */
    public function isMasterCommand(string $commandName): bool
    {
        return array_key_exists($commandName, $this->commandChains);
    }

    /**
     * Get first master command for member
     *
     * @param string $memberCommand
     *
     * @return string
     */
    public function getMasterCommandForMember(string $memberCommand = ''): string
    {
        if (!$memberCommand) {
            return '';
        }

        foreach ($this->commandChains as $masterCommand => $members) {
            foreach ($members as $command) {
                if ($command == $memberCommand) {
                    return $masterCommand;
                }
            }
        }

        return '';
    }
}