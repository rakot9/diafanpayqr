<?php

class dfnUserAuth {
	
	public static $instance;
	public $diafan;

	public function __construct($diafan)
	{
		$this->diafan = $diafan;
	}

	public static function getInstance($diafan)
	{
		if(self::$instance instanceof dfnUserAuth)
		{
			return self::$instance;
		}
		return self::$instance = new self($diafan);
	}

	/**
	 * Проверяет наличие пользователя в системе
	 * @param string $email
	 * @return bool
	 */
	public function checkUser($email)
	{
		return DB::query_result("SELECT id FROM {users} WHERE mail='%s' LIMIT 1", $email)? true : false;
	}


	/**
	 * Возвращает идентификатор пользователя
	 * @param string $email
	 */
	public function getUserId($email)
	{
		return DB::query_result("SELECT id FROM {users} WHERE LOWER(mail)='%s' LIMIT 1", strtolower($email));
	}

	public function CreateUser($email)
	{
		$_POST["name"] = $email;
		$_POST["mail"] = $email;
		
		//Пароль 123qwe123
		$_POST["password"] = "5667a84c6f63b6cbcd87f974c4fc032e";
		
		//Пароль 123qwe123
		$_POST["password2"] = "5667a84c6f63b6cbcd87f974c4fc032e";
		$_POST["fio"] = "PayQR";
		$_POST["action"] = "add";
		$_POST["phone"] = "";

		/**
		 * Отключаем каптчу временно для модуля
		 */
		//в таблице diafan_config ищем модуль users по полю name='captcha'
		DB::query("UPDATE {config} SET name='%s' WHERE module_name='users' AND name='captcha'", "c_aptcha");

		$module = 'registration';
		$this->diafan->_site->module = $module;
		$this->diafan->current_module = $module;
		Custom::inc('modules/'.$module.'/'.$module.'.php');
		$registration = new Registration($this->diafan);
		$registration->action();
		/**
		 * Включаем снова каптчу
		 */
		DB::query("UPDATE {config} SET name='%s' WHERE module_name='users' AND name='c_aptcha'", "captcha");


		return $this->getUserId($email);
	}

	/**
     * Получаем роль пользователя
     * @var int userId
     * @return int
     */
    public function _dfnGetUserRoleId($userId)
    {
        //см. таблицу {users_role}
        if($userId)
        {
        	PayqrLog::log("SELECT role_id FROM diafan_users WHERE id=".$userId);
            return DB::query_result("SELECT role_id FROM {users} WHERE id=%d", $userId);
        }
        return 0;
    }
}