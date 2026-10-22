<?php
/**
 * Database Connection & Environment Helper
 * RepairSystemClass - Composer PSR-4 OOP Edition
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Backwards compatibility alias for non-namespaced Database usage
if (!class_exists('Database', false)) {
    class_alias(\App\Core\Database::class, 'Database');
}
