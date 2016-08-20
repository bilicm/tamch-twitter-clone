<?php

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Relation;


class Hashtags extends Model
{
	private $id;
	private $value;
	
	public function initialize(){
	$this->hasMany("id", "Hashpost", "hashtag_id",
			array(
				'foreignKey' => array(
					'action' => Relation::ACTION_CASCADE
				)
			)
		);
	}

	public function getId() {
		return $this->id;
	}
	
	public function getValue() {
		return $this->value;
	}
	
	public function setValue($value) {
		$this->value = $value;
	}
	
	public function setId($id) {
		$this->id = $id;
	}
}