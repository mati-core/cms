<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;


use App\Presenters\BasePresenter;
use Baraja\Doctrine\EntityManager;
use Baraja\Doctrine\EntityManagerException;
use MatiCore\Form\FormFactoryTrait;
use MatiCore\User\BaseUser;
use MatiCore\User\StorageIdentity;
use MatiCore\User\UserException;
use MatiCore\User\UserPassword;
use MatiCore\User\UserPresenterAccessTrait;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

/**
 * Class AccountInnerPackagePresenter
 * @package App\AdminModule\Presenters
 */
class AccountInnerPackagePresenter extends BaseAdminPresenter
{

	use FormFactoryTrait;

	/**
	 * @return Form
	 * @throws UserException
	 */
	public function createComponentAccountEditForm(): Form
	{
		$identity = $this->user->getIdentity();

		if ($identity instanceof StorageIdentity) {
			$user = $identity->getUser();

			if (!$user instanceof BaseUser) {
				throw new UserException('User identity must be instance of ' . BaseUser::class);
			}
		}

		$form = $this->formFactory->create();

		$form->addText('firstName', 'First name')
			->setDefaultValue($user->getFirstName());

		$form->addText('lastName', 'Last name')
			->setDefaultValue($user->getLastName());

		$form->addText('namePrefix', 'Name prefix')
			->setDefaultValue($user->getNamePrefix());

		$form->addText('nameSuffix', 'Name suffix')
			->setDefaultValue($user->getNameSuffix());

		$form->addEmail('email', 'Email')
			->setDefaultValue($user->getEmail());

		$form->addText('phone', 'Phone')
			->setDefaultValue($user->getPhone());

		$avatarList = [
			'0' => 'Gravatar',
			'1' => 'Style 1',
			'4' => 'Style 2',
			'5' => 'Style 3',
			'2' => 'Style 4',
			'3' => 'Style 5',
		];

		$userIconPath = $user->getIconPath();
		if (!array_key_exists($userIconPath, $avatarList)) {
			$userIconPath = '0';
		}

		$form->addRadioList('avatar', 'Avatar', $avatarList)
			->setDefaultValue($userIconPath);

		$form->addSubmit('submit', 'Save');

		/**
		 * @param Form $form
		 * @param ArrayHash $values
		 */
		$form->onSuccess[] = function (Form $form, ArrayHash $values) use ($user): void {
			try {
				$user->setFirstName($values->firstName === '' ? null : $values->firstName);
				$user->setLastName($values->lastName === '' ? null : $values->lastName);
				$user->setNamePrefix($values->namePrefix === '' ? null : $values->namePrefix);
				$user->setNameSuffix($values->nameSuffix === '' ? null : $values->nameSuffix);
				$user->setEmail($values->email === '' ? null : $values->email);
				$user->setPhone($values->phone === '' ? null : $values->phone);

				$path = (string) $values->avatar;

				if ($path === '0') {
					$user->setIconPath(null);
					$user->setGravatarLastCheck(null);
				} else {
					$user->setIconPath($path);
					$user->setGravatarLastCheck(null);
				}

				$user->getIcon();

				$this->entityManager->flush($user);

				$this->flashMessage('Změny byly úspěšně uloženy.', 'success');
				$this->redirect('Account:default');

			} catch (EntityManagerException $e) {
				Debugger::log($e);
				$this->flashMessage('Database error', 'error');
			}
		};

		return $form;
	}

	/**
	 * @return Form
	 * @throws UserException
	 */
	public function createComponentAccountPasswordForm(): Form
	{
		$identity = $this->user->getIdentity();

		if ($identity instanceof StorageIdentity) {
			$user = $identity->getUser();

			if (!$user instanceof BaseUser) {
				throw new UserException('User identity must be instance of ' . BaseUser::class);
			}
		}

		$form = $this->formFactory->create();

		$form->addPassword('password', 'Password')
			->setCaption('******');

		$form->addPassword('passwordConfirm', 'Password confirm')
			->setCaption('******');

		$form->addSubmit('submit', 'Save');

		/**
		 * @param Form $form
		 * @param ArrayHash $values
		 */
		$form->onSuccess[] = function (Form $form, ArrayHash $values) use ($user): void {
			try {
				if ((string) $values->password !== (string) $values->passwordConfirm) {
					$this->flashMessage('cms.profile.passwordChangeErrorEqual', 'warning');
					$this->redrawControl('flashes');

					return;
				}

				$password = UserPassword::hash((string) $values->password);

				$user->setPassword($password);

				$this->entityManager->flush($user);

				$this->flashMessage('cms.profile.passwordChangeSuccess', 'success');
				$this->redirect('Account:default');

			} catch (EntityManagerException $e) {
				Debugger::log($e);
				$this->flashMessage('Database error', 'error');
			}
		};

		return $form;
	}

}