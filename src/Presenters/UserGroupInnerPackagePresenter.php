<?php

declare(strict_types=1);


namespace App\AdminModule\Presenters;

use Baraja\Doctrine\EntityManagerException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use MatiCore\Form\FormFactoryTrait;
use MatiCore\User\UserGroup;
use MatiCore\User\UserGroupException;
use MatiCore\User\UserRole;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

/**
 * Class UserGroupInnerPackagePresenter
 * @package App\AdminModule\Presenters
 */
class UserGroupInnerPackagePresenter extends BaseAdminPresenter
{

	/**
	 * @var string
	 */
	protected string $pageRight = 'cms__users__groups';

	use FormFactoryTrait;

	/**
	 * @var UserGroup|null
	 */
	private UserGroup|null $editedUserGroup;

	/**
	 * @var UserRole|null
	 */
	private UserRole|null $editedRole;

	public function actionDefault(): void
	{
		$this->template->userGroups = $this->userManager->get()->getUserGroups();
		$this->template->roles = $this->userManager->get()->getUserRoles();
	}

	/**
	 * @param string $id
	 * @throws AbortException
	 */
	public function actionAccess(string $id): void
	{
		try {
			$this->editedUserGroup = $this->userManager->get()->getUserGroupById($id);
			$this->template->group = $this->editedUserGroup;
			$this->template->roles = $this->userManager->get()->getUserRoles();
		} catch (NoResultException|NonUniqueResultException) {
			$this->flashMessage('Požadované skupina uživatelů neexistuje.', 'error');
			$this->redirect('default');
		}
	}

	/**
	 * @param string $id
	 * @throws AbortException
	 */
	public function actionEdit(string $id): void
	{
		try {
			$this->editedUserGroup = $this->userManager->get()->getUserGroupById($id);
			$this->template->group = $this->editedUserGroup;
		} catch (NoResultException|NonUniqueResultException) {
			$this->flashMessage('Požadované skupina uživatelů neexistuje.', 'error');
			$this->redirect('default');
		}
	}

	/**
	 * @param string $id
	 * @throws AbortException
	 */
	public function handleDelete(string $id): void
	{
		try {
			$group = $this->userManager->get()->getUserGroupById($id);

			try {
				$roles = $group->getRoles();
				foreach ($roles as $role) {
					$group->removeRole($role);
				}

				$this->entityManager->remove($group);
				$this->entityManager->flush();

				$this->flashMessage('Skupina byla úspěšně odebrána.');
			} catch (EntityManagerException) {
				$this->flashMessage('Skupinu nelze odebrat, protože je používána.', 'error');
			}
		} catch (NoResultException|NonUniqueResultException) {
			$this->flashMessage('Požadovaná skupina neexistuje.', 'error');
		}

		$this->redirect('default');
	}

	/**
	 * @param string $id
	 * @throws AbortException
	 */
	public function handleDefault(string $id): void
	{
		try {
			$newDefaultGroup = $this->userManager->get()->getUserGroupById($id);

			try {
				$groups = $this->userManager->get()->getUserGroups();
				foreach ($groups as $group) {
					$group->setDefault($group->getId() === $newDefaultGroup->getId());
				}

				$this->entityManager->flush();
			} catch (EntityManagerException) {
				$this->flashMessage('Při ukládání do databáze nastala chyba.', 'error');
			}
		} catch (NoResultException|NonUniqueResultException) {
			$this->flashMessage('Požadovaná skupina uživatel neexistuje.', 'error');
		}

		$this->redirect('default');
	}

	/**
	 * @return Form
	 */
	public function createComponentCreateUserGroupForm(): Form
	{
		$form = $this->formFactory->create();

		$form->addText('name', 'Název')
			->setRequired('Zadejte název skupiny uživatel');

		$roles = [];
		foreach ($this->userManager->get()->getUserRoles() as $role) {
			$roles[$role->getId()] = $role->getName();
		}

		$form->addCheckboxList('roles', 'Role', $roles);

		$form->addSubmit('submit', 'Create');

		$form->onSuccess[] = function (Form $form, ArrayHash $values): void {
			try {
				$group = new UserGroup($values->name);

				$this->entityManager->persist($group);

				foreach ($values->roles as $roleId) {
					$role = $this->userManager->get()->getRoleById($roleId);
					$group->addRole($role);
				}

				$this->entityManager->flush($group);

				$this->flashMessage('Skupina uživatelů byla úspěšně přidána.', 'success');

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
	 * @throws UserGroupException
	 */
	public function createComponentEditUserGroupForm(): Form
	{
		if ($this->editedUserGroup === null) {
			throw new UserGroupException('Edited userGroup is null');
		}

		$form = $this->formFactory->create();

		$form->addText('name', 'Název')
			->setDefaultValue($this->editedUserGroup->getName())
			->setRequired('Zadejte název skupiny uživatel');


		$roles = [];
		foreach ($this->userManager->get()->getUserRoles() as $role) {
			$roles[$role->getId()] = $role->getName();
		}

		$activeRoles = [];
		foreach ($this->editedUserGroup->getRoles() as $role) {
			$activeRoles[] = $role->getId();
		}

		$form->addCheckboxList('roles', 'Role', $roles)
			->setDefaultValue($activeRoles);

		$form->addSubmit('submit', 'Save');

		$form->onSuccess[] = function (Form $form, ArrayHash $values): void {
			try {
				$group = $this->editedUserGroup;
				$group->setName($values->name);

				foreach ($group->getRoles() as $r) {
					$group->removeRole($r);
				}

				$this->entityManager->flush($group);

				foreach ($values->roles as $roleId) {
					try {
						$role = $this->userManager->get()->getRoleById($roleId);
						$group->addRole($role);
					} catch (NoResultException|NonUniqueResultException) {
						$this->flashMessage('Některé role se nepodařilo přiřadit ke skupině.', 'warning');
					}
				}

				$this->entityManager->flush($group);

				$this->flashMessage('Změny byly úspěšně uloženy.', 'success');

				$this->redirect('default');
			} catch (EntityManagerException $e) {
				Debugger::log($e);

				$this->flashMessage('Při ukládání do databáze nastala chyba.', 'error');
			}
		};

		return $form;
	}

}