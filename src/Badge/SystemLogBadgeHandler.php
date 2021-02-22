<?php

declare(strict_types=1);

namespace MatiCore\Cms;


use MatiCore\Menu\Entity\MenuBadge;
use MatiCore\Menu\IMenuBadgeHandler;
use Nette\Utils\Finder;
use Tracy\Debugger;

/**
 * Class SystemLogBadgeHandler
 * @package MatiCore\Cms
 */
class SystemLogBadgeHandler implements IMenuBadgeHandler
{

	/**
	 * @return array|MenuBadge[]
	 */
	public function getBadge(): array
	{
		$logDirectory = Debugger::$logDirectory;

		$criticalErrorsCount = 0;
		$warningErrorsCount = 0;
		$infoErrorsCount = 0;

		if ($logDirectory !== null && is_dir($logDirectory)) {
			foreach (Finder::findFiles('*.html')->in($logDirectory) as $file) {
				$filePath = (string) $file;
				if (
					preg_match('/^.*\/(?<type>[a-z]+)--(?<date>\d{4}-\d{2}-\d{2})--(?<time>\d{2}-\d{2})--(?<hash>\w+)\.html$/', $filePath, $match)
					&& isset($match['type'], $match['date'], $match['time'], $match['hash'])
				) {
					if ($match['type'] === 'critical' || $match['type'] === 'exception') {
						$criticalErrorsCount++;
					} elseif ($match['type'] === 'warning') {
						$warningErrorsCount++;
					} else {
						$infoErrorsCount++;
					}
				}
			}
		}

		$ret = [];

		if ($criticalErrorsCount > 0) {
			$ret[] = new MenuBadge((string) $criticalErrorsCount, MenuBadge::TYPE_DANGER);
		}

		if ($warningErrorsCount > 0) {
			$ret[] = new MenuBadge((string) $warningErrorsCount, MenuBadge::TYPE_WARNING);
		}

		if ($infoErrorsCount > 0) {
			$ret[] = new MenuBadge((string) $infoErrorsCount, MenuBadge::TYPE_INFO);
		}

		return $ret;
	}

}