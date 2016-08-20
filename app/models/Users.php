<?php

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Relation;

class Users extends Model
{
	private $id;
	private $username;
	private $first_name;
	private $last_name;
	private $timestamp = null;
	private $email;
	
	public function beforeSave() {
		if($this->timestamp === null){
			date_default_timezone_set('Europe/Zagreb');
			$this->timestamp = date('Y-m-d H:i:s');
		}
	}
	
	public function initialize() {
		$this->hasOne("id", "Auth", "user_id",
			array(
				'foreignKey' => array(
					'action' => Relation::ACTION_CASCADE
				)
			)
		);
		
		$this->hasMany("id", "Posts", "user_id",
			array(
				'foreignKey' => array(
					'action' => Relation::ACTION_CASCADE
				)
			)
		);
		
		$this->hasManyToMany(
			"id",
			"Followers",
			"follower_id", "followed_id",
			"Users",
			"id",
			array(
				'alias' => 'followees',
				'foreignKey' => array(
					'action' => Relation::ACTION_CASCADE
				)
			)
		);
		
		$this->hasManyToMany(
			"id",
			"Followers",
			"followed_id", "follower_id",
			"Users",
			"id",
			array(
				'alias' => 'followers',
				'foreignKey' => array(
					'action' => Relation::ACTION_CASCADE
				)
			)
		);
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getUsername() {
		return $this->username;
	}
	
	public function getFirstName() {
		return $this->first_name;
	}
	
	public function getLastName() {
		return $this->last_name;
	}
	
	public function getTimestamp() {
		return $this->timestamp;
	}
	
	public function getEmail() {
		return $this->email;
	}
	
	public function setUsername($username) {
		$this->username = $username;
	}
	
	public function setFirstName($first_name) {
		$this->first_name = $first_name;
	}
	
	public function setLastName($last_name) {
		$this->last_name = $last_name;
	}
	
	public function setEmail($email) {
		$this->email = $email;
	}
	
}