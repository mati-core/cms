<?php

declare(strict_types=1);


namespace App\AdminModule\Presenters;

use Baraja\Doctrine\EntityManagerException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use MatiCore\Form\FormFactoryTrait;
use MatiCore\User\Entity\UserGroup;
use MatiCore\User\Entity\UserRole;
use MatiCore\User\UserManagerAccessor;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\Strings;
use Tracy\Debugger;

/**
 * Class UserGroupInnerPackagePresenter
 * @package App\AdminModule\Presenters
 */
class UserGroupInnerPackagePresenter extends BaseAdminPresenter
{

	use FormFactoryTrait;

	/**
	 * @var UserGroup|null
	 */
	private $editedUserGroup;

	/**
	 * @var UserRole|null
	 */
	private $editedRole;

	public function actionDefault(): void
	{
		$this->template->userGroups = $this->userManager->get()->getGroups();
		$this->template->roles = $this->userManager->get()->getRoles();
	}

	/**
	 * @param string $id
	 */
	public function actionAccess(string $id): void
	{
		try {
			$this->editedUserGroup = $this->userManager->get()->getGroupById($id);
			$this->template->group = $this->editedUserGroup;
			$this->template->roles = $this->userManager->get()->getRoles();
		} catch (NoResultException|NonUniqueResultException $e) {
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
			$this->editedUserGroup = $this->userManager->get()->getGroupById($id);
			$this->template->group = $this->editedUserGroup;
		} catch (NoResultException|NonUniqueResultException $e) {
			$this->flashMessage('Požadované skupina uživatelů neexistuje.', 'error');
			$this->redirect('default');
		}
	}

	/**
	 * @param string $id
	 */
	public function handleDelete(string $id): void
	{
		try {
			$group = $this->userManager->get()->getGroupById($id);

			try {
				$roles = $group->getPermissionRoles();
				foreach ($roles as $role) {
					$group->removePermissionRole($role);
				}

				$rights = $group->getPermissionRights();
				foreach ($rights as $right) {
					$group->removePermissionRight($right);
				}

				$this->entityManager->remove($group);
				$this->entityManager->flush();

				$this->flashMessage('Skupina byla úspěšně odebrána.', 'info');
			} catch (EntityManagerException $e) {
				$this->flashMessage('Skupinu nelze odebrat, protože je používána.', 'error');
			}
		} catch (NoResultException|NonUniqueResultException $e) {
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
			$newDefaultGroup = $this->userManager->get()->getGroupById($id);

			try {
				$groups = $this->userManager->get()->getGroups();
				foreach ($groups as $group) {
					$group->setDefault($group->getId() === $newDefaultGroup->getId());
				}

				$this->entityManager->flush();
			} catch (EntityManagerException $e) {
				$this->flashMessage('Při ukládání do databáze nastala chyba.', 'error');
			}
		} catch (NoResultException|NonUniqueResultException $e) {
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
		foreach ($this->userManager->get()->getRoles() as $role) {
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
					$group->addPermissionRole($role);
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
	 */
	public function createComponentEditUserGroupForm(): Form
	{
		if ($this->editedUserGroup === null) {
			throw new \Exception('Edited userGroup is null');
		}

		$form = $this->formFactory->create();

		$form->addText('name', 'Název')
			->setDefaultValue($this->editedUserGroup->getName())
			->setRequired('Zadejte název skupiny uživatel');


		$roles = [];
		foreach ($this->userManager->get()->getRoles() as $role) {
			$roles[$role->getId()] = $role->getName();
		}

		$activeRoles = [];
		foreach ($this->editedUserGroup->getPermissionRoles() as $role) {
			$activeRoles[] = $role->getId();
		}

		$form->addCheckboxList('roles', 'Role', $roles)
			->setDefaultValue($activeRoles);

		$form->addSubmit('submit', 'Save');

		$form->onSuccess[] = function (Form $form, ArrayHash $values): void {
			try {
				$group = $this->editedUserGroup;
				$group->setName($values->name);

				foreach ($group->getPermissionRoles() as $r) {
					$group->removePermissionRole($r);
				}

				$this->entityManager->flush($group);

				foreach ($values->roles as $roleId) {
					try {
						$role = $this->userManager->get()->getRoleById($roleId);
						$group->addPermissionRole($role);
					} catch (NoResultException|NonUniqueResultException $e) {
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