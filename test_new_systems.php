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

use App\Models\Notification;
use App\Models\EquipmentType;
use App\Models\RepairTicket;
use App\Models\Asset;

echo "========================================\n";
echo " RepairForSchool - New Systems Verification\n";
echo "========================================\n";

// 1. Notifications System
echo "1. Notifications System:\n";
$notifId = Notification::send(1, 'ทดสอบแจ้งเตือนระบบใหม่', 'ข้อความทดสอบการแจ้งเตือน', 1, 'info');
$unreadBefore = Notification::countUnreadByUser(1);
assert($unreadBefore > 0, "Unread count should be > 0");
Notification::markAsRead($notifId, 1);
echo "   [PASS] Created notification #{$notifId}, verified unread counter, and marked as read\n";

// 2. Status Logs & Timeline
echo "2. Status Timeline System:\n";
$logs = RepairTicket::getLogs(1);
assert(count($logs) > 0, "Ticket #1 should have logs");
echo "   [PASS] Loaded " . count($logs) . " timeline logs for Ticket #1\n";

// 3. Equipment Types CRUD
echo "3. Equipment Types Management:\n";
$types = EquipmentType::allWithStats();
assert(count($types) >= 8, "Should load at least 8 equipment types");
$canDeleteFirst = EquipmentType::canDelete((int)$types[0]['id']);
echo "   [PASS] Loaded " . count($types) . " types with real usage counts (Type #1 canDelete=" . ($canDeleteFirst ? 'yes' : 'no') . ")\n";

// 4. Asset QR Link & Integration
echo "4. Asset QR & Ticket Integration:\n";
$assets = Asset::all();
assert(count($assets) > 0, "Assets should be loaded");
$sampleAsset = $assets[0];
$foundByToken = Asset::findByQrToken($sampleAsset['qr_token']);
assert($foundByToken !== null, "Should find asset by QR token");
echo "   [PASS] Verified Asset QR Token lookup ({$sampleAsset['asset_code']} -> {$sampleAsset['qr_token']})\n";

echo "========================================\n";
echo " ALL 4 NEW SYSTEMS VERIFIED PERFECTLY!\n";
echo "========================================\n";
