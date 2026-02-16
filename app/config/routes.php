<?php

use app\controllers\DiscussionController;
use app\controllers\MessageController;
use app\middlewares\SecurityHeadersMiddleware;
use flight\Engine;
use flight\net\Router;
use app\controllers\UserController;
use app\controllers\VillesController;
use app\controllers\ArticlesController;
use app\controllers\BesoinsVillesController;
use app\controllers\DonsRecusController;


/** 
 * @var Router $router 
 * @var Engine $app
 */

// This wraps all routes in the group with the SecurityHeadersMiddleware
$router->group('', function(Router $router) use ($app) {
	/*
	$userController = new UserController();
	$discussionController = new DiscussionController();
	$messageController = new MessageController();
	*/

	// Initialisation du contrôleur des villes
	$villesController = new VillesController();
	
	// Initialisation des autres contrôleurs
	$articlesController = new ArticlesController();
	$besoinsVillesController = new BesoinsVillesController();
	$donsRecusController = new DonsRecusController();

	$router->get('/', function() use ($app) {
		$app->render('dashboard', []);
	});

	$router->get('/form', function() use ($app) {
		$app->render('besoins', ['nonce' => $app->get('csp_nonce')]);
	});


	// Routes pour les villes
	$router->get('/api/getAll/villes', [$villesController, 'getAll']);
	$router->get('/villes/@id', [$villesController, 'show']);
	$router->get('/villes/region/@region', [$villesController, 'getByRegion']);
	$router->post('/villes', [$villesController, 'create']);
	$router->put('/villes/@id', [$villesController, 'update']);
	$router->delete('/villes/@id', [$villesController, 'delete']);
	$router->get('/villes/stats', [$villesController, 'stats']);
	$router->get('/villes/regions', [$villesController, 'regions']);

	// Routes pour les articles
	$router->get('/api/getAll/articles', [$articlesController, 'getAll']);
	$router->get('/articles/@id', [$articlesController, 'show']);
	$router->get('/articles/categorie/@categorie', [$articlesController, 'getByCategorie']);
	$router->post('/articles', [$articlesController, 'create']);
	$router->put('/articles/@id', [$articlesController, 'update']);
	$router->delete('/articles/@id', [$articlesController, 'delete']);
	$router->get('/articles/categories', [$articlesController, 'categories']);

	// Routes pour les besoins des villes
	$router->get('/besoins-villes', [$besoinsVillesController, 'index']);
	$router->get('/besoins-villes/@id', [$besoinsVillesController, 'show']);
	$router->get('/besoins-villes/ville/@id_ville', [$besoinsVillesController, 'getByVille']);
	$router->get('/besoins-villes/article/@id_article', [$besoinsVillesController, 'getByArticle']);
	$router->post('/api/create/besoins', [$besoinsVillesController, 'create']);
	$router->post('/besoins-villes', [$besoinsVillesController, 'create']);
	$router->put('/besoins-villes/@id', [$besoinsVillesController, 'update']);
	$router->delete('/besoins-villes/@id', [$besoinsVillesController, 'delete']);
	$router->get('/besoins-villes/stats/villes', [$besoinsVillesController, 'statsByVille']);
	$router->get('/besoins-villes/stats/articles', [$besoinsVillesController, 'statsByArticle']);

	// Routes pour les dons reçus
	$router->get('/dons-recus', [$donsRecusController, 'index']);
	$router->get('/dons-recus/@id', [$donsRecusController, 'show']);
	$router->get('/dons-recus/article/@id_article', [$donsRecusController, 'getByArticle']);
	$router->get('/dons-recus/date/@date', [$donsRecusController, 'getByDate']);
	$router->post('/api/create/dons', [$donsRecusController, 'create']);
	$router->post('/dons-recus', [$donsRecusController, 'create']);
	$router->put('/dons-recus/@id', [$donsRecusController, 'update']);
	$router->delete('/dons-recus/@id', [$donsRecusController, 'delete']);
	$router->get('/dons-recus/stats/articles', [$donsRecusController, 'statsByArticle']);
	$router->get('/dons-recus/stats/categories', [$donsRecusController, 'statsByCategorie']);
	$router->get('/dons-recus/periode', [$donsRecusController, 'getByPeriod']);
	$router->get('/dons-recus/valeur-totale', [$donsRecusController, 'valeurTotale']);

	
}, [ SecurityHeadersMiddleware::class ]);