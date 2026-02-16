<?php

use app\middlewares\SecurityHeadersMiddleware;
use flight\Engine;
use flight\net\Router;
use app\controllers\VillesController;
use app\controllers\ArticlesController;
use app\controllers\BesoinsVillesController;
use app\controllers\DonsRecusController;
use app\controllers\DistributionsController;
use app\controllers\DispatchController;
use app\controllers\AchatsController;

/** 
 * @var Router $router 
 * @var Engine $app
 */

$app = \Flight::app();

$router->group('', function(Router $router) use ($app) {

	$villesController = new VillesController();
	$articlesController = new ArticlesController();
	$besoinsVillesController = new BesoinsVillesController();
	$donsRecusController = new DonsRecusController();
	$distributionsController = new DistributionsController();
	$dispatchController = new DispatchController();
	$achatsController = new AchatsController();

	// Dashboard
	$router->get('/', function() use ($app) {
		$villesModel = new \app\models\Villes();
		$donsRecusModel = new \app\models\DonsRecus();
		$articlesModel = new \app\models\Articles();
		$distributionsModel = new \app\models\Distributions();

		$app->render('dashboard', [
			'nonce' => $app->get('csp_nonce'),
			'active_page' => 'dashboard',
			'nbVilles' => $villesModel->count(),
			'nbDons' => $donsRecusModel->count(),
			'nbDistributions' => $distributionsModel->count(),
			'nbArticles' => $articlesModel->count(),
		]);
	});

	$router->get('/form', function() use ($app) {
		$app->render('besoins', [
			'nonce' => $app->get('csp_nonce'),
			'active_page' => 'form',
		]);
	});

	$router->get('/tables', function() use ($app) {
		$app->render('tables', [
			'nonce' => $app->get('csp_nonce'),
			'active_page' => 'tables',
		]);
	});
	$router->get('/dons', function() use ($app) {
		$app->render('dons', ['nonce' => $app->get('csp_nonce')]);
	});

	$router->get('/billing', function() use ($app) {
		$app->render('billing', [
			'nonce' => $app->get('csp_nonce'),
			'active_page' => 'billing',
		]);
	});

	$router->get('/profile', function() use ($app) {
		$app->render('profile', [
			'nonce' => $app->get('csp_nonce'),
			'active_page' => 'profile',
		]);
	});

	$router->get('/dispatch', function() use ($app) {
		$app->render('dispatch', [
			'nonce' => $app->get('csp_nonce'),
			'active_page' => 'dispatch',
		]);
	});

	$router->get('/achats', function() use ($app) {
		$app->render('achats', [
			'nonce' => $app->get('csp_nonce'),
			'active_page' => 'achats',
		]);
	});

	$router->get('/sign-in', function() use ($app) {
		$app->render('sign-in', [
			'nonce' => $app->get('csp_nonce'),
		]);
	});

	$router->get('/sign-up', function() use ($app) {
		$app->render('sign-up', [
			'nonce' => $app->get('csp_nonce'),
		]);
	});

	$router->get('/rtl', function() use ($app) {
		$app->redirect('/');
	});

	// API Villes
	$router->get('/api/getAll/villes', [$villesController, 'getAll']);
	$router->get('/villes/objectifs-dashboard', [$villesController, 'objectifsDashboard']);

	// API Articles
	$router->get('/api/getAll/articles', [$articlesController, 'getAll']);

	// API Besoins
	$router->post('/api/create/besoins', [$besoinsVillesController, 'create']);


	// Routes pour les dons reçus
	$router->get('/api/getAll/dons-recus', function() use ($app) {
		$donsRecusController = new DonsRecusController();
		$dons = $donsRecusController->getAll();
		$app->json($dons);
	});
	// API Dons
	$router->post('/api/create/dons', [$donsRecusController, 'create']);

	// API Distributions
	$router->post('/distributions', [$distributionsController, 'create']);

	// Dispatch
	$router->post('/dispatch/run', [$dispatchController, 'run']);
	$router->post('/dispatch/validate', [$dispatchController, 'validate']);
	$router->get('/dispatch/summary', [$dispatchController, 'summary']);

	// Achats
	$router->get('/achats/solde', [$achatsController, 'solde']);
	$router->get('/achats/besoins-restants', [$achatsController, 'besoinsRestants']);
	$router->post('/achats/simulate', [$achatsController, 'simulate']);
	$router->post('/achats/validate', [$achatsController, 'validate']);

}, [ SecurityHeadersMiddleware::class ]);