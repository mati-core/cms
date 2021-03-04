<?php

declare(strict_types=1);

namespace MatiCore\Cms\Components;


use Nette\Application\UI\Control;
use Nette\Application\UI\Template;

/**
 * Class FaviconControl
 * @package MatiCore\Cms\Components
 */
class FontAwesomeKitControl extends Control
{

	/**
	 * @var string|null
	 */
	private string|null $faviconPackId;

	/**
	 * FaviconControl constructor.
	 * @param string $faviconPackId
	 */
	public function __construct(string $faviconPackId)
	{
		$this->faviconPackId = $faviconPackId;
	}

	/**
	 * @throws FontAwesomeKitException
	 */
	public function render(): void
	{
		if ($this->faviconPackId === null) {
			throw new FontAwesomeKitException('Font Awesome Kit ID doesn\'t set! ');
		}

		/** @var Template $template */
		$template = $this->template;
		$template->faviconPackId = $this->faviconPackId;
		$template->setFile(__DIR__ . '/default.latte');
		$template->render();
	}

}