<?php

declare(strict_types=1);

namespace MatiCore\Cms\Dashboard;


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
	protected string $blockName;

	/**
	 * @var string
	 */
	protected string $right = 'cms';

	/**
	 * @var string
	 */
	protected string $templateFile;

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
		$template->render();
	}

}