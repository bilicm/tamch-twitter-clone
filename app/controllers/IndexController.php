<?php

use Phalcon\Mvc\Controller;

class IndexController extends Controller
{

	public function indexAction() {
	
	}
	
	public function signupAction() {
	
	}
	

	public function registerAction() {
		
		$user = new Users();
		$success_auth = false;

		$success = $user->save($this->request->getPost(), array('username', 'first_name', 'last_name', 'email'));
		
		if ($success){
			$user = Users::findFirst(array(
				"username = '" . $this->request->getPost("username") . "'"
			));
			
			$id = $user->getId();
			
			$auth = new Auth();
			
			$success = $auth->save(
				array(
					"user_id" => $id,
					"username" => $this->request->getPost("username"),
					"password" => $this->security->hash($this->request->getPost("password"))
				)
			);
			
			if($success) {
				$imagedir = 'public/img/user/' . $this->request->getPost("username");
				mkdir($imagedir);
				mkdir($imagedir . '/original');
				mkdir($imagedir . '/large');
				mkdir($imagedir . '/small');
				//echo "Thanks for registering!";
				//echo $this->tag->linkTo("login", "GOTO Login");
				$this->response->redirect("user/login");
			} else {
				echo "Sorry, the following problems were generated: ";
				foreach ($auth->getMessages() as $message) {
					echo $message->getMessage(), "<br/>";
				}
			}
			
		} else {
			echo "Sorry, the following problems were generated: ";
			foreach ($user->getMessages() as $message) {
				echo $message->getMessage(), "<br/>";
			}
		}

		$this->view->disable();
	}

	public function hashtagAction($tag) {
		$hashtag = new Hashtags();
		$hashtag = Hashtags::findFirst(array(
					"value = '" . strtolower($tag) . "'"
				));

		if($hashtag){
			$posts = new Posts();
			$hashtag_id = $hashtag->getId();
			$posts = $this->modelsManager->createBuilder()
				->from(array('p' => 'Posts'))
				->addFrom('Users', 'u')
				->addFrom('Hashpost', 'h')
				->Where('u.id = p.user_id')
				->andWhere('h.post_id = p.id')
				->andWhere('h.hashtag_id = :id:', array('id' => $hashtag_id))
				->orderBy('p.timestamp DESC')
				->columns('u.username, p.timestamp, p.content')
				->getQuery()
				->execute();
			/*$posts->setHydrateMode(Resultset::HYDRATE_ARRAYS);*/
			$this->view->posts = $posts;
			$this->view->none = false;
		} else {
			$this->view->none = true;
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

}