<?php

declare(strict_types=1);


namespace MatiCore\Cms;

use Nette\Utils\DateTime;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Tracy\Debugger;

/**
 * Class CmsHelper
 * @package MatiCore\Cms
 */
class CmsHelper
{

	public const CMS_STATUS_CRITICAL = 0;
	public const CMS_STATUS_WARNING = 1;
	public const CMS_STATUS_UPDATE = 2;
	public const CMS_STATUS_OK = 3;

	/**
	 * @return int
	 */
	public static function getCMSStatus(): int
	{
		static $status;

		if ($status === null) {
			$criticalErrorsCount = 0;
			$exceptionErrorsCount = 0;
			$warningErrorsCount = 0;
			$infoErrorsCount = 0;

			$logDir = Debugger::$logDirectory;

			if ($logDir !== null && is_dir($logDir)) {
				foreach (Finder::findFiles('*.html')->in($logDir) as $file) {
					if (
						preg_match('/^.*\/(?<type>[a-z]+)--(?<date>\d{4}-\d{2}-\d{2})--(?<time>\d{2}-\d{2})--(?<hash>\w+)\.html$/', (string) $file, $match)
						&& isset($match['type'], $match['date'], $match['time'], $match['hash'])
					) {
						if ($match['type'] === 'critical') {
							$criticalErrorsCount++;
						} elseif ($match['type'] === 'exception') {
							$exceptionErrorsCount++;
						} elseif ($match['type'] === 'warning') {
							$warningErrorsCount++;
						} else {
							$infoErrorsCount++;
						}
					}
				}
			}

			if (
				$criticalErrorsCount > 5
				|| $exceptionErrorsCount > 5
				|| $warningErrorsCount > 10
				|| $infoErrorsCount > 20
			) {
				$status = self::CMS_STATUS_CRITICAL;
			} elseif (
				$criticalErrorsCount > 2
				|| $exceptionErrorsCount > 2
				|| $warningErrorsCount > 5
				|| $infoErrorsCount > 10
			) {
				$status = self::CMS_STATUS_WARNING;
			} elseif (self::getAvaiableCMSUpdate() !== null) {
				$status = self::CMS_STATUS_UPDATE;
			} else {
				$status = self::CMS_STATUS_OK;
			}
		}

		return $status;
	}

	/**
	 * @return string
	 */
	public static function getCMSVersion(): string
	{
		static $version;

		if ($version === null) {
			$version = self::loadCMSVersion();
		}

		return $version;
	}

	/**
	 * @return DateTime|null
	 */
	public static function getCMSVersionDate(): ?DateTime
	{
		static $versionDate;

		if ($versionDate === null) {
			$versionDate = self::loadCMSVersionDate();
		}

		return $versionDate;
	}

	/**
	 * @return string|null
	 */
	public static function getAvaiableCMSUpdate(): ?string
	{
		return null;
	}

	/**
	 * @return string
	 */
	private static function loadCMSVersion(): string
	{
		try {
			$composerData = self::getComposerData();

			if (isset($composerData['packages'])) {
				foreach ($composerData['packages'] as $package) {
					if (isset($package['name'], $package['version']) && $package['name'] === 'mati-core/cms') {
						return $package['version'];
					}
				}
			}

			if (isset($composerData['packages-dev'])) {
				foreach ($composerData['packages-dev'] as $package) {
					if (isset($package['name'], $package['version']) && $package['name'] === 'mati-core/cms') {
						return $package['version'];
					}
				}
			}
		} catch (JsonException $e) {
			Debugger::log($e);
		}

		return 'Unknown';
	}

	/**
	 * @return DateTime|null
	 */
	private static function loadCMSVersionDate(): ?DateTime
	{
		try {
			$composerData = self::getComposerData();

			if (isset($composerData['packages'])) {
				foreach ($composerData['packages'] as $package) {
					if (isset($package['name'], $package['version'], $package['time']) && $package['name'] === 'mati-core/cms') {
						return DateTime::from($package['time']);
					}
				}
			}

			if (isset($composerData['packages-dev'])) {
				foreach ($composerData['packages-dev'] as $package) {
					if (isset($package['name'], $package['version'], $package['time']) && $package['name'] === 'mati-core/cms') {
						return DateTime::from($package['time']);
					}
				}
			}
		} catch (JsonException | \Exception $e) {
			Debugger::log($e);
		}

		return null;
	}

	/**
	 * @return array(array<string>|string)
	 * @throws \Nette\Utils\JsonException
	 */
	public static function getComposerData(): array
	{
		if (!is_file(__DIR__ . '/../../../../../composer.lock')) {
			return [];
		}

		return Json::decode(
			FileSystem::read(__DIR__ . '/../../../../../composer.lock'),
			Json::FORCE_ARRAY
		);
	}

}
