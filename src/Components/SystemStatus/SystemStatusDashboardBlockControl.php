<?php

declare(strict_types=1);

namespace MatiCore\Cms\Dashboard;

/**
 * Class SystemStatusDashboardBlockControl
 * @package MatiCore\Cms\Dashboard
 */
class SystemStatusDashboardBlockControl extends DashboardBlockControl
{

	/**
	 * @var string
	 */
	protected $blockName = 'systemStatus';

	/**
	 * @var string
	 */
	protected $right = 'page__dashboard__system_status';

	/**
	 * @var string 
	 */
	protected $templateFile = __DIR__ . '/default.latte';

}