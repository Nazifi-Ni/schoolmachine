<?php

// Start session
session_start();

// Autoload dependencies
require_once __DIR__ . '/../vendor/autoload.php';

use App\Helpers\Router;

// Initialize Router
$router = new Router();

// Define Routes
// Auth Routes (Legacy PHP Views)
$router->get('/', 'HomeController', 'index');
$router->get('/login', 'AuthController', 'loginForm');
$router->post('/login', 'AuthController', 'login');
$router->get('/logout', 'AuthController', 'logout');
$router->get('/dashboard', 'DashboardController', 'index');

// API Routes (React Frontend)
$router->post('/api/login', 'AuthController', 'apiLogin');
$router->post('/api/student-login', 'AuthController', 'apiStudentLogin');
$router->get('/api/me', 'AuthController', 'apiMe');
$router->post('/api/change-password', 'AuthController', 'apiChangePassword');
$router->post('/api/logout', 'AuthController', 'apiLogout');

// Student Portal Routes
$router->get('/api/student-portal/dashboard', 'StudentPortalController', 'apiGetDashboard');
$router->get('/api/student-portal/results/{id}', 'StudentPortalController', 'apiGetResultDetails');
$router->post('/api/student-portal/pay/submit-receipt', 'StudentPortalController', 'apiSubmitPaymentReceipt');
$router->get('/api/dashboard', 'DashboardController', 'apiIndex');
$router->get('/api/teachers', 'TeacherController', 'apiIndex');
$router->get('/api/teachers/{id}', 'TeacherController', 'apiShow');
$router->post('/api/teachers', 'TeacherController', 'apiStore');
$router->put('/api/teachers/{id}', 'TeacherController', 'apiUpdate');
$router->delete('/api/teachers/{id}', 'TeacherController', 'apiDelete');

$router->get('/api/classes', 'ClassController', 'apiIndex');
$router->post('/api/classes', 'ClassController', 'apiStore');
$router->put('/api/classes/{id}', 'ClassController', 'apiUpdate');
$router->put('/api/classes/{id}/promote', 'ClassController', 'apiPromote');
$router->delete('/api/classes/{id}', 'ClassController', 'apiDelete');

$router->get('/api/students', 'StudentController', 'apiIndex');
$router->post('/api/students', 'StudentController', 'apiStore');
$router->put('/api/students/{id}', 'StudentController', 'apiUpdate');
$router->delete('/api/students/{id}', 'StudentController', 'apiDelete');

$router->get('/api/sessions', 'SessionController', 'apiIndex');
$router->post('/api/sessions', 'SessionController', 'apiStoreSession');
$router->put('/api/sessions/{id}/set-current', 'SessionController', 'apiSetCurrentSession');
$router->delete('/api/sessions/{id}', 'SessionController', 'apiDeleteSession');
$router->post('/api/terms', 'SessionController', 'apiStoreTerm');
$router->put('/api/terms/{id}/set-current', 'SessionController', 'apiSetCurrentTerm');
$router->delete('/api/terms/{id}', 'SessionController', 'apiDeleteTerm');

$router->get('/api/classes/{id}/subjects', 'SubjectController', 'apiIndex');
$router->post('/api/classes/{id}/subjects', 'SubjectController', 'apiStore');
$router->delete('/api/classes/{classId}/subjects/{subjectId}', 'SubjectController', 'apiDelete');

$router->get('/api/grading', 'GradingController', 'apiIndex');
$router->post('/api/grading', 'GradingController', 'apiStore');
$router->put('/api/grading/{id}', 'GradingController', 'apiUpdate');
$router->delete('/api/grading/{id}', 'GradingController', 'apiDelete');

// Finance Routes
$router->get('/api/finance/pending-approvals', 'FinanceController', 'apiGetPendingApprovals');
$router->post('/api/finance/approvals/{id}', 'FinanceController', 'apiProcessApproval');
$router->get('/api/finance/fees', 'FinanceController', 'apiGetFees');
$router->post('/api/finance/fees', 'FinanceController', 'apiSaveFee');
$router->get('/api/finance/bills', 'FinanceController', 'apiGetBills');
$router->post('/api/finance/bills/generate', 'FinanceController', 'apiGenerateBills');
$router->post('/api/finance/pay', 'FinanceController', 'apiRecordPayment');
$router->get('/api/finance/student/{id}', 'FinanceController', 'apiGetStudentFinance');

// Admissions Routes
$router->get('/api/public/classes', 'AdmissionController', 'apiClasses'); // public list of classes for the form
$router->post('/api/admissions/apply', 'AdmissionController', 'apiApply');
$router->get('/api/admissions', 'AdmissionController', 'apiIndex');
$router->post('/api/admissions/{id}/approve', 'AdmissionController', 'apiApprove');
$router->post('/api/admissions/{id}/reject', 'AdmissionController', 'apiReject');

// Bulk Import
$router->post('/api/students/bulk', 'StudentController', 'apiBulkImport');

$router->get('/api/my-class', 'ResultController', 'apiMyClass');
$router->get('/api/my-class/students/{id}/results', 'ResultController', 'apiStudentResults');
$router->post('/api/my-class/students/{id}/results', 'ResultController', 'apiSaveStudentResults');
$router->delete('/api/my-class/students/{id}/results', 'ResultController', 'apiDeleteResult');

// Teacher Routes
$router->get('/teachers', 'TeacherController', 'index');
$router->get('/teachers/create', 'TeacherController', 'create');
$router->post('/teachers/store', 'TeacherController', 'store');
$router->get('/teachers/edit/{id}', 'TeacherController', 'edit');
$router->post('/teachers/update/{id}', 'TeacherController', 'update');
$router->post('/teachers/delete/{id}', 'TeacherController', 'delete');

// Classes Routes
$router->get('/classes', 'ClassController', 'index');
$router->get('/classes/create', 'ClassController', 'create');
$router->post('/classes/store', 'ClassController', 'store');
$router->get('/classes/edit/{id}', 'ClassController', 'edit');
$router->post('/classes/update/{id}', 'ClassController', 'update');
$router->post('/classes/delete/{id}', 'ClassController', 'delete');

// Subjects Routes
$router->get('/classes/{id}/subjects', 'SubjectController', 'index');
$router->post('/classes/{id}/subjects/store', 'SubjectController', 'store');
$router->post('/classes/{id}/subjects/delete/{subject_id}', 'SubjectController', 'delete');

// Student Routes
$router->get('/students', 'StudentController', 'index');
$router->get('/students/create', 'StudentController', 'create');
$router->post('/students/store', 'StudentController', 'store');
$router->get('/students/edit/{id}', 'StudentController', 'edit');
$router->post('/students/update/{id}', 'StudentController', 'update');

// Session & Term Routes
$router->get('/sessions', 'SessionController', 'index');
$router->post('/sessions/store', 'SessionController', 'store');
$router->post('/sessions/set-current/{id}', 'SessionController', 'setCurrent');
$router->post('/sessions/delete/{id}', 'SessionController', 'delete');
$router->post('/terms/store', 'SessionController', 'storeTerm');
$router->post('/terms/set-current/{id}', 'SessionController', 'setCurrentTerm');

// Grading System Routes
$router->get('/grading', 'GradingController', 'index');
$router->get('/grading/create', 'GradingController', 'create');
$router->post('/grading/store', 'GradingController', 'store');
$router->get('/grading/edit/{id}', 'GradingController', 'edit');
$router->post('/grading/update/{id}', 'GradingController', 'update');
$router->post('/grading/delete/{id}', 'GradingController', 'delete');

// Result Entry Routes (Class Teacher)
$router->get('/my-class', 'ResultController', 'myClass');
$router->get('/results/subjects', 'ResultController', 'manageSubjects');
$router->post('/results/subjects/store', 'ResultController', 'storeSubject');
$router->post('/results/subjects/delete/{id}', 'ResultController', 'deleteSubject');
$router->get('/results/student/{id}', 'ResultController', 'studentResults');
$router->post('/results/student/{id}/save', 'ResultController', 'saveStudentResults');
$router->get('/results/print/{id}', 'ResultController', 'printResult');
$router->get('/results/print-all', 'ResultController', 'printAll');
$router->post('/results/delete/{id}', 'ResultController', 'deleteResult');
$router->post('/students/delete/{id}', 'StudentController', 'delete');

// Dispatch Router
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
