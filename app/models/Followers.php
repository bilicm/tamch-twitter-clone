<?php

use Phalcon\Mvc\Model;

class Followers extends Model
{
	private $id;
	private $follower_id;
	private $followed_id;
	
	public function initialize() {
		$this->belongsTo("follower_id", "Users", "id",
			array('alias' => 'follower')
		);
		$this->belongsTo("followed_id", "Users", "id",
			array('alias' => 'followee')
		);
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getFollowerId() {
		return $this->follower_id;
	}
	
	public function getFollowedId() {
		return $this->followed_id;
	}
}