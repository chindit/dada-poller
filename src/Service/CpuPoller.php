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

        /** @var string[] $startCpu */
        $startCpu = file('/proc/stat');
        usleep(1000);
        /** @var string[] $endCpu */
        $endCpu = file('/proc/stat');

        /**
         * Line 1 in total of all CPUs
         *
         * Following lines (ignored) are load detail CPU by CPU
         */
        $info1 = explode(' ', preg_replace('!cpu +!', '', $startCpu[0] ?? '') ?: '');
        $info2 = explode(' ', preg_replace('!cpu +!', '', $endCpu[0] ?? '') ?: '');
        $dif = array();
        $dif['user'] = (int)$info2[0] - (int)$info1[0];
        $dif['nice'] = (int)$info2[1] - (int)$info1[1];
        $dif['sys'] = (int)$info2[2] - (int)$info1[2];
        $dif['idle'] = (int)$info2[3] - (int)$info1[3];
        $total = array_sum($dif);

        foreach ($dif as $x => $y) {
            $cpu[] = (new PollData(sprintf('cpu_%s', $x), round($y / $total * 100, 1)))->setCategory('cpu');
        }

        return $cpu;
    }

    private function getCpuLoad(): array
    {
        list($loadOne, $loadFive, $loadFifteen) = sys_getloadavg();

        return [
            (new PollData('load_1', $loadOne))->setCategory('loadavg'),
            (new PollData('load_5', $loadFive))->setCategory('loadavg'),
            (new PollData('load_15', $loadFifteen))->setCategory('loadavg'),
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
						$result[] = (new PollData('processes', (float)$chunks[ 1 ]))->setCategory('processes');
						break;
					case 'procs_running':
						$result[] = (new PollData('procs_running', (float)$chunks[ 1 ]))->setCategory('processes');
						break;
				}
			}
		}

		return $result;
	}
}
