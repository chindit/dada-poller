<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\PollData;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;

class NetworkPoller implements PollerInterface
{
	private $interface = '';

	private $cache;

	public function __construct()
	{
		$this->cache = new FilesystemAdapter();
	}

	public function getName(): string
	{
		$name = explode('\\', __CLASS__);

		return end($name) ?: '';
	}

	public function poll(): array
	{
		/** @var CacheItem $startReceivedBytes */
		$startReceivedBytes = $this->cache->getItem('rx_bytes');
		/** @var CacheItem $startTransferredBytes */
		$startTransferredBytes = $this->cache->getItem('tx_bytes');

		$endReceivedBytes = (int)trim(file_get_contents($this->buildFileName()) ?: '');
		$endTransferredBytes = (int)trim(file_get_contents($this->buildFileName(false)) ?: '');

		$data = [];

		if (null !== $startTransferredBytes->get() && null !== $startReceivedBytes->get())
		{
			$data = [
				(new PollData('rx_bytes', (float)($endReceivedBytes - $startReceivedBytes->get())))->setCategory('network')->setName('Received traffic (bytes)'),
				(new PollData('tx_bytes', (float)($endTransferredBytes - $startTransferredBytes->get())))->setCategory('network')->setName('Transferred traffic (bytes)'),
			];
		}

		$startReceivedBytes->set($endReceivedBytes)->expiresAfter(65);
		$startTransferredBytes->set($endTransferredBytes)->expiresAfter(65);

		$this->cache->save($startTransferredBytes);
		$this->cache->save($startReceivedBytes);

		return $data;
	}

	private function autodetectNetworkInterface(): string
	{
		if (!empty($this->interface))
		{
			return $this->interface;
		}

		if (isset($_ENV[ 'NETWORK_INTERFACE' ]) && is_file(sprintf('/sys/class/net/%s/rx_bytes', $_ENV[ 'NETWORK_INTERFACE' ])))
		{
			$this->interface = $_ENV[ 'NETWORK_INTERFACE' ];

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
