#!/usr/bin/env php
<?php
require __DIR__ . '/vendor/autoload.php';

use App\Command\PollerCommand;
use App\Kernel;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

$kernel = new Kernel('dev', true);
$kernel->boot();
$application = new Application('echo', '1.0.0');
/** @var Command $command */
$command = $kernel->getContainer()->get(PollerCommand::class);

$application->add($command);

$application->setDefaultCommand($command->getName(), true);
$application->run();
