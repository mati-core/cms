<?php

declare(strict_types=1);

namespace MatiCore\Cms\Components;

/**
 * Trait FontAwesomeKit
 * @package MatiCore\Cms\Components
 */
trait FontAwesomeKit
{

	/**
	 * @var FontAwesomeKitControl
	 * @inject
	 */
	public FontAwesomeKitControl $fontAwesomeKitControl;

	/**
	 * @return FontAwesomeKitControl
	 */
	public function createComponentFontAwesomeKit(): FontAwesomeKitControl
	{
		return $this->fontAwesomeKitControl;
	}

}