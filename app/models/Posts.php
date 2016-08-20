<?php

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Relation;

class Posts extends Model
{
	private $id;
	private $user_id;
	private $content;
	private $timestamp;
	
	private function createMentions() {
		//check for mentions
		$contents = explode(" ", $this->content);
		$mention = new Mentions();
		
		if (substr($contents[0], 0, 1) === '@'){
			$user = Users::findFirst(array(
				"username = '" . ltrim($contents[0], '@') . "'"
			));
			if($user){
				$mention->save(array(
					"user_id" => $user->getId(),
					"post_id" => $this->getId(),
					"shoutout"=> 1
				));
			} else {
				$mention->save(array(
					"user_id" => 0,
					"post_id" => $this->getId(),
					"shoutout"=> 1
				));
			}
		} else {
			$mention->save(array(
				"user_id" => 0,
				"post_id" => $this->getId(),
				"shoutout"=> 1
			));
		}
		
		unset($contents[0]);
		
		foreach ($contents as $word){
			$mention = new Mentions();
			if (substr($word, 0, 1) === '@'){
					$user = Users::findFirst(array(
					"username = '" . ltrim($word, '@') . "'"
				));
				if($user){
					$mention->save(array(
						"user_id" => $user->getId(),
						"post_id" => $this->getId(),
						"shoutout"=> 0
					));
				}
			}
		}
	}
	
	private function createHashtags() {
		$contents = explode(" ", $this->content);
		
		foreach ($contents as $word){
			$hashtag = new Hashpost();
			if (substr($word, 0, 1) === '#'){
				$word = strtolower($word);
				$hash = Hashtags::findFirst(array(
					"value = '" . ltrim($word, '#') . "'"
				));
				if($hash){
					$hashtag->save(array(
						"hashtag_id" => $hash->getId(),
						"post_id" => $this->getId()
					));
				} else {
					$hash = new Hashtags();
					$hash->save(array(
						"value" => ltrim($word, '#')
					));
					
					$hashtag->save(array(
						"hashtag_id" => $hash->getId(),
						"post_id" => $this->getId()
					));
				}
			}
		}
	}
	
	public function afterSave(){
		$this->createMentions();
		$this->createHashtags();
	}
	
	public function beforeSave() {		
		if($this->timestamp === null){
			date_default_timezone_set('Europe/Zagreb');
			$this->timestamp = date('Y-m-d H:i:s');
		}
	}
	
	public function initialize() {
		$this->belongsTo("user_id", "Users", "id",
			array('alias' => 'user')
		);
		
		$this->hasMany("id", "Hashpost", "post_id",
			array(
				'foreignKey' => array(
					'action' => Relation::ACTION_CASCADE
				)
			)
		);
		
		$this->hasManyToMany(
			"id",
			"Hashpost",
			"post_id", "hashtag_id",
			"Hashtags",
			"id",
			array(
				'alias' => 'hashtags',
				'foreignKey' => array(
					'action' => Relation::ACTION_CASCADE
				)
			)
		);
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getUserId() {
		return $this->user_id;
	}
	
	public function getContent() {
		return $this->content;
	}
	
	public function getTimestamp() {
		return $this->timestamp;
	}
	
	public function setUserId($user_id) {
		$this->user_id = $user_id;
	}
	
	public function setContent($content) {
		$this->content = $content;
	}
}