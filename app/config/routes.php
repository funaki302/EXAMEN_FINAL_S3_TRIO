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
use app\controllers\ModesController;

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
	$modesController = new ModesController();

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
		$app->render('dons', [
			'nonce' => $app->get('csp_nonce'),
			'active_page' => 'dons',
		]);
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
	$router->post('/api/create/villes', [$villesController, 'create']);


	// API Articles
	$router->get('/api/getAll/articles', [$articlesController, 'getAll']);
	$router->post('/api/create/articles', [$articlesController, 'create']);


	// API Besoins
	$router->post('/api/create/besoins', [$besoinsVillesController, 'create']);
	$router->get('/api/getAll/besoins', function() use ($app) {
		$besoinsVillesController = new BesoinsVillesController();
		$dons = $besoinsVillesController->getAll();
		$app->json($dons);
	});

	// API Dons
	$router->get('/api/getAll/dons-recus', function() use ($app) {
		$donsRecusController = new DonsRecusController();
		$dons = $donsRecusController->getAll();
		$app->json($dons);
	});
	$router->get('/api/dashboard/dons-pourcentages', [$donsRecusController, 'dashboardPourcentages']);
	$router->get('/api/getAll/dons-restants', function() use ($app) {
		$donsRecusController = new DonsRecusController();
		$dons = $donsRecusController->getDonsRestants();
		$app->json($dons);
	});
	$router->post('/api/create/dons', [$donsRecusController, 'create']);

	// API Distributions
	$router->post('/distributions', [$distributionsController, 'create']);

	// Dispatch
	$router->post('/dispatch/run', [$dispatchController, 'run']);
	$router->post('/dispatch/run-smallest', [$dispatchController, 'runSmallestNeeds']);
	$router->post('/dispatch/run-proportionnel', [$dispatchController, 'runProportionnel']);
	$router->post('/dispatch/validate', [$dispatchController, 'validate']);
	$router->post('/dispatch/validate-smallest', [$dispatchController, 'validateSmallestNeeds']);
	$router->post('/dispatch/validate-proportionnel', [$dispatchController, 'validateProportionnel']);
	$router->get('/dispatch/summary', [$dispatchController, 'summary']);
	$router->get('/dispatch/dons-restants', [$dispatchController, 'donsRestants']);

	// Achats
	$router->get('/achats/solde', [$achatsController, 'solde']);
	$router->get('/achats/besoins-restants', [$achatsController, 'besoinsRestants']);
	$router->post('/achats/simulate', [$achatsController, 'simulate']);
	$router->post('/achats/validate', [$achatsController, 'validate']);

	// Modes & Réinitialisation
	$router->get('/api/getAll/modes', [$modesController, 'getAll']);
	$router->get('/api/modes/stats', [$modesController, 'getStats']);
	$router->post('/api/modes/reinitialiser', [$modesController, 'reinitialiser']);

}, [ SecurityHeadersMiddleware::class ]);