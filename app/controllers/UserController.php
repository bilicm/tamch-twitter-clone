<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Resultset;

class UserController extends Controller
{

	public function indexAction()
	{		
		$this->view->disable();
		$this->response->redirect("user/profile/" . $this->session->get("username"));

	}
	
	public function profileAction($username){
		$this->view->username = $username;
		
		if($this->session->get("username")) {
			$this->view->my_profile = ($this->session->get("username") === $username);
		} else {
			$this->view->my_profile = false;
		}
		
		if($this->session->has("username")){
			$this->view->session = 1;
		} else {
			$this->view->session = 0;
		}
		
		$img = "public/img/user/$username/small/avatar.jpg";
		if (file_exists($img)) {
  			$this->view->img = $img; 
		}else{
 			$this->view->img = "public/img/user/default/small/avatar.jpg";
		}

		$user = new Users();

		$user = Users::findFirst(array(
				"username = '" . $username . "'"
		));
				
		if($user){
			$posts = new Posts();
			$user_id = $user->getId();
			$curr_user_id = $this->session->get("user_id");
			//$posts = $user->getPosts();
			$posts = Posts::find(
				array(
					"conditions"=> "user_id = ?1",
					"bind"		=> array(1 => "$user_id"),
					"order"		=> "timestamp DESC",
					"columns"	=> "content, timestamp"
				)
			);

			$followers = new Followers();
			$followers = Followers::find(array(
				'columns'    => '*',
        			'conditions' => 'follower_id = ?1 AND followed_id = ?2',
        			'bind'       =>  [1 => $curr_user_id, 2 => $user_id]
			));
			$this->view->following = $followers->count();
			//$this->view->curr_user_id = $curr_user_id;
			//$this->view->user_id = $user_id;

			$posts->setHydrateMode(Resultset::HYDRATE_ARRAYS);
			$this->view->posts = $posts;
			//$user->setHydrateMode(Resultset::HYDRATE_ARRAYS);
			$this->view->user = $user;
			$this->view->none = false;
		} else {
			

			$this->view->none = true;
		}


		function getLink($post){
		
		$returnedPost = "";
		$contents = explode(" ", $post);
		
		foreach ($contents as $word){
			if (substr($word, 0, 1) === '#'){
				$word = "<a href='/index/hashtag/" . ltrim($word, '#') . "'>" . $word . "</a>";
				}
			if (substr($word, 0, 1) === '@'){
				$word = "<a href='/user/profile/" . ltrim($word, '@') . "'>" . $word . "</a>";
				}
			
			$returnedPost .= $word;
			$returnedPost .= " ";
			
			}
		return $returnedPost;

		}

	}
	
	public function postsAction()
	{
		
		$user = new Users();
		$posts = new Posts();
		$username = $this->session->get("username");

		$user = Users::findFirst(array(
				"username = '" . $username . "'"
			));
		$id = $user->getId();
		
		$success = $posts->save(
			array(
				"user_id" => $id,
				"content" => strip_tags($this->request->getPost("content"))
			)
		);
		
		$this->view->disable();
		
		if($success) {
				$this->response->redirect("user/profile/$username");
		} else {
				$this->response->redirect("index");
		}
		
	}

	public function followAction($username)
	{

		$user1 = new Users();
		$user1 = Users::findFirst(array(
				"username = '" . $username . "'"
		));

		$curr_user = $this->session->get("username");
		$user2 = new Users();
		$user2 = Users::findFirst(array(
				"username = '" . $curr_user . "'"
		));
		
		$user_id = $user1->getId();
		$curr_user_id = $user2->getId();

		$followers = new Followers();
		$followers = Followers::find(array(
			'columns'    => '*',
       			'conditions' => 'follower_id = ?1 AND followed_id = ?2',
       			'bind'       =>  [1 => $curr_user_id, 2 => $user_id]
		));
		
		if($followers->count()){

			$delete = $followers->delete();

			$this->view->disable();

			if($delete) {
				$this->response->redirect("user/profile/$username");
			} else {
				$this->response->redirect("index");
			}
		} else {
			$followers = new Followers();
			$success = $followers->save(
				array(
					"follower_id" => $curr_user_id,
					"followed_id" => $user_id
				)
			);

			$this->view->disable();

			if($success) {
				$this->response->redirect("user/profile/$username");
			} else {
				$this->response->redirect("index");
			}
		}

	}
	
	public function loginAction() {
	
	}

	public function userloginAction() {
		$auth = new Auth();
		$username = $this->request->getPost("username");
		$password = $this->request->getPost("password");
		
		$auth = Auth::findFirst(array(
			"username = '" . $username ."'"
		));

		if($auth){
			$passcheck = $auth->getPassword();
			$user_id = $auth->user_id;
			if($this->security->checkHash($password, $passcheck)){
				$this->session->set("username", "$username");
				$this->session->set("user_id", "$user_id");
				$this->view->disable();
				$this->response->redirect("/");
			} else {
				echo "Wrong password<br />";
			}
		} else {
			echo "Wrong Username<br />";
		}
	}

	public function logoutAction() {
		$this->session->destroy();
		echo "Session is kill<br />";
		$this->view->disable();
		$this->response->redirect("/");
	}
	
	public function mentionsAction($username)
	{
		if(isset($username)) {
			//$this->view->my_profile = ($this->session->get("username") === $username);
		} else {
			$this->view->disable();
			$this->response->redirect("index");
			return;
		}
		

		$user = Users::findFirst(array(
				"username = '" . $username . "'"
		));
				
		if($user){
			$posts = new Posts();
			$user_id = $user->getId();
			$posts = $this->modelsManager->createBuilder()
				->from(array('p' => 'Posts'))
				->addFrom('Users', 'u')
				->addFrom('Mentions', 'm')
				->Where('u.id = p.user_id')
				->andWhere('m.post_id = p.id')
				->andWhere('m.user_id = :id:', array('id' => $user_id))
				->orderBy('p.timestamp DESC')
				->columns('u.username, p.timestamp, p.content')
				->getQuery()
				->execute();
			
			$posts->setHydrateMode(Resultset::HYDRATE_ARRAYS);
			$this->view->posts = $posts;
			//$user->setHydrateMode(Resultset::HYDRATE_ARRAYS);
			//$this->view->user = $user;
			$this->view->none = false;
		} else {
			$this->view->none = true;
		}
		
		/*var_dump($followed);
		var_dump($posts);
		$this->view->disable();*/

		function getLink($post){
		
		$returnedPost = "";
		$contents = explode(" ", $post);
		
		foreach ($contents as $word){
			if (substr($word, 0, 1) === '#'){
				$word = "<a href='/index/hashtag/" . ltrim($word, '#') . "'>" . $word . "</a>";
				}
			if (substr($word, 0, 1) === '@'){
				$word = "<a href='/user/profile/" . ltrim($word, '@') . "'>" . $word . "</a>";
				}
			
			$returnedPost .= $word;
			$returnedPost .= " ";
			
			}
		return $returnedPost;

		}

		function checkImage($username){
		
		$img = "public/img/user/$username/small/avatar.jpg";
		if (file_exists($img)) {
  			$img= $username; 
		}else{
 			$img = "default	";
		}

		return $img;

		}
	
	}
	
	public function settingsAction () {

		
	$img = "public/img/user/" . $this->session->get("username") . "/small/avatar.jpg";
		if (file_exists($img)) {
  			$this->view->img = $img; 
		}else{
 			$this->view->img = "public/img/user/default/small/avatar.jpg";
		}

		
	}

	public function uploadAction () {
		if ($this->request->hasFiles() == true) {
			if($this->session->get("username")) {
				$directory = 'public/img/user/' . $this->session->get("username");
			}

			foreach ($this->request->getUploadedFiles() as $file) {
				/* $photos = new Image();
				$photos->name = $file->getName();
				$photos->size = $file->getSize();
				$photos->save(); */

				/*if(file_exists($filename)) {
					unlink($filename);
				}*/
				
				$filepath = $directory . '/original/avatar.jpg';
				if(file_exists($filepath)){
					unlink($filepath);
				}
				$file->moveTo($filepath);
				
				$image = new Phalcon\Image\Adapter\Imagick($filepath);
				$image->resize(160, 160);
				$filepath = $directory . '/large/avatar.jpg';
				if(file_exists($filepath)){
					unlink($filepath);
				}
				$image->save($filepath);
				
				$image->resize(50, 50);
				$filepath = $directory . '/small/avatar.jpg';
				if(file_exists($filepath)){
					unlink($filepath);
				}
				$image->save($filepath);
			}
			$img = $this->session->get("username");
		}
	}
	
	public function findAction () {
		if ($this->request->isPost() == true) {
			$name = $this->request->getPost("name");
			$this->response->redirect("/user/profile/" . $name . "");
		}
		

	}

}