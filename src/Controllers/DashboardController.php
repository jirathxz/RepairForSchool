<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\RepairTicket;
use App\Models\User;

/**
 * Dashboard Controller
 * Provides role-tailored dashboards for Admin, Technician, and Teacher.
 */
class DashboardController extends Controller
{
    /**
     * Entry router for /dashboard -> redirects by role
     */
    public function index(): void
    {
        Auth::requireLogin();
        $this->redirect(Auth::defaultRedirectUrl());
    }

    /**
     * Admin KPI Dashboard
     */
    public function admin(): void
    {
        Auth::requireRole('admin');

        $kpi = RepairTicket::countByStatus();
        $overdueCount = RepairTicket::countOverdue();
        $totalCost = RepairTicket::getTotalCost();
        $workloads = RepairTicket::getTechnicianWorkload();
        $recentTickets = RepairTicket::getFiltered(['limit' => 8]);

        // Health assessment calculation
        $total = $kpi['total'];
        $pending = $kpi['pending'];
        $inProcess = $kpi['in_process'];
        $completed = $kpi['completed'];

        $healthStatus = 'good';
        $healthMessage = 'สถานะระบบอยู่ในเกณฑ์ดีเยี่ยม: งานส่วนใหญ่ได้รับการจัดการอย่างรวดเร็ว';

        if ($total > 0) {
            $pendingRate = ($pending / $total) * 100;
            if ($pendingRate > 40 || $overdueCount >= 3) {
                $healthStatus = 'danger';
                $healthMessage = 'สถานะน่าเป็นห่วง: มีงานค้างรอดำเนินการหรือเกินกำหนดจำนวนมาก ควรเร่งจ่ายงานให้ช่าง';
            } elseif ($pendingRate > 20 || $overdueCount > 0) {
                $healthStatus = 'warning';
                $healthMessage = 'สถานะปานกลาง: มีงานรอดำเนินการหรือชะลอตัวบางส่วน ควรติดตามความคืบหน้า';
            }
        }

        $this->render('admin/dashboard', [
            'pageTitle' => 'แดชบอร์ดผู้ดูแลระบบ - RepairForSchool',
            'kpi' => $kpi,
            'overdueCount' => $overdueCount,
            'totalCost' => $totalCost,
            'workloads' => $workloads,
            'recentTickets' => $recentTickets,
            'healthStatus' => $healthStatus,
            'healthMessage' => $healthMessage,
        ]);
    }

    /**
     * Technician Dashboard
     */
    public function technician(): void
    {
        Auth::requireRole('technician');
        $user = Auth::user();

        $myTickets = RepairTicket::getByTechnician((int)$user->id);

        $pendingCount = 0;
        $inProcessCount = 0;
        $completedCount = 0;

        foreach ($myTickets as $t) {
            if ($t['status'] === 'pending') $pendingCount++;
            elseif ($t['status'] === 'in_process') $inProcessCount++;
            elseif ($t['status'] === 'completed') $completedCount++;
        }

        $this->render('technician/dashboard', [
            'pageTitle' => 'งานซ่อมของฉัน - ช่างเทคนิค',
            'myTickets' => $myTickets,
            'inProcessCount' => $inProcessCount,
            'completedCount' => $completedCount,
            'pendingCount' => $pendingCount,
        ]);
    }

    /**
     * Teacher / Staff Dashboard
     */
    public function teacher(): void
    {
        Auth::requireRole('teacher');
        $user = Auth::user();

        $myTickets = RepairTicket::getByMember((int)$user->id);

        $inProcessCount = 0;
        $pendingCount = 0;
        $completedCount = 0;

        foreach ($myTickets as $t) {
            if ($t['status'] === 'pending') $pendingCount++;
            elseif ($t['status'] === 'in_process') $inProcessCount++;
            elseif ($t['status'] === 'completed') $completedCount++;
        }

        $this->render('teacher/dashboard', [
            'pageTitle' => 'ประวัติการแจ้งซ่อมของฉัน - ครู/บุคลากร',
            'myTickets' => $myTickets,
            'inProcessCount' => $inProcessCount,
            'completedCount' => $completedCount,
            'pendingCount' => $pendingCount,
        ]);
    }

    /**
     * Real-time API Status endpoint
     */
    public function apiStatus(): void
    {
        $dbHealth = \App\Core\Database::checkHealth();
        $isConnected = ($dbHealth['status'] ?? '') === 'connected';
        $kpi = $isConnected ? RepairTicket::countByStatus() : null;

        $this->json([
            'status' => $isConnected ? 'ok' : 'degraded',
            'timestamp' => date('c'),
            'database' => [
                'status' => $dbHealth['status'] ?? 'unknown',
                'version' => $dbHealth['version'] ?? null,
                'tables_count' => $dbHealth['tables_count'] ?? 0,
            ],
            'kpi' => $kpi,
        ], $isConnected ? 200 : 503);
    }
}

