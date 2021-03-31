<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use MatiCore\Cms\Dashboard\DashboardControl;

/**
 * Class HomepagePresenter
 * @package App\AdminModule\Presenters
 */
class HomepageInnerPackagePresenter extends BaseAdminPresenter
{

	/**
	 * @var string 
	 */
	protected string $pageRight = 'page__dashboard';

	/**
	 * @var DashboardControl
	 * @inject
	 */
	public DashboardControl $dashboardControl;

	/**
	 * @return DashboardControl
	 */
	public function createComponentDashboard(): DashboardControl
	{
		return $this->dashboardControl;
	}

}
