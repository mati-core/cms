<?php

declare(strict_types=1);


namespace App\AdminModule\Presenters;

use Baraja\Doctrine\EntityManagerException;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use MatiCore\Form\FormFactoryTrait;
use MatiCore\User\BaseUser;
use MatiCore\User\IUser;
use MatiCore\User\UserPassword;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

/**
 * Class UserInnerPackagePresenter
 * @package App\AdminModule\Presenters
 */
class UserInnerPackagePresenter extends BaseAdminPresenter
{

	/**
	 * @var string
	 */
	protected $pageRight = 'cms__users__accounts';

	use FormFactoryTrait;

	/**
	 * @var BaseUser|IUser|null
	 */
	private $editedUser;

	public function actionDefault(): void
	{
		$this->template->userList = $this->entityManager->getRepository(BaseUser::class)
			->createQueryBuilder('u')
			->select('u')
			->where('u.id != :id')
			->setParameter('id', $this->getUser()->getIdentity()->getId())
			->andWhere('u.username != :adminEmail')
			->setParameter('adminEmail', 'admin@martinolmr.cz')
			->orderBy('u.lastName', 'ASC')
			->addOrderBy('u.firstName', 'ASC')
			->addOrderBy('u.username', 'ASC')
			->getQuery()
			->getResult();
	}

	/**
	 * @param string $id
	 */
	public function actionEdit(string $id): void
	{
		try {
			$this->editedUser = $this->userManager->get()->getUserById($id);
			$this->template->editedUser = $this->editedUser;
		} catch (NonUniqueResultException|NoResultException $e) {
			$this->flashMessage('Požadovaný uživatel neexistuje.', 'error');
			$this->redirect('default');
		}
	}

	/**
	 * @param string $id
	 */
	public function handleDelete(string $id): void
	{
		try {
			$user = $this->userManager->get()->getUserById($id);
			$this->entityManager->remove($user)->flush();
			$this->flashMessage('Uživatel byl úspěšně odebrán.', 'info');
		} catch (NonUniqueResultException|NoResultException $e) {
			$this->flashMessage('Požadovaný uživatel neexistuje.', 'error');
		} catch (EntityManagerException|ForeignKeyConstraintViolationException $e) {
			$this->flashMessage('Tohoto uživatele nezle odebrat, protože jsou na něho vázány položky v databázi', 'error');
		}
		$this->redirect('default');
	}

	/**
	 * @return Form
	 */
	public function createComponentCreateUserForm(): Form
	{
		$form = $this->formFactory->create();

		$form->addText('firstName', 'Jméno')
			->setRequired('Zadejte křestní jméno');

		$form->addText('lastName', 'Příjmení')
			->setRequired('Zadejte příjmení');

		$form->addText('email', 'E-mail')
			->setRequired('Zadejte email');

		$form->addPassword('password', 'Heslo')
			->setRequired('Zadejte heslo')
			->addRule(Form::MIN_LENGTH, 'Heslo musí mít minimálně 8 znaků.', 8);

		$avatarList = [
			'0' => 'Gravatar',
			'1' => 'Style 1',
			'4' => 'Style 2',
			'5' => 'Style 3',
			'2' => 'Style 4',
			'3' => 'Style 5',
		];

		$form->addRadioList('avatar', 'Avatar', $avatarList)
			->setDefaultValue('1');

		$groups = [];
		foreach ($this->userManager->get()->getUserGroups() as $group) {
			$groups[$group->getId()] = $group->getName();
		}

		$form->addSelect('group', 'Group', $groups)
			->setRequired('Select user group')
			->setDefaultValue($this->userManager->get()->getDefaultUserGroup()->getId());

		$form->addText('prefix', 'Name prefix');
		$form->addText('suffix', 'Name suffix');

		$form->addText('phone', 'Phone');

		$form->addCheckbox('active', 'Active')
			->setHtmlId('customSwitch3');

		$form->addSubmit('submit', 'Create');

		$form->onSuccess[] = function (Form $form, ArrayHash $values): void {
			try {
				$group = $this->userManager->get()->getUserGroupById($values->group);

				$userEntity = $this->userManager->get()->getUserEntityName();

				/** @var BaseUser $user */
				$user = new $userEntity($group, $values->email, UserPassword::hash($values->password));
				$user->setEmail($values->email);
				$user->setActive($values->active ? true : false);
				$user->setPhone($values->phone !== '' ? $values->phone : null);
				$user->setNamePrefix($values->prefix !== '' ? $values->prefix : null);
				$user->setNameSuffix($values->suffix !== '' ? $values->suffix : null);
				$user->setFirstName($values->firstName);
				$user->setLastName($values->lastName);

				$path = (string) $values->avatar;

				if ($path === '0') {
					$user->setIconPath(null);
					$user->setGravatarLastCheck(null);
				} else {
					$user->setIconPath($path);
					$user->setGravatarLastCheck(null);
				}

				$user->getIcon();

				$this->entityManager->persist($user)->flush($user);
				$this->flashMessage('Uživatel byl úspěšně vytvořen.', 'success');
				$this->redirect('default');
			} catch (EntityManagerException $e) {
				Debugger::log($e);
				$this->flashMessage('Při ukládání do databáze nastala chyba.', 'error');
			}
		};

		return $form;
	}

	/**
	 * @return Form
	 */
	public function createComponentEditUserForm(): Form
	{
		$form = $this->formFactory->create();

		$form->addText('firstName', 'Jméno')
			->setDefaultValue($this->editedUser->getFirstName())
			->setRequired('Zadejte křestní jméno');

		$form->addText('lastName', 'Příjmení')
			->setDefaultValue($this->editedUser->getLastName())
			->setRequired('Zadejte příjmení');

		$form->addText('email', 'E-mail')
			->setDefaultValue($this->editedUser->getEmail())
			->setRequired('Zadejte email');

		$form->addPassword('password', 'Heslo');

		$groups = [];
		foreach ($this->userManager->get()->getUserGroups() as $group) {
			$groups[$group->getId()] = $group->getName();
		}

		$form->addSelect('group', 'Skupina', $groups)
			->setRequired('Vyberte skupinu uživatele')
			->setDefaultValue($this->editedUser->getGroup()->getId());

		$form->addText('prefix', 'Tituly před jménem');
		$form->addText('suffix', 'Tituly za jménem');

		$form->addText('phone', 'Phone')
			->setDefaultValue($this->editedUser->getPhone() ?? '');

		$form->addCheckbox('active', 'Aktivní')
			->setDefaultValue($this->editedUser->isActive())
			->setHtmlId('customSwitch3');

		$form->addSubmit('submit', 'Save');

		$avatarList = [
			'0' => 'Gravatar',
			'1' => 'Style 1',
			'4' => 'Style 2',
			'5' => 'Style 3',
			'2' => 'Style 4',
			'3' => 'Style 5',
		];

		$userIconPath = $this->editedUser->getIconPath();
		if (!array_key_exists($userIconPath, $avatarList)) {
			$userIconPath = '0';
		}

		$form->addRadioList('avatar', 'Avatar', $avatarList)
			->setDefaultValue($userIconPath);

		$form->onSuccess[] = function (Form $form, ArrayHash $values): void {
			try {
				$group = $this->userManager->get()->getUserGroupById($values->group);

				/** @var BaseUser $user */
				$user = $this->editedUser;
				$user->setGroup($group);
				$user->setActive($values->active ? true : false);
				$user->setPhone($values->phone !== '' ? $values->phone : null);
				$user->setFirstName($values->firstName);
				$user->setLastName($values->lastName);
				$user->setNamePrefix($values->prefix !== '' ? $values->prefix : null);
				$user->setNameSuffix($values->suffix !== '' ? $values->suffix : null);
				$user->setEmail($values->email);
				$user->setUsername($values->email);

				$path = (string) $values->avatar;

				if ($path === '0') {
					$user->setIconPath(null);
					$user->setGravatarLastCheck(null);
				} else {
					$user->setIconPath($path);
					$user->setGravatarLastCheck(null);
				}

				$user->getIcon();

				if ($values->password !== '') {
					$user->setPassword(UserPassword::hash($values->password));
				}

				$this->entityManager->flush($user);
				$this->flashMessage('Uživatel byl úspěšně vytvořen.', 'success');
				$this->redirect('default');
			} catch (EntityManagerException $e) {
				Debugger::log($e);
				$this->flashMessage('Při ukládání do databáze nastala chyba.', 'error');
			}
		};

		return $form;
	}

}