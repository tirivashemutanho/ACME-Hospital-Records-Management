<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

require_once __DIR__ . '/../src/DB.php';
require_once __DIR__ . '/../src/Consultation.php';
require_once __DIR__ . '/../src/ProcedurePerformed.php';
require_once __DIR__ . '/../src/Patient.php';
require_once __DIR__ . '/../src/Outpatient.php';
require_once __DIR__ . '/../src/Inpatient.php';
require_once __DIR__ . '/../src/DaycasePatient.php';
require_once __DIR__ . '/../src/Ward.php';
require_once __DIR__ . '/../src/Invoice.php';
require_once __DIR__ . '/../src/Hospital.php';
// load repositories
foreach (glob(__DIR__ . '/../src/Repositories/*.php') as $repoFile) {
	require_once $repoFile;
}

require_once __DIR__ . '/../src/Auth.php';

use Hospital\Repositories\PatientRepository;

$repo = new PatientRepository();
// expose the repository globally on $GLOBALS for quick access in templates
$GLOBALS['repo'] = $repo;

// current user from session
$currentUser = $_SESSION['user'] ?? null;
$GLOBALS['currentUser'] = $currentUser;

// CSRF token for simple protection
if (empty($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}
$GLOBALS['csrf_token'] = $_SESSION['csrf_token'];

// Public pages that don't require login
$publicPages = ['login.php', 'migrate.php', 'demo.php', 'create_user.php'];
$script = basename($_SERVER['SCRIPT_NAME']);
if (!in_array($script, $publicPages, true) && !$currentUser) {
	header('Location: login.php');
	exit;
}
