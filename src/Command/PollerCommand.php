<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\PollerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PollerCommand extends Command
{
	protected static $defaultName = 'app:poll';

	private $pollers;

	private $entityManager;

	public function __construct(iterable $pollers, EntityManagerInterface $entityManager, string $name = null)
	{
		$this->pollers = $pollers;

		parent::__construct($name);

		$this->entityManager = $entityManager;
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
		$output->writeln('Starting poll');

		/** @var PollerInterface $poller */
		foreach ($this->pollers as $poller)
		{
			$output->writeln(sprintf('Starting poller %s', $poller->getName()));

			$pollResults = $poller->poll();

			foreach ($pollResults as $pollResult)
			{
				$this->entityManager->persist($pollResult);
			}
		}

		$this->entityManager->flush();
	}
}
