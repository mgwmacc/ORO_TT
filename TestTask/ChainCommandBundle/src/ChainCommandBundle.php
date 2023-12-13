<?php

namespace TestTask\ChainCommandBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class ChainCommandBundle
 *
 * Example bundle
 */
class ChainCommandBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}