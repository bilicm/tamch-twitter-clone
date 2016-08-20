<?php

use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Db\Adapter\Pdo\Sqlite as DbAdapter;
use Phalcon\Session\Adapter\Files as Session;

try {
	$loader = new Loader();
	$loader->registerDirs(array(
		'app/controllers/',
		'app/models/'
	))->register();

	$di = new FactoryDefault();
	
	$di->setShared('session', function () {
		$session = new Session();
		$session->start();
		return $session;
	});
	
    $di->set('db', function () {
        return new DbAdapter(array(
            "dbname"   => "app/tamch.db"
        ));
    });

	$di->set('view', function () {
		$view = new View();
		$view->setViewsDir('app/views/');
		return $view;
	});

	$di->set('url', function () {
		$url = new UrlProvider();
		$url->setBaseUri('/');
		return $url;
	});

	$application = new Application($di);

	echo $application->handle()->getContent();

} catch (\Exception $e) {
	echo "Exception: ", $e->getMessage();
}