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
	 * @var array<string|null>
	 */
	private array $config;

	/**
	 * FontAwesomeKitControl constructor.
	 * @param array<null|string|bool> $config
	 */
	public function __construct(array $config)
	{
		$this->config = $config;
	}

	/**
	 * @throws FontAwesomeKitException
	 */
	public function render(): void
	{
		$kitId = $this->config['kitId'] ?? null;
		$cssUrl = $this->config['css'] ?? null;
		$jsUrl = $this->config['js'] ?? null;
		$cssAutoLoad = $this->config['cssAutoLoad'] ?? true;

		if (
			$kitId === null
			&& $cssUrl === null
			&& $jsUrl === null
		) {
			throw new FontAwesomeKitException('Font Awesome Kit ID doesn\'t set!');
		}

		/** @var Template $template */
		$template = $this->template;
		$template->kitId = $kitId;
		$template->cssUrl = $cssUrl;
		$template->jsUrl = $jsUrl;
		$template->cssAutoLoad = $cssAutoLoad ? 'true' : 'false';
		$template->setFile(__DIR__ . '/default.latte');
		$template->render();
	}

}