<?php

declare(strict_types=1);

namespace MatiCore\Cms\Dashboard;


use MatiCore\Cms\CmsHelper;
use Nette\Application\UI\Control;

/**
 * Class DashboardBlockControl
 * @package MatiCore\Cms\Dashboard
 */
class DashboardBlockControl extends Control
{

	/**
	 * @var string
	 */
	protected $blockName;

	/**
	 * @var string
	 */
	protected $right = 'cms';

	/**
	 * @var string
	 */
	protected $templateFile;

	/**
	 * @return string
	 */
	public function getBlockName(): string
	{
		return $this->blockName;
	}

	public function render(): void
	{
		$presenter = $this->getPresenter();

		$show = false;
		if ($presenter !== null) {
			$show = $presenter->checkAccess($this->right);
		}

		$template = $this->template;
		$template->setFile($this->templateFile);
		$template->show = $show;
		$template->systemStatus = CmsHelper::getCMSStatus();
		$template->systemVersion = CmsHelper::getCMSVersion();
		$template->systemVersionDate = CmsHelper::getCMSVersionDate();
		$template->systemUpdateAvailable = CmsHelper::getAvaiableCMSUpdate();
		$template->render();
	}

}