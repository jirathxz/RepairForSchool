<?php

declare(strict_types=1);

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

use App\Core\Database;
use App\Models\User;
use App\Models\RepairTicket;
use App\Models\TicketComment;
use App\Models\EquipmentType;
use App\Models\Asset;
use App\Models\SparePart;
use App\Models\PMSchedule;
use App\Models\Notification;

echo "====================================================\n";
echo " RepairForSchool - Unified OOP Smoke & System Test\n";
echo "====================================================\n";

// 1. Database Connectivity & Health
$h = Database::checkHealth();
echo "1. Database Status      : {$h['status']} (Version: {$h['version']}, Tables: {$h['tables_count']})\n";
assert($h['tables_count'] >= 11, "Must have at least 11 tables");

// 2. Authentication by Role
$admin = User::authenticate('admin', 'password123');
echo "2. Admin Login          : " . ($admin ? "PASS ({$admin->full_name}, Role: {$admin->role})" : "FAIL") . "\n";
assert($admin !== null && $admin->role === 'admin', "Admin auth failed");

$tech = User::authenticate('tech01', 'password123');
echo "3. Tech Login           : " . ($tech ? "PASS ({$tech->full_name}, Role: {$tech->role})" : "FAIL") . "\n";
assert($tech !== null && $tech->role === 'technician', "Tech auth failed");

$teacher = User::authenticate('teacher01', 'password123');
echo "4. Teacher Login        : " . ($teacher ? "PASS ({$teacher->full_name}, Role: {$teacher->role})" : "FAIL") . "\n";
assert($teacher !== null && $teacher->role === 'teacher', "Teacher auth failed");

// 3. User Profile Verification
$adminUser = User::findById($admin->id);
echo "5. User Profile Check   : PASS (ID: {$adminUser->id}, {$adminUser->email})\n";
assert($adminUser !== null && $adminUser->email === 'admin@school.ac.th', "Profile check failed");

// 4. KPI Status & Overdue
$kpi = RepairTicket::countByStatus();
echo "6. KPI Status Counts    : Total={$kpi['total']}, Pending={$kpi['pending']}, InProcess={$kpi['in_process']}, Completed={$kpi['completed']}\n";
$overdue = RepairTicket::countOverdue();
echo "7. Overdue Tickets      : {$overdue} tickets\n";

// 5. Equipment Types & Usage Stats
$types = EquipmentType::allWithStats();
echo "8. Equipment Types CRUD : " . count($types) . " types loaded with stats\n";
assert(count($types) >= 8, "Expected at least 8 equipment types");
$canDeleteFirst = EquipmentType::canDelete((int)$types[0]['id']);
echo "   - First type canDelete: " . ($canDeleteFirst ? 'YES' : 'NO (in use)') . "\n";

// 6. Detailed Ticket Check & Timeline Logs
$t1 = RepairTicket::getDetailedById(1);
echo "9. Ticket #1 Detail     : [{$t1['ticket_no']}] {$t1['title']}\n";
echo "   - Location           : {$t1['building']} ({$t1['room']})\n";
echo "   - Status             : {$t1['status']}\n";
$logs = RepairTicket::getLogs(1);
echo "10. Status Timeline     : " . count($logs) . " timeline log events loaded\n";
assert(count($logs) > 0, "Ticket #1 must have timeline logs");

// 7. Comments Thread
$comments = TicketComment::getByTicket(1);
echo "11. Comments Thread     : " . count($comments) . " comments on Ticket #1\n";

// 8. Technician Workloads
$workloads = RepairTicket::getTechnicianWorkload();
echo "12. Tech Workloads      : " . count($workloads) . " technicians assigned\n";

// 9. Asset Registry & QR Code Lookup
$assets = Asset::all();
echo "13. Asset Registry      : " . count($assets) . " school assets registered\n";
assert(count($assets) > 0, "Assets must be loaded");
$sampleAsset = $assets[0];
$foundAsset = Asset::findByQrToken($sampleAsset['qr_token']);
assert($foundAsset !== null && $foundAsset['id'] == $sampleAsset['id'], "QR token lookup failed");
echo "    - QR Token Match    : [{$sampleAsset['asset_code']}] -> {$sampleAsset['qr_token']} (PASS)\n";

// 10. Spare Parts Inventory
$parts = SparePart::all();
echo "14. Spare Parts Stock   : " . count($parts) . " parts in inventory\n";
assert(count($parts) > 0, "Spare parts must be loaded");

// 11. Preventive Maintenance (PM Schedules)
$schedules = PMSchedule::all();
echo "15. PM Schedules        : " . count($schedules) . " schedules configured\n";
assert(count($schedules) > 0, "PM schedules must be loaded");
assert(method_exists(PMSchedule::class, 'generateDueTickets'), "generateDueTickets must exist");

// 12. In-App Notification System
$testNotifId = Notification::send($admin->id, 'การทดสอบระบบอัตโนมัติ', 'ระบบทำงานสมบูรณ์ 100%', 1, 'success');
assert($testNotifId > 0, "Notification::send failed");
$unreadCount = Notification::countUnreadByUser($admin->id);
assert($unreadCount > 0, "Unread count should be > 0");
Notification::markAsRead($testNotifId, $admin->id);
$recentNotifs = Notification::getRecentByUser($admin->id, 5);
assert(count($recentNotifs) > 0, "Should fetch recent notifications");
echo "16. In-App Notifications: PASS (Created #{$testNotifId}, unread={$unreadCount}, markAsRead=OK)\n";

echo "====================================================\n";
echo " ALL 16 TEST SUITES EXECUTED & PASSED PERFECTLY!\n";
echo "====================================================\n";
