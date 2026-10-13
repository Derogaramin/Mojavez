<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('log_errors', '1');

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/helper.php';
require_once dirname(__DIR__) . '/config/jdf.php';
require_once CORE_PATH . '/AutoLoader.php';
AutoLoader::register();

Session::start();

$router = new Router(BASE_URL);

// Home
$router->get('/', ['HomeController', 'index']);
$router->get('/guide', ['HomeController', 'guide']);
/*$router->get('/', function () {
    $controller = new class extends Controller {}; // anonymous
    $controller->render('home', ['title' => 'سامانه مجوز صنایع دستی']);
});*/

// Registration
$router->get('/register', ['RegistrationController', 'step1']);
$router->post('/register/step1', ['RegistrationController', 'processStep1']);
$router->get('/register/step2', ['RegistrationController', 'step2']);
$router->post('/register/step2', ['RegistrationController', 'processStep2']);
$router->get('/register/step3', ['RegistrationController', 'step3']);
$router->post('/register/step3', ['RegistrationController', 'processStep3']);
$router->get('/register/step4', ['RegistrationController', 'step4']);
$router->post('/register/step4', ['RegistrationController', 'processStep4']);
$router->get('/register/success/{trackingCode}', ['RegistrationController', 'success']);

// Tracking
$router->get('/tracking', ['TrackingController', 'search']);
$router->post('/tracking/search', ['TrackingController', 'find']);
$router->get('/tracking/status/{trackingCode}', ['TrackingController', 'status']);
$router->post('/tracking/change-postal-code', ['TrackingController', 'changePostalCode']);
$router->get('/tracking/print/{trackingCode}', ['TrackingController', 'printView']);

// Admin
$router->get('/admin/login', ['AdminController', 'loginForm']);
$router->post('/admin/login', ['AdminController', 'login']);
$router->get('/admin/logout', ['AdminController', 'logout']);
$router->get('/admin/dashboard', ['AdminController', 'dashboard']);
$router->get('/admin/view/{id}', ['AdminController', 'viewApplication']);
$router->post('/admin/change-status', ['AdminController', 'changeStatus']);
// Admin new routes
$router->get('/admin/applications', ['AdminController', 'applications']); // لیست کامل با فیلتر
$router->get('/admin/reports/by-province', ['ReportController', 'byProvince']);
$router->get('/admin/reports/by-city', ['ReportController', 'byCity']);
$router->get('/admin/reports/by-village', ['ReportController', 'byVillage']);
$router->get('/admin/reports/by-license', ['ReportController', 'byLicense']);
$router->get('/admin/reports/by-date', ['ReportController', 'byDate']);
$router->get('/admin/output/general', ['ReportController', 'generalReport']);
$router->get('/admin/output/single/{trackingCode}', ['ReportController', 'singleApplicantReport']);
$router->get('/admin/output/grouped', ['ReportController', 'groupedReportForm']);
$router->post('/admin/output/grouped', ['ReportController', 'groupedReportResult']);
$router->get('/admin/register', ['AdminController', 'registerForm']);
$router->post('/admin/register', ['AdminController', 'registerProcess']);
$router->get('/admin/edit/{id}', ['AdminController', 'editApplication']);
$router->post('/admin/update', ['AdminController', 'updateApplication']);
$router->post('/admin/add-document', ['AdminController', 'addDocument']);
$router->get('/admin/my-tasks', ['AdminController', 'myTasks']);
$router->get('/admin/report/csv', ['ReportController', 'csvReport']);
$router->get('/admin/report/pdf', ['ReportController', 'pdfReport']);
$router->get('/admin/users', ['UserController', 'index']);
$router->get('/admin/users/create', ['UserController', 'createForm']);
$router->post('/admin/users/store', ['UserController', 'store']);
$router->get('/admin/users/edit/{id}', ['UserController', 'editForm']);
$router->post('/admin/users/update/{id}', ['UserController', 'update']);
$router->get('/admin/users/delete/{id}', ['UserController', 'delete']);

// Documents
$router->post('/document/view', ['DocumentController', 'view']);
// Thumbnail
$router->get('/document/thumb/{id}', ['DocumentController', 'thumb']);
//$router->get('/document/{id}', ['DocumentController', 'serve']);

// Check cancel
$router->get('/check-cancel', function () {
    $count = AutoCancelService::cancelExpired();
    AutoCancelService::updatePriorities();
    echo json_encode(['cancelled' => $count, 'message' => 'OK']);
});

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);