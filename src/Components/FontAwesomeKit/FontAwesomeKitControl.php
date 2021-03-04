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
	 * @param array<null|string> $config
	 * @throws FontAwesomeKitException
	 */
	public function __construct(array $config)
	{
		if (!isset($config['kitId'], $config['css'], $config['js'])) {
			throw new FontAwesomeKitException('Font Awesome Kit - bad configuration! ');
		}

		$this->config = $config;
	}

	/**
	 * @throws FontAwesomeKitException
	 */
	public function render(): void
	{
		if (
			$this->config['kitId'] === null
			&& $this->config['css'] === null
			&& $this->config['js'] === null
		) {
			throw new FontAwesomeKitException('Font Awesome Kit ID doesn\'t set! ');
		}

		/** @var Template $template */
		$template = $this->template;
		$template->kitId = $this->config['kitId'];
		$template->cssUrl = $this->config['css'];
		$template->jsUrl = $this->config['js'];
		$template->setFile(__DIR__ . '/default.latte');
		$template->render();
	}

}