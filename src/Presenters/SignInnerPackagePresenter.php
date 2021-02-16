<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette\Application\AbortException;

/**
 * Class SignInPresenter
 * @package App\AdminModule\Presenters
 */
class SignInnerPackagePresenter extends BaseAdminPresenter
{

	/**
	 * @param string|null $backLink
	 * @throws AbortException
	 */
	public function actionIn(?string $backLink = null): void
	{
		if ($this->user !== null && $this->user->isLoggedIn() === true && $this->user->getIdentity() !== null) {
			$this->redirect('Homepage:default');
		}

		$this->template->backLink = $backLink;
	}

	/**
	 * Logout
	 * @param bool $clear Clear identity
	 */
	public function actionOut(bool $clear = true): void
	{
		$this->user->logout($clear);
	}

}