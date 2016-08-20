<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Resultset;

class HomepageController extends Controller
{

	public function indexAction()
	{
		if($this->session->get("user_id")) {
			//$this->view->my_profile = ($this->session->get("username") === $username);
		} else {
			$this->view->disable();
			$this->response->redirect("index");
			return;
		}
		
		$user_id = $this->session->get("user_id");
		
		$user = Users::findFirst(array(
				"id = '" . $user_id . "'"
		));
				
		if($user){
			$posts = new Posts();
			$user_id = $user->getId();
			
			$followed = Followers::find(
				array(
					"conditions"=> "follower_id = ?1",
					"bind"		=> array(1 => "$user_id"),
					"columns"	=> "followed_id"
				)
			);
			$followed->setHydrateMode(Resultset::HYDRATE_ARRAYS);
			$followed = $followed->toArray();
			
			$followed = array_column($followed, "followed_id");
			//$followed = implode(",", $followed);
			
			//$posts = $user->getPosts();
			
			/*$posts = Posts::find(
				array(
					"conditions"=> "user_id IN (?1)",
					"bind"		=> array(1 => $followed),
					"order"		=> "timestamp DESC",
					"columns"	=> "content, timestamp, user_id"
				)
			);*/
			
			array_push($followed, '0', $user_id);
			
			$posts = $this->modelsManager->createBuilder()
				->from(array('p' => 'Posts'))
				->addFrom('Users', 'u')
				->addFrom('Mentions', 'm')
				->Where('u.id = p.user_id')
				->andWhere('m.post_id = p.id')
				->andWhere('m.shoutout = 1')
				->inWhere("p.user_id", $followed)
				->inWhere("m.user_id", $followed)
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
	
}