<?php

namespace App\Service;

use App\Entity\PollData;

class MemoryPoller implements PollerInterface
{
	public function poll(): array
	{
		$memory = $this->getMemoryUsage();

		return [
			(new PollData('mem_total', (float)$memory[ 'MemTotal' ]))->setCategory('memory')->setName('Total Memory'),
			(new PollData('mem_free', (float)$memory[ 'MemFree' ]))->setCategory('memory')->setName('Free Memory'),
			(new PollData('mem_cached', (float)$memory[ 'Cached' ] + (float)$memory[ 'SwapCached' ]))->setCategory('memory')->setName('Cached Memory'),
			(new PollData('mem_used', (float)$memory[ 'MemTotal' ] - (float)$memory[ 'MemFree' ]))->setCategory('memory')->setName('Used Memory'),
		];
	}

	private function getMemoryUsage(): array
	{
		$memInfo = [];
		/** @var string[] $data */
		$data = file('/proc/meminfo');

		foreach ($data as $line)
		{
			list($name, $value) = explode(':', $line);
			$memInfo[ $name ] = trim($value);
		}

		return $memInfo;
	}

	public function getName(): string
	{
		$name = explode('\\', __CLASS__);

		return end($name) ?: '';
	}
}
