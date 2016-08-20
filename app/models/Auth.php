<?php

use Phalcon\Mvc\Model;

class Auth extends Model
{
	private $id;
	private $user_id;
	private $username;
	private $password;
	
	
	public function initialize() {
		$this->belongsTo("user_id", "Users", "id",
			array('alias' => 'user')
		);
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getUserId() {
		return $this->user_id;
	}
	
	public function getUsername() {
		return $this->username;
	}
	
	public function getPassword() {
		return $this->password;
	}
	
	public function setUserId($user_id) {
		$this->user_id = $user_id;
	}
	
	public function setUsername($username) {
		$this->username = $username;
	}
	
	public function setPassword($password) {
		$this->password = $password;
	}
}