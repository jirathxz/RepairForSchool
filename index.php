<?php

declare(strict_types=1);

/**
 * RepairForSchool - Front Controller & Application Router
 * Technology Stack: PHP 8.3 OOP, MariaDB 11.4 / MySQL, Bootstrap 5.3 CDN
 */

// 1. Register Composer PSR-4 Autoloader or Built-in PSR-4 Fallback
$autoloaderPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloaderPath)) {
    require_once $autoloaderPath;
} else {
    spl_autoload_register(function (string $class) {
        $prefix = 'App\\';
        $baseDir = __DIR__ . '/src/';
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    });
}

// 2. Start Secure Session
use App\Core\Auth;
use App\Core\Session;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\TicketController;
use App\Controllers\CommentController;
use App\Controllers\UserController;
use App\Controllers\LandingController;
use App\Controllers\AssetController;
use App\Controllers\SparePartController;
use App\Controllers\PMScheduleController;
use App\Controllers\NotificationController;
use App\Controllers\EquipmentTypeController;

Session::start();
header('Content-Type: text/html; charset=UTF-8');

// 3. Resolve Requested Page
$page = $_GET['page'] ?? null;

// If visiting root URL without query param
if ($page === null || $page === '') {
    if (Auth::check()) {
        header('Location: ' . Auth::defaultRedirectUrl());
        exit;
    } else {
        $page = 'landing';
    }
}

// 4. Route Dispatcher
try {
    switch ($page) {
        // --- Landing Page ---
        case 'landing':
            (new LandingController())->index();
            break;

        // --- Authentication Routes ---
        case 'login':
            (new AuthController())->showLogin();
            break;

        case 'login-submit':
            (new AuthController())->login();
            break;

        case 'logout':
            (new AuthController())->logout();
            break;

        case 'profile':
            (new AuthController())->profile();
            break;

        case 'profile-update':
            (new AuthController())->updateProfile();
            break;

        // --- Role Dashboards ---
        case 'dashboard':
            (new DashboardController())->index();
            break;

        case 'admin-dashboard':
            (new DashboardController())->admin();
            break;

        case 'technician-dashboard':
            (new DashboardController())->technician();
            break;

        case 'teacher-dashboard':
            (new DashboardController())->teacher();
            break;

        // --- Repair Tickets ---
        case 'tickets':
            (new TicketController())->index();
            break;

        case 'tickets-show':
            (new TicketController())->show();
            break;

        case 'tickets-create':
            (new TicketController())->create();
            break;

        case 'tickets-store':
            (new TicketController())->store();
            break;

        case 'tickets-assign':
            (new TicketController())->assign();
            break;

        case 'tickets-update-tech':
            (new TicketController())->updateTechnician();
            break;

        case 'tickets-export':
            (new TicketController())->exportCsv();
            break;

        case 'tickets-print':
            (new TicketController())->printTicket();
            break;

        case 'tickets-accept':
            (new TicketController())->accept();
            break;

        case 'tickets-rework':
            (new TicketController())->rework();
            break;

        case 'tickets-add-part':
            (new TicketController())->addPart();
            break;

        case 'tickets-remove-part':
            (new TicketController())->removePart();
            break;

        // --- System A: Asset Registry & QR Code ---
        case 'assets':
            (new AssetController())->index();
            break;

        case 'assets-store':
            (new AssetController())->store();
            break;

        case 'assets-qr':
            (new AssetController())->qrPrint();
            break;

        // --- System C: Spare Parts & Stock Inventory ---
        case 'spare-parts':
            (new SparePartController())->index();
            break;

        case 'spare-parts-store':
            (new SparePartController())->store();
            break;

        case 'spare-parts-restock':
            (new SparePartController())->restock();
            break;

        // --- System E: Preventive Maintenance Schedules ---
        case 'pm-schedules':
            (new PMScheduleController())->index();
            break;

        case 'pm-schedules-store':
            (new PMScheduleController())->store();
            break;

        case 'pm-schedules-trigger':
            (new PMScheduleController())->trigger();
            break;

        case 'pm-schedules-trigger-due':
            (new PMScheduleController())->triggerDue();
            break;

        // --- Ticket Comments ---
        case 'comments-store':
            (new CommentController())->store();
            break;

        // --- User Management (Admin) ---
        case 'users':
            (new UserController())->index();
            break;

        case 'users-store':
            (new UserController())->store();
            break;

        case 'users-toggle':
            (new UserController())->toggleStatus();
            break;

        // --- Notifications ---
        case 'notifications-read':
            (new NotificationController())->read();
            break;

        case 'notifications-read-all':
            (new NotificationController())->readAll();
            break;

        // --- System 4: Equipment Types Management (Admin) ---
        case 'equipment-types':
            (new EquipmentTypeController())->index();
            break;

        case 'equipment-types-store':
            (new EquipmentTypeController())->store();
            break;

        case 'equipment-types-delete':
            (new EquipmentTypeController())->delete();
            break;

        default:
            http_response_code(404);
            Session::setFlash('warning', "ไม่พบหน้าที่คุณต้องการเรียกใช้งาน ({$page})");
            header('Location: ' . (Auth::check() ? Auth::defaultRedirectUrl() : 'index.php?page=login'));
            exit;
    }
} catch (\Throwable $e) {
    http_response_code(500);
    error_log("[Application Error] " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());

    $isDebug = getenv('APP_DEBUG') === 'true';

    echo "<div style='font-family: sans-serif; padding: 30px; max-width: 800px; margin: 40px auto; border: 2px solid #ef4444; border-radius: 8px; background: #fff5f5;'>";
    echo "<h3 style='color: #dc2626; margin-top: 0;'>เกิดข้อผิดพลาดในการประมวลผล (Application Error)</h3>";

    if ($isDebug) {
        echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . " (Line: " . $e->getLine() . ")</p>";
        echo "<pre style='background: #f8fafc; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        echo "<p style='color: #4b5563; font-size: 14px;'>ขออภัย เกิดข้อผิดพลาดทางเทคนิคขึ้นในระบบ เจ้าหน้าที่กำลังเร่งดำเนินการตรวจสอบ</p>";
        echo "<p><a href='index.php' style='color: #2563eb; text-decoration: underline; font-size: 14px;'>&larr; กลับสู่หน้าหลัก</a></p>";
    }

    echo "</div>";
    exit;
}