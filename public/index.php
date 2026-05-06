<?php
require dirname(__DIR__) . '/src/bootstrap.php';

use App\Core\Router;
use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\OnboardingController;

$router = new Router();
$router->get('/', [HomeController::class, 'index']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/onboarding', [OnboardingController::class, 'show']);
$router->post('/onboarding', [OnboardingController::class, 'save']);
$router->get('/admin', [AdminController::class, 'dashboard']);
$router->get('/admin/forms', [AdminController::class, 'formBuilder']);
$router->post('/admin/forms/steps', [AdminController::class, 'saveStep']);
$router->post('/admin/forms/steps/delete', [AdminController::class, 'deleteStep']);
$router->post('/admin/forms/groups', [AdminController::class, 'saveGroup']);
$router->post('/admin/forms/groups/delete', [AdminController::class, 'deleteGroup']);
$router->post('/admin/forms/questions', [AdminController::class, 'saveQuestion']);
$router->post('/admin/forms/questions/delete', [AdminController::class, 'deleteQuestion']);
$router->get('/admin/catalogs', [AdminController::class, 'catalogs']);
$router->post('/admin/catalogs/goals', [AdminController::class, 'saveGoal']);
$router->post('/admin/catalogs/goals/delete', [AdminController::class, 'deleteGoal']);
$router->post('/admin/catalogs/provinces', [AdminController::class, 'saveProvince']);
$router->post('/admin/catalogs/provinces/delete', [AdminController::class, 'deleteProvince']);
$router->post('/admin/catalogs/cities', [AdminController::class, 'saveCity']);
$router->post('/admin/catalogs/cities/delete', [AdminController::class, 'deleteCity']);
$router->get('/admin/health', [AdminController::class, 'health']);
$router->get('/admin/users', [AdminController::class, 'users']);
$router->get('/admin/users/show', [AdminController::class, 'userDetail']);
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
