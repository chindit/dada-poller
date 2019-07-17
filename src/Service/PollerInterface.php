<?php
declare(strict_types=1);

namespace App\Service;

interface PollerInterface
{
	public function poll(): array;
}
