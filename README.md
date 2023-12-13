# ChainCommandBundle (Test Task)

ChainCommandBundle for Command Chaining Functionality test task.

# Installation

### 1) Copy this folder to Root directory of your Symfony app

### 2) Add bundles to the "./config/bundles.php"

```php
<?php

return [
    ...
    TestTask\FooBundle\TestTaskFooBundle::class => ['all' => true],
    TestTask\BarBundle\TestTaskBarBundle::class => ['all' => true],
    TestTask\ChainCommandBundle\ChainCommandBundle::class => ['all' => true],
];

```

### 3) Update your composer.json so that needed bundles are loaded

```json

"autoload" {
    "psr-4": {
    "App\\": "src/",
        
    "TestTask\\FooBundle\\": "TestTask/FooBundle/src/",
    "TestTask\\BarBundle\\": "TestTask/BarBundle/src/",
    "TestTask\\ChainCommandBundle\\": "TestTask/ChainCommandBundle/src/"
    }
},

```
### 3.1) Run: "composer dump-autoload"

### 4) Add the next to the main ./config/service.yaml file:

```yaml
    TestTask\FooBundle\Command\:
      resource: '../TestTask/FooBundle/src/Command/'

    TestTask\BarBundle\Command\:
      resource: '../TestTask/BarBundle/src/Command/'
```

### 5) Run "foo:hello" OR "bar:hi" command 

### Notes:

- Logs are expected to be in the ../var/log/dev.log file.
- Additional FooBandle and BarBandle are included and reside on the same level as ChainCommandBundle
- PHP 8.2 is used.
- Symfony 7.0 is used.
- PHPUnit 10 is used
