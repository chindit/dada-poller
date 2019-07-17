<?php
declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PollerCommand extends Command
{
	protected static $defaultName = 'app:poll';

	private $pollers;

	public function __construct(iterable $pollers, string $name = null)
	{
		$this->pollers = $pollers;

		parent::__construct($name);
	}

	protected function configure()
	{
		$this
			->setDescription('Poll data from OS')
			->setHelp('Runs all the pollers and store data into DB.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('yeah');
	}
}
