<?php

namespace TestTask\BarBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class TestTaskBarBundle
 *
 * Example bundle
 */
class TestTaskBarBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}