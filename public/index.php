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
$router->post('/admin/forms/groups', [AdminController::class, 'saveGroup']);
$router->post('/admin/forms/questions', [AdminController::class, 'saveQuestion']);
$router->post('/admin/forms/questions/delete', [AdminController::class, 'deleteQuestion']);
$router->get('/admin/users', [AdminController::class, 'users']);
$router->get('/admin/users/show', [AdminController::class, 'userDetail']);
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
