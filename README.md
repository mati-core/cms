# Mati-Core  | USER

Install
-------

Comoposer command:
```bash
composer require mati-core/user
```

Insert next code in class BasePresenter

```php

    /**
	 * @var string
	 */
	protected $pageRight = 'cms';
	
    use UserPresenterAccessTrait;

```

Commands
--------

**Default init**

Create "Super admin" group with full access, 
"Admin" group with role "Admin" and "cms" right, 
Super admin account

```bash
php www/index.php app:user:init <username> <password> 
```

**Create user group**

Create user group. If is first user group, then be set as default.

```bash
php www/index.php app:usergroup:create <groupname>
```

**Create user**

Create user account and associate in default user group.

```bash
php www/index.php app:user:create <username> <password> 
```