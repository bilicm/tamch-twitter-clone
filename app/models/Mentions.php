<?php

use Phalcon\Mvc\Model;

class Mentions extends Model
{
	private $id;
	private $post_id;
	private $user_id;
	private $shoutout;
	
	public function initialize() {
		$this->belongsTo("post_id", "Posts", "id",
			array('alias' => 'post')
		);
		$this->belongsTo("user_id", "Users", "id",
			array('alias' => 'user')
		);
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getShoutout() {
		return $this->shoutout;
	}
	
	public function getPostId() {
		return $this->user_id;
	}
	
	public function getUserId() {
		return $this->post_id;
	}
}