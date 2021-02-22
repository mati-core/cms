<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;


use App\Presenters\BasePresenter;
use Baraja\Doctrine\EntityManager;
use MatiCore\Cms\Nav\NavControl;
use MatiCore\Menu\MenuPresenterTrait;
use MatiCore\User\BaseUser;
use MatiCore\User\StorageIdentity;
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
	 * @var NavControl
	 * @inject
	 */
	public $navControl;

	/**
	 * @var EntityManager
	 * @inject
	 */
	public $entityManager;

	use UserPresenterAccessTrait;
	
	use MenuPresenterTrait;

	public function beforeRender()
	{
		if($this->getUser()->getIdentity() !== null && $this->getUser()->getIdentity() instanceof StorageIdentity){
			$this->template->user = $this->getUser()->getIdentity()?->getUser();
		}else{
			$this->template->user = null;
		}
		$this->template->cmsMenu = $this->getMenuListByGroup('cms-main');
	}

	/**
	 * @return NavControl
	 */
	public function createComponentNav(): NavControl
	{
		return $this->navControl;
	}

}