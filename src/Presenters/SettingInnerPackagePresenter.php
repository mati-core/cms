<?php

declare(strict_types=1);


namespace App\AdminModule\Presenters;

use Baraja\Doctrine\EntityManagerException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use MatiCore\Form\FormFactoryTrait;
use MatiCore\User\Entity\User;
use MatiCore\User\Entity\UserGroup;
use MatiCore\User\UserManager;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

/**
 * Class UserInnerPackagePresenter
 * @package App\AdminModule\Presenters
 */
class SettingInnerPackagePresenter extends BaseAdminPresenter
{

	/**
	 * @var string
	 */
	protected $pageRight = 'cms__settings';

	use FormFactoryTrait;

}