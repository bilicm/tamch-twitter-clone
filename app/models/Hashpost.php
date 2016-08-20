<?php

use Phalcon\Mvc\Model;

class Hashpost extends Model
{
	private $id;
	private $hashtag_id;
	private $post_id;
	
	public function initialize() {
		$this->belongsTo("post_id", "Posts", "id",
			array('alias' => 'post')
		);
		$this->belongsTo("hashtag_id", "Hashtags", "id",
			array('alias' => 'hashtag')
		);
	}
	public function getId() {
		return $this->id;
	}
	
	public function getHashtagId() {
		return $this->hashtag_id;
	}
	
	public function getPostId() {
		return $this->post_id;
	}
	
	public function setHashtagId($id) {
		$this->hashtag_id = $id;
	}
	
	public function setPostId($id) {
		$this->post_id = $id;
	}
}