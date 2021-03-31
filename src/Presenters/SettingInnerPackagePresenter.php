<?php

declare(strict_types=1);


namespace App\AdminModule\Presenters;

use MatiCore\Form\FormFactoryTrait;

/**
 * Class UserInnerPackagePresenter
 * @package App\AdminModule\Presenters
 */
class SettingInnerPackagePresenter extends BaseAdminPresenter
{

	/**
	 * @var string
	 */
	protected string $pageRight = 'cms__settings';

	use FormFactoryTrait;

}