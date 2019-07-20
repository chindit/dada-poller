<?php
/**
 * dada-poller : Copyright © 2019 Chindit
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * First generated : 07/17/2019 at 20:14
 */

namespace App\Service;


use App\Entity\PollData;
use Symfony\Component\Process\Process;

class CpuPoller implements PollerInterface
{
    public function getName(): string
    {
        $name = explode('\\', __CLASS__);

        return end($name) ?: '';
    }

    public function poll(): array
    {
	    return array_merge($this->getCpuUsage(), $this->getCpuLoad(), $this->getProcesses());
    }

    private function getCpuUsage(): array
    {
        $cpu = [];

        $cpuProcess = new Process(['mpstat']);
        $cpuProcess->run();

        $cpuStatsLines = explode("\n", $cpuProcess->getOutput());
        $cpuStats = explode(' ', $cpuStatsLines[3]);

        $cpuStats = array_filter($cpuStats, function ($value) {
           return !empty(trim($value));
        });

        // Reorder keys
        $cpuStats = array_values($cpuStats);

        $dif = [];
        $dif['user'] = $cpuStats[2];
        $dif['nice'] = $cpuStats[3];
        $dif['sys'] = $cpuStats[4];
        $dif['idle'] = end($cpuStats);

        foreach ($dif as $x => $y) {
            $cpu[] = (new PollData(sprintf('cpu_%s', $x), (float)$y))->setCategory('cpu')->setName('CPU Utilization');
        }

        return $cpu;
    }

    private function getCpuLoad(): array
    {
        list($loadOne, $loadFive, $loadFifteen) = sys_getloadavg();

        return [
	        (new PollData('load_1', $loadOne))->setCategory('loadavg')->setName('Load average 1 min'),
	        (new PollData('load_5', $loadFive))->setCategory('loadavg')->setName('Load average 5 min'),
	        (new PollData('load_15', $loadFifteen))->setCategory('loadavg')->setName('Load average 15 min'),
        ];
    }

	private function getProcesses(): array
	{
		$result = [];

		/** @var string[] $cpuInfo */
		$cpuInfo = file('/proc/stat');

		foreach ($cpuInfo as $line)
		{
			if (strpos('proc', $line) === 0)
			{
				$chunks = explode(' ', trim($line));

				switch ($chunks[ 0 ])
				{
					case 'processes':
						$result[] = (new PollData('processes', (float)$chunks[ 1 ]))->setCategory('processes')->setName('Number of active processes');
						break;
					case 'procs_running':
						$result[] = (new PollData('procs_running', (float)$chunks[ 1 ]))->setCategory('processes')->setName('Number of running processes');
						break;
				}
			}
		}

		return $result;
	}
}
