<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\RepairTicket;
use App\Models\EquipmentType;

/**
 * Landing Page Controller
 * Public introduction page with ReactBits interactive components and system highlights.
 */
class LandingController extends Controller
{
    public function index(): void
    {
        // If user is already logged in, redirect to their role dashboard
        if (Auth::check()) {
            $this->redirect(Auth::defaultRedirectUrl());
        }

        $kpi = RepairTicket::countByStatus();
        $totalCost = RepairTicket::getTotalCost();
        $equipmentTypes = EquipmentType::allSorted();

        $this->render('landing', [
            'pageTitle' => 'RepairForSchool — ระบบแจ้งซ่อมบำรุงโรงเรียนยุคดิจิทัล',
            'kpi' => $kpi,
            'totalCost' => $totalCost,
            'equipmentTypes' => $equipmentTypes,
        ]);
    }
}
