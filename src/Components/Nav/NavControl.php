<?php

declare(strict_types=1);

namespace MatiCore\Cms\Nav;


use Nette\Application\UI\Control;
use Nette\ComponentModel\IComponent;

/**
 * Class NavControl
 * @package MatiCore\Cms\Nav
 */
class NavControl extends Control
{

	/**
	 * @var array(array<string, NavBlockControl>)
	 */
	private $blocks = [];

	public function render(): void
	{
		$this->sortBlocks();

		$template = $this->template;
		$template->setFile(__DIR__ . '/default.latte');
		$template->blocks = [];

		foreach ($this->blocks as $block) {
			$template->blocks[] = $block['control']->getBlockName();
		}

		$template->render();
	}

	/**
	 * @param string $blockName
	 * @return NavBlockControl|null
	 */
	public function createComponent(string $blockName): ?IComponent
	{
		foreach ($this->blocks as $block) {
			if ($block['control']->getBlockName() === $blockName) {
				return $block['control'];
			}
		}

		return null;
	}

	/**
	 * @param NavBlockControl $blockControl
	 * @param int $priority
	 */
	public function addBlock(NavBlockControl $blockControl, int $priority): void
	{
		$this->blocks[] = [
			'priority' => $priority,
			'control' => $blockControl,
		];
	}

	/**
	 * Sorting blocks
	 */
	private function sortBlocks(): void
	{
		usort($this->blocks, function (array $a, array $b): int {
			if ($a['priority'] === $b['priority']) {
				return 0;
			}

			if ($a['priority'] > $b['priority']) {
				return -1;
			}

			return 1;
		});
	}

}