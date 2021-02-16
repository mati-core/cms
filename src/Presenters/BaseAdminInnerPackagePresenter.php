<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;


use App\Presenters\BasePresenter;
use Baraja\Doctrine\EntityManager;
use MatiCore\User\UserPresenterAccessTrait;

/**
 * Class BaseAdminPresenter
 * @package App\AdminModule\Presenters
 */
class BaseAdminInnerPackagePresenter extends BasePresenter
{

	/**
	 * @var string
	 */
	protected $pageRight = 'cms';

	/**
	 * @var EntityManager
	 * @inject
	 */
	public $entityManager;

	use UserPresenterAccessTrait;

	public function startup()
	{
		parent::startup();
		$this->template->user = $this->getUser()->getIdentity()?->getUser();
	}



}