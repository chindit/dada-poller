<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\PollData;
use function get_class;

class NetworkPoller implements PollerInterface
{
	private $interface = '';

	public function getName(): string
	{
		$name = explode('\\', get_class());

		return end($name) ?: '';
	}

	public function poll(): array
	{
		$startReceivedBytes = (int)trim(file_get_contents($this->buildFileName()) ?: '');
		$startTransferredBytes = (int)trim(file_get_contents($this->buildFileName(false)) ?: '');

		usleep(1000);
		$endReceivedBytes = (int)trim(file_get_contents($this->buildFileName()) ?: '');
		$endTransferredBytes = (int)trim(file_get_contents($this->buildFileName(false)) ?: '');

		return [
			new PollData('rx_bytes', (float)($endReceivedBytes - $startReceivedBytes)),
			new PollData('tx_bytes', (float)($endTransferredBytes - $startTransferredBytes)),
		];
	}

	private function autodetectNetworkInterface(): string
	{
		if (!empty($this->interface))
		{
			return $this->interface;
		}

		/** @var string[] $interfaces */
		$interfaces = scandir('/sys/class/net');

		foreach ($interfaces as $interface)
		{
			if ($interface[ 0 ] === '.')
			{
				continue;
			}

			if ((int)trim(file_get_contents($this->buildFileName(true, $interface)) ?: '') > 0)
			{
				return $interface;
			}
		}

		return '';
	}

	private function buildFileName(bool $isReception = true, ?string $interface = null): string
	{
		if (null === $interface)
		{
			$interface = $this->autodetectNetworkInterface();
		}

		return sprintf('/sys/class/net/%s/statistics/' . ($isReception ? 'r' : 't') . 'x_bytes', $interface);
	}
}
