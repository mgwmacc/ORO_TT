<?php

namespace TestTask\FooBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class TestTaskFooBundle
 *
 * Example bundle
 */
class TestTaskFooBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}