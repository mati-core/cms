<?php

declare(strict_types=1);


namespace App\AdminModule\Presenters;

use Baraja\Doctrine\EntityManagerException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use MatiCore\Form\FormFactoryTrait;
use MatiCore\User\UserGroup;
use MatiCore\User\UserManagerAccessor;
use MatiCore\User\UserRight;
use MatiCore\User\UserRole;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\Strings;
use Tracy\Debugger;

class UserRoleInnerPackagePresenter extends BaseAdminPresenter
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

	/**
	 * @var UserRight|null
	 */
	private $editedRight;

	public function actionDefault(): void
	{
		$this->template->roles = $this->userManager->get()->getUserRoles();
	}

	/**
	 * @param string $id
	 */
	public function actionAccess(string $id): void
	{
		try {
			$this->editedRole = $this->userManager->get()->getRoleById($id);

			$this->template->role = $this->editedRole;
			$this->template->rights = $this->getRights();
		} catch (NoResultException|NonUniqueResultException $e) {
			$this->flashMessage('Požadovaná role neexistuje.', 'error');
			$this->redirect('default');
		}
	}

	/**
	 * @return array
	 */
	private function getRights(): array
	{
		$rights = $this->userManager->get()->getUserRights();

		$list = [];

		foreach ($rights as $right) {
			$parsed = explode('__', $right->getSlug());

			if (count($parsed) === 2) {
				$category = $parsed[0];
				$name = $parsed[1];

				$list[$category]['items'][$name] = [
					'id' => $right->getId(),
					'name' => $right->getName(),
					'slug' => $right->getSlug(),
					'enabled' => $this->editedRole->isRight($right) ? 1 : 0,
					'items' => [],
				];
			} elseif (count($parsed) === 3) {
				$category = $parsed[0];
				$subcategory = $parsed[1];

				$list[$category]['items'][$subcategory]['items'][] = [
					'id' => $right->getId(),
					'name' => $right->getName(),
					'slug' => $right->getSlug(),
					'enabled' => $this->editedRole->isRight($right) ? 1 : 0,
				];
			} else {
				$list['items'][] = [
					'id' => $right->getId(),
					'name' => $right->getName(),
					'slug' => $right->getSlug(),
					'enabled' => $this->editedRole->isRight($right) ? 1 : 0,
				];
			}

		}

		return $list;
	}

	/**
	 * @param string $id
	 * @param string $rightId
	 * @throws AbortException
	 */
	public function actionEditRight(string $id, string $rightId): void
	{
		try {
			$this->editedRole = $this->userManager->get()->getRoleById($id);
			$this->editedRight = $this->userManager->get()->getRightById($rightId);

			$this->template->role = $this->editedRole;
			$this->template->right = $this->editedRight;
		} catch (NoResultException|NonUniqueResultException $e) {
			$this->flashMessage('Požadované oprávnění neexistuje.', 'error');
			$this->redirect('default');
		}
	}

	/**
	 * @param string $id
	 * @param string $rightId
	 */
	public function handleRemoveRight(string $id, string $rightId): void
	{
		try {
			$this->editedRole = $this->userManager->get()->getRoleById($id);
			$this->editedRight = $this->userManager->get()->getRightById($rightId);

			$this->userManager->get()->removeRight($this->editedRight);

			$this->flashMessage('Oprávnění bylo odstraněno.', 'success');
			$this->redirect('access', ['id' => $this->editedRole->getId()]);
		} catch (NoResultException|NonUniqueResultException $e) {
			$this->flashMessage('Požadované oprávnění neexistuje.', 'error');
		}
		$this->redirect('default');
	}

	/**
	 * @param string $id
	 * @throws AbortException
	 */
	public function actionEdit(string $id): void
	{
		try {
			$this->editedRole = $this->userManager->get()->getRoleById($id);
			$this->template->role = $this->editedRole;
		} catch (NoResultException|NonUniqueResultException $e) {
			$this->flashMessage('Požadovaná role nebyla nalezena.', 'error');
			$this->redirect('default');
		}
	}

	/**
	 * @param string $id
	 * @param string $rightId
	 */
	public function handleToogleRight(string $id, string $rightId = null): void
	{
		try {
			$this->editedRole = $this->userManager->get()->getRoleById($id);
			$this->editedRight = $this->userManager->get()->getRightById($rightId);

			if ($this->editedRole->isRight($this->editedRight)) {
				$this->editedRole->removeRight($this->editedRight);
			} else {
				$this->editedRole->addRight($this->editedRight);
			}

			$this->entityManager->flush();

		} catch (NoResultException|NonUniqueResultException $e) {
			$this->flashMessage('Požadované oprávnění neexistuje.', 'error');
			$this->redirect('default');
		} catch (EntityManagerException $e) {
			$this->flashMessage('Při ukládání do databáze nastala chyba.', 'error');
			$this->redirect('default');
		}
	}

	/**
	 * @param string $id
	 */
	public function handleDelete(string $id): void
	{
		try {
			$role = $this->userManager->get()->getRoleById($id);

			$this->userManager->get()->removeRole($role);
			$this->flashMessage('Role ' . $role->getName() . ' byla odstraněna.', 'info');
		} catch (NoResultException|NonUniqueResultException $e) {
			$this->flashMessage('Požadovaná role neexistuje.', 'error');
		} catch (EntityManagerException $e) {
			Debugger::log($e);
			$this->flashMessage('Při mazání nastala chyba: ' . $e->getMessage(), 'error');
		}

		$this->redirect('default');
	}

	/**
	 * @return Form
	 */
	public function createComponentCreateUserRoleForm(): Form
	{
		$form = $this->formFactory->create();

		$form->addText('name', 'Name')
			->setRequired('Zadejte název role');

		$form->addSubmit('submit', 'Přidat');

		$form->onSuccess[] = function (Form $form, ArrayHash $values): void {
			try {
				$role = new UserRole($values->name);

				$this->entityManager->persist($role)->flush($role);

				$this->flashMessage('Role ' . $role->getName() . ' byla úspěšně vytvořena.', 'success');

				$this->redirect('default');
			} catch (EntityManagerException $e) {
				$this->flashMessage('Při ukládání do databáze nastala chyba.', 'error');
			}
		};

		return $form;
	}

	/**
	 * @return Form
	 */
	public function createComponentEditUserRoleForm(): Form
	{
		$form = $this->formFactory->create();

		$form->addText('name', 'Name')
			->setDefaultValue($this->editedRole->getName())
			->setRequired('Zadejte název role');

		$form->addText('slug', 'Slug')
			->setDefaultValue($this->editedRole->getSlug())
			->setRequired('Zadejte slug role');

		$form->addSubmit('submit', 'Save');

		$form->onSuccess[] = function (Form $form, ArrayHash $values): void {
			try {
				$this->editedRole->setName($values->name);
				$this->editedRole->setSlug(Strings::webalize($values->name));

				$this->entityManager->flush($this->editedRole);

				$this->flashMessage('Změny byly úspěšně uloženy.', 'success');

				$this->redirect('default');
			} catch (EntityManagerException $e) {
				$this->flashMessage('Při ukládání do databáze nastala chyba.', 'error');
			}
		};

		return $form;
	}

	/**
	 * @return Form
	 */
	public function createComponentEditUserRightForm(): Form
	{
		$form = $this->formFactory->create();

		$form->addText('name', 'Název')
			->setDefaultValue($this->editedRight->getName())
			->setRequired('Zadejte název oprávnění.');

		$form->addTextArea('description', 'Popis')
			->setDefaultValue($this->editedRight->getDescription());

		$form->addSubmit('submit', 'Save');

		/**
		 * @param Form $form
		 * @param ArrayHash $values
		 */
		$form->onSuccess[] = function (Form $form, ArrayHash $values): void {
			try {
				$this->editedRight->setName($values->name);
				$this->editedRight->setDescription($values->description);

				$this->entityManager->flush($this->editedRight);

				$this->flashMessage('Změny byly úspěšně uloženy.', 'success');
				$this->redirect('access', ['id' => $this->editedRole->getId()]);
			} catch (EntityManagerException $e) {
				$this->flashMessage('Při ukládání do databáze nastala chyba.', 'error');
			}
		};

		return $form;
	}
}
