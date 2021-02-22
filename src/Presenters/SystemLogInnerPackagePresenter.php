<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette\Utils\DateTime;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Tracy\Debugger;

/**
 * Class SystemLogInnerPackagePresenter
 * @package App\AdminModule\Presenters
 */
class SystemLogInnerPackagePresenter extends BaseAdminPresenter
{

	/**
	 * @var string
	 */
	protected $pageRight = 'super-admin';

	public function actionDefault(): void
	{
		$logDirectory = Debugger::$logDirectory;

		$criticalErrors = [];
		$exceptionErrors = [];
		$warningErrors = [];
		$infoErrors = [];

		if ($logDirectory !== null && is_dir($logDirectory)) {
			foreach (Finder::findFiles('*.html')->in($logDirectory) as $file) {
				$filePath = (string) $file;
				if (
					preg_match('/^.*\/(?<type>[a-z]+)--(?<date>\d{4}-\d{2}-\d{2})--(?<time>\d{2}-\d{2})--(?<hash>\w+)\.html$/', $filePath, $match)
					&& isset($match['type'], $match['date'], $match['time'], $match['hash'])
				) {

					$fileContent = FileSystem::read($filePath);

					$title = 'Uknown error';

					if(preg_match('/.*<title>(?<title>.*)<\/title>.*/', $fileContent, $m) && isset($m['title'])){
						$title = $m['title'];
					}

					if ($match['type'] === 'critical') {
						$criticalErrors[] = [
							'path' => $filePath,
							'title' => $title,
							'date' => DateTime::from($match['date'] . ' ' . str_replace('-', ':', $match['time']) . ':00'),
							'hash' => $match['hash'],
						];
					} elseif ($match['type'] === 'exception') {
						$exceptionErrors[] = [
							'path' => $filePath,
							'title' => $title,
							'date' => DateTime::from($match['date'] . ' ' . str_replace('-', ':', $match['time']) . ':00'),
							'hash' => $match['hash'],
						];
					} elseif ($match['type'] === 'warning') {
						$warningErrors[] = [
							'path' => $filePath,
							'title' => $title,
							'date' => DateTime::from($match['date'] . ' ' . str_replace('-', ':', $match['time']) . ':00'),
							'hash' => $match['hash'],
						];
					} else {
						$infoErrors[] = [
							'path' => $filePath,
							'title' => $title,
							'date' => DateTime::from($match['date'] . ' ' . str_replace('-', ':', $match['time']) . ':00'),
							'hash' => $match['hash'],
						];
					}
				}
			}
		}

		usort($criticalErrors, function ($a, $b) {
			if ($a['date'] == $b['date']) {
				return 0;

			}

			return ($a['date'] < $b['date']) ? -1 : 1;
		});

		usort($exceptionErrors, function ($a, $b) {
			if ($a['date'] == $b['date']) {
				return 0;

			}

			return ($a['date'] < $b['date']) ? -1 : 1;
		});

		usort($warningErrors, function ($a, $b) {
			if ($a['date'] == $b['date']) {
				return 0;

			}

			return ($a['date'] < $b['date']) ? -1 : 1;
		});

		usort($infoErrors, function ($a, $b) {
			if ($a['date'] == $b['date']) {
				return 0;

			}

			return ($a['date'] < $b['date']) ? -1 : 1;
		});

		$this->template->criticalErrors = $criticalErrors;
		$this->template->exceptionErrors = $exceptionErrors;
		$this->template->warningErrors = $warningErrors;
		$this->template->infoErrors = $infoErrors;
		$this->template->logCount = count($criticalErrors) 
			+ count($exceptionErrors) 
			+ count($warningErrors) 
			+ count($infoErrors);
	}

	/**
	 * @param string $hash
	 */
	public function actionShow(string $hash): void
	{
		$logDirectory = Debugger::$logDirectory;

		$founded = false;

		if ($logDirectory !== null && is_dir($logDirectory)) {
			foreach (Finder::findFiles('*.html')->in($logDirectory) as $file) {
				$filePath = (string) $file;

				if (
					preg_match('/^.*\/(?<type>[a-z]+)--(?<date>\d{4}-\d{2}-\d{2})--(?<time>\d{2}-\d{2})--(?<hash>\w+)\.html$/', $filePath, $match)
					&& isset($match['type'], $match['date'], $match['time'], $match['hash'])
					&& $match['hash'] === $hash
				) {
					if($this->getHttpResponse()->isSent() === false) {
						echo FileSystem::read($filePath);
						die;
					}else{
						$founded = true;
						$this->flashMessage('cms.error.headerSend', 'error');
					}
				}
			}
		}

		if($founded === false){
			$this->flashMessage('cms.systemLog.notFound', 'error');
		}
	}

	/**
	 * @param string $hash
	 */
	public function handleDelete(string $hash): void
	{
		$logDirectory = Debugger::$logDirectory;

		$founded = false;

		if ($logDirectory !== null && is_dir($logDirectory)) {
			foreach (Finder::findFiles('*.html')->in($logDirectory) as $file) {
				$filePath = (string) $file;

				if (
					preg_match('/^.*\/(?<type>[a-z]+)--(?<date>\d{4}-\d{2}-\d{2})--(?<time>\d{2}-\d{2})--(?<hash>\w+)\.html$/', $filePath, $match)
					&& isset($match['type'], $match['date'], $match['time'], $match['hash'])
					&& $match['hash'] === $hash
				) {
					FileSystem::delete($filePath);

					$this->actionDefault();
					
					break;
				}
			}
		}

		$this->redrawControl('pageContent');
	}

}