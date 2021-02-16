<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

/**
 * Class HomepagePresenter
 * @package App\AdminModule\Presenters
 */
class HomepageInnerPackagePresenter extends BaseAdminPresenter
{

	/**
	 *
	 */
	public function actionDefault(): void
	{
		bdump($this->checkAccess('test'));
	}

}
