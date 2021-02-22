<?php

namespace MatiCore\Cms\Dashboard;


use Nette\Application\UI\Control;
use Nette\ComponentModel\IComponent;

class DashboardControl extends Control
{

	/**
	 * @var array(array<string, DashboardBlockControl>)
	 */
	private $blocks = [];

	public function render(): void
	{
		$this->sortBlocks();

		$template = $this->template;
		$this->template->dashboardBlocks = [];

		foreach ($this->blocks as $block) {
			$this->template->dashboardBlocks[] = $block['control']->getBlockName();
		}

		$this->template->render(__DIR__ . '/default.latte');
	}

	/**
	 * @param string $blockName
	 * @return DashboardBlockControl|null
	 */
	public function createComponent(string $blockName): ?IComponent
	{
		foreach ($this->blocks as $block) {
			if ($block['control'] instanceof DashboardBlockControl && $block['control']->getBlockName() === $blockName) {
				return $block['control'];
			}
		}

		return null;
	}

	/**
	 * @param DashboardBlockControl $blockControl
	 * @param int $priority
	 */
	public function addBlock(DashboardBlockControl $blockControl, int $priority): void
	{
		$this->blocks[] = [
			'priority' => $priority,
			'control' => $blockControl,
		];
	}

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
