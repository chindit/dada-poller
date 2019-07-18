#!/usr/bin/env php
<?php
require __DIR__ . '/vendor/autoload.php';

use App\Command\PollerCommand;
use App\Kernel;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');
$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'dev', $_SERVER['APP_ENV'] === 'dev');
$kernel->boot();
$application = new Application('dada-poller', '1.0.0');
/** @var Command $command */
$command = $kernel->getContainer()->get(PollerCommand::class);

$application->add($command);

$application->setDefaultCommand($command->getName(), true);
$application->run();
