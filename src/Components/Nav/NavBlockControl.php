<?php

declare(strict_types=1);

namespace MatiCore\Cms\Nav;


/**
 * Class NavBlockControl
 * @package MatiCore\Cms\Nav
 */
abstract class NavBlockControl extends \Nette\Application\UI\Control
{

	/**
	 * @return string
	 */
	abstract public function getBlockName(): string;

}