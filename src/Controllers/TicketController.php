<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\EquipmentType;
use App\Models\RepairTicket;
use App\Models\TicketComment;
use App\Models\TicketImage;
use App\Models\User;
use App\Models\Asset;
use App\Models\SparePart;
use App\Models\TicketPart;
use App\Models\Notification;
use App\Services\CsvExportService;
use App\Services\FileUploadService;

/**
 * Ticket Controller
 * Core workflow for repair requests, assignment, updates, photo uploads, and exports.
 */
class TicketController extends Controller
{
    /**
     * List tickets with filters and search
     */
    public function index(): void
    {
        Auth::requireLogin();
        $currentUser = Auth::user();

        $filters = [
            'status' => $_GET['status'] ?? '',
            'priority' => $_GET['priority'] ?? '',
            'technician_id' => $_GET['technician_id'] ?? '',
            'equipment_type_id' => $_GET['equipment_type_id'] ?? '',
            'building' => $_GET['building'] ?? '',
            'search' => $_GET['search'] ?? '',
        ];

        // Role scoping
        if (Auth::isTeacher()) {
            $filters['member_id'] = (int)$currentUser->id;
        } elseif (Auth::isTechnician()) {
            // Technicians see assigned tickets by default, or all if searching
            if (empty($filters['technician_id']) && empty($filters['search'])) {
                $filters['technician_id'] = (int)$currentUser->id;
            }
        }

        $tickets = RepairTicket::getFiltered($filters);
        $technicians = User::getTechnicians();
        $equipmentTypes = EquipmentType::allSorted();
        $buildings = RepairTicket::getBuildingsList();

        $this->render('tickets/index', [
            'pageTitle' => 'รายการแจ้งซ่อมทั้งหมด - RepairForSchool',
            'tickets' => $tickets,
            'filters' => $filters,
            'technicians' => $technicians,
            'equipmentTypes' => $equipmentTypes,
            'buildings' => $buildings,
        ]);
    }

    /**
     * Show detailed ticket view
     */
    public function show(): void
    {
        Auth::requireLogin();
        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            Session::setFlash('danger', 'ไม่พบรหัสใบแจ้งซ่อมที่ระบุ');
            $this->redirect('index.php?page=tickets');
        }

        $ticket = RepairTicket::getDetailedById($id);

        if (!$ticket) {
            Session::setFlash('danger', 'ไม่พบข้อมูลใบแจ้งซ่อม');
            $this->redirect('index.php?page=tickets');
        }

        // Access authorization check
        $currentUserId = (int)Auth::id();
        if (Auth::isTeacher() && (int)$ticket['member_id'] !== $currentUserId) {
            Session::setFlash('danger', 'คุณสามารถดูได้เฉพาะใบแจ้งซ่อมของตนเองเท่านั้น');
            $this->redirect('index.php?page=teacher-dashboard');
        }

        $beforeImages = TicketImage::getByTicket($id, 'before');
        $afterImages = TicketImage::getByTicket($id, 'after');
        $comments = TicketComment::getByTicket($id);
        $logs = RepairTicket::getLogs($id);
        $technicians = User::getTechnicians();
        $spareParts = SparePart::all();
        $ticketParts = TicketPart::getByTicket($id);

        $this->render('tickets/show', [
            'pageTitle' => "ใบแจ้งซ่อม {$ticket['ticket_no']} - RepairForSchool",
            'ticket' => $ticket,
            'beforeImages' => $beforeImages,
            'afterImages' => $afterImages,
            'comments' => $comments,
            'logs' => $logs,
            'technicians' => $technicians,
            'spareParts' => $spareParts,
            'ticketParts' => $ticketParts,
        ]);
    }

    /**
     * Show repair request creation form
     */
    public function create(): void
    {
        Auth::requireLogin();

        $equipmentTypes = EquipmentType::allSorted();
        $assets = Asset::all();

        $selectedAsset = null;
        if (!empty($_GET['asset_token'])) {
            $selectedAsset = Asset::findByQrToken(trim((string)$_GET['asset_token']));
        } elseif (!empty($_GET['asset_id'])) {
            $selectedAsset = Asset::find((int)$_GET['asset_id']);
        }

        $this->render('tickets/create', [
            'pageTitle' => 'แจ้งซ่อมใหม่ - RepairForSchool',
            'equipmentTypes' => $equipmentTypes,
            'assets' => $assets,
            'selectedAsset' => $selectedAsset,
        ]);
    }

    /**
     * Store newly submitted repair request
     */
    public function store(): void
    {
        Auth::requireLogin();
        $this->validateCsrf('index.php?page=tickets-create');
        $currentUser = Auth::user();

        $title = trim((string)($_POST['title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $building = trim((string)($_POST['building'] ?? ''));
        $room = trim((string)($_POST['room'] ?? ''));
        $equipmentTypeId = (int)($_POST['equipment_type_id'] ?? 0);
        $priority = in_array($_POST['priority'] ?? '', ['low', 'normal', 'high', 'urgent'], true)
            ? $_POST['priority']
            : 'normal';

        if ($title === '' || $description === '' || $building === '' || $room === '' || $equipmentTypeId <= 0) {
            Session::setFlash('danger', 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน (หัวข้อ, รายละเอียด, อาคาร, ห้อง, และประเภทอุปกรณ์)');
            $this->redirect('index.php?page=tickets-create');
        }

        $assetId = !empty($_POST['asset_id']) ? (int)$_POST['asset_id'] : null;

        // Create ticket record with collision-retry protection
        $ticket = RepairTicket::createWithUniqueTicketNo([
            'member_id' => (int)$currentUser->id,
            'equipment_type_id' => $equipmentTypeId,
            'asset_id' => $assetId,
            'title' => $title,
            'description' => $description,
            'building' => $building,
            'room' => $room,
            'status' => 'pending',
            'priority' => $priority,
        ]);

        if ($assetId) {
            Asset::updateStatus($assetId, 'maintenance');
        }

        $ticketId = (int)$ticket->id;
        $ticketNo = (string)$ticket->ticket_no;

        // Log creation
        RepairTicket::addStatusLog(
            $ticketId,
            (int)$currentUser->id,
            null,
            'pending',
            "ส่งคำขอแจ้งซ่อม (ระดับความเร่งด่วน: {$priority})"
        );

        // Notify Admins about new ticket
        Notification::sendToRole('admin', 'มีคำขอแจ้งซ่อมใหม่', "{$currentUser->full_name} ส่งคำขอแจ้งซ่อม [{$ticketNo}]: {$title}", $ticketId, 'info');

        // Process optional Before image upload
        if (isset($_FILES['before_image']) && $_FILES['before_image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = FileUploadService::uploadImage($_FILES['before_image'], 'before', $ticketNo);
            if ($uploadResult['success']) {
                TicketImage::addImage(
                    $ticketId,
                    (int)$currentUser->id,
                    'before',
                    $uploadResult['fileName'],
                    $uploadResult['originalName'] ?? null,
                    $uploadResult['fileSize'] ?? null,
                    $uploadResult['mimeType'] ?? null,
                    'ภาพถ่ายความเสียหายก่อนดำเนินการซ่อม'
                );
            } else {
                Session::setFlash('warning', 'บันทึกคำขอแล้ว แต่ไม่สามารถอัพโหลดภาพได้: ' . ($uploadResult['error'] ?? ''));
            }
        }

        Session::setFlash('success', "ส่งคำขอแจ้งซ่อมสำเร็จ รหัสใบแจ้งซ่อมคือ {$ticketNo}");
        $this->redirect("index.php?page=tickets-show&id={$ticketId}");
    }

    /**
     * Admin assigns technician and due date
     */
    public function assign(): void
    {
        Auth::requireRole('admin');
        $this->validateCsrf();
        $adminUser = Auth::user();

        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $technicianId = (int)($_POST['technician_id'] ?? 0);
        $dueDate = !empty($_POST['due_date']) ? trim((string)$_POST['due_date']) : null;
        $priority = !empty($_POST['priority']) ? trim((string)$_POST['priority']) : null;

        if ($ticketId <= 0 || $technicianId <= 0) {
            Session::setFlash('danger', 'กรุณาเลือกช่างผู้รับผิดชอบงานซ่อม');
            $this->redirectBack("index.php?page=tickets-show&id={$ticketId}");
        }

        $success = RepairTicket::assignTechnician(
            $ticketId,
            $technicianId,
            $dueDate,
            $priority,
            (int)$adminUser->id
        );

        if ($success) {
            $t = RepairTicket::getDetailedById($ticketId);
            if ($t) {
                Notification::send($technicianId, 'ได้รับมอบหมายงานซ่อมใหม่', "คุณได้รับมอบหมายงาน [{$t['ticket_no']}]: {$t['title']}", $ticketId, 'info');
                Notification::send((int)$t['member_id'], 'ใบแจ้งซ่อมได้รับการมอบหมายแล้ว', "ใบแจ้งซ่อม [{$t['ticket_no']}] ได้รับการมอบหมายให้ช่างแล้ว", $ticketId, 'info');
            }
            Session::setFlash('success', 'มอบหมายงานซ่อมและกำหนดวันแล้วเสร็จเรียบร้อย');
        } else {
            Session::setFlash('danger', 'ไม่สามารถมอบหมายงานได้ กรุณาลองใหม่อีกครั้ง');
        }

        $this->redirect("index.php?page=tickets-show&id={$ticketId}");
    }

    /**
     * Technician updates progress, costs, and uploads after photo
     */
    public function updateTechnician(): void
    {
        Auth::requireRole('admin', 'technician');
        $this->validateCsrf();
        $user = Auth::user();

        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $status = in_array($_POST['status'] ?? '', ['in_process', 'completed'], true) ? $_POST['status'] : 'in_process';
        $technicianNote = trim((string)($_POST['technician_note'] ?? ''));
        $repairCost = (float)($_POST['repair_cost'] ?? 0.0);
        $costDescription = trim((string)($_POST['cost_description'] ?? ''));

        $ticket = RepairTicket::find($ticketId);
        if (!$ticket) {
            Session::setFlash('danger', 'ไม่พบใบแจ้งซ่อม');
            $this->redirect('index.php?page=tickets');
        }

        // Process optional After image upload
        if (isset($_FILES['after_image']) && $_FILES['after_image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = FileUploadService::uploadImage($_FILES['after_image'], 'after', (string)$ticket->ticket_no);
            if ($uploadResult['success']) {
                TicketImage::addImage(
                    $ticketId,
                    (int)$user->id,
                    'after',
                    $uploadResult['fileName'],
                    $uploadResult['originalName'] ?? null,
                    $uploadResult['fileSize'] ?? null,
                    $uploadResult['mimeType'] ?? null,
                    'ภาพถ่ายยืนยันผลการซ่อมเสร็จสิ้น'
                );
            } else {
                Session::setFlash('warning', 'อัพเดทข้อมูลแล้ว แต่ไม่สามารถอัพโหลดภาพหลังซ่อมได้: ' . ($uploadResult['error'] ?? ''));
            }
        }

        $success = RepairTicket::updateTechnicianProgress(
            $ticketId,
            $status,
            $technicianNote !== '' ? $technicianNote : null,
            $repairCost,
            $costDescription !== '' ? $costDescription : null,
            (int)$user->id
        );

        if ($success) {
            $t = RepairTicket::getDetailedById($ticketId);
            if ($status === 'completed' && $t) {
                Notification::send((int)$t['member_id'], 'งานซ่อมเสร็จสิ้นแล้ว - โปรดตรวจรับงาน', "ช่างดำเนินการแก้ไขใบแจ้งซ่อม [{$t['ticket_no']}] เรียบร้อยแล้ว โปรดตรวจรับงานและประเมินผล", $ticketId, 'success');
                Notification::sendToRole('admin', 'งานซ่อมเสร็จสิ้น', "ใบแจ้งซ่อม [{$t['ticket_no']}] ซ่อมเสร็จสิ้นแล้ว รอผู้แจ้งตรวจรับงาน", $ticketId, 'success');
            }
            $statusThai = RepairTicket::getStatusLabel($status);
            Session::setFlash('success', "บันทึกผลการซ่อมและอัพเดทสถานะเป็น \"{$statusThai}\" เรียบร้อยแล้ว");
        } else {
            Session::setFlash('danger', 'ไม่สามารถบันทึกข้อมูลได้ กรุณาลองใหม่อีกครั้ง');
        }

        $this->redirect("index.php?page=tickets-show&id={$ticketId}");
    }

    /**
     * Export tickets to Excel-compatible CSV (Admin only)
     */
    public function exportCsv(): void
    {
        Auth::requireRole('admin');

        $filters = [
            'status' => $_GET['status'] ?? '',
            'priority' => $_GET['priority'] ?? '',
            'technician_id' => $_GET['technician_id'] ?? '',
            'equipment_type_id' => $_GET['equipment_type_id'] ?? '',
            'building' => $_GET['building'] ?? '',
            'search' => $_GET['search'] ?? '',
        ];

        $tickets = RepairTicket::getFiltered($filters);
        CsvExportService::exportTickets($tickets);
    }

    /**
     * Render printable A4 / PDF work order
     */
    public function printTicket(): void
    {
        Auth::requireLogin();
        $id = (int)($_GET['id'] ?? 0);

        $ticket = RepairTicket::getDetailedById($id);
        if (!$ticket) {
            Session::setFlash('danger', 'ไม่พบใบแจ้งซ่อมสำหรับการพิมพ์');
            $this->redirect('index.php?page=tickets');
        }

        $beforeImages = TicketImage::getByTicket($id, 'before');
        $afterImages = TicketImage::getByTicket($id, 'after');
        $logs = RepairTicket::getLogs($id);

        $this->render('shared/ticket_print', [
            'pageTitle' => "พิมพ์ใบแจ้งซ่อม {$ticket['ticket_no']}",
            'ticket' => $ticket,
            'beforeImages' => $beforeImages,
            'afterImages' => $afterImages,
            'logs' => $logs,
        ]);
    }

    /**
     * Teacher accepts completed work and rates satisfaction (System B)
     */
    public function accept(): void
    {
        Auth::requireLogin();
        $this->validateCsrf();

        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 5);
        $comment = trim((string)($_POST['rating_comment'] ?? ''));

        $ticket = RepairTicket::getDetailedById($ticketId);
        if (!$ticket) {
            Session::setFlash('danger', 'ไม่พบใบแจ้งซ่อม');
            $this->redirect('index.php?page=tickets');
        }

        // Only submitter or admin can accept
        $currentUserId = (int)Auth::id();
        if ((int)$ticket['member_id'] !== $currentUserId && !Auth::isAdmin()) {
            $this->forbidden('คุณไม่มีสิทธิ์ตรวจรับใบแจ้งซ่อมนี้');
        }

        RepairTicket::acceptAndRate($ticketId, $rating, $comment ?: null, $currentUserId);
        
        // If asset linked, return asset status to active
        if (!empty($ticket['asset_id'])) {
            Asset::updateStatus((int)$ticket['asset_id'], 'active');
        }

        // Notify technician and admins of acceptance
        if (!empty($ticket['technician_id'])) {
            Notification::send((int)$ticket['technician_id'], 'ผู้แจ้งตรวจรับงานซ่อมแล้ว', "ผู้แจ้งตรวจรับงาน [{$ticket['ticket_no']}] เรียบร้อยแล้ว (คะแนน {$rating}/5 ดาว)", $ticketId, 'success');
        }
        Notification::sendToRole('admin', 'ตรวจรับงานซ่อมสำเร็จ', "ใบแจ้งซ่อม [{$ticket['ticket_no']}] ผ่านการตรวจรับแล้ว (คะแนน {$rating}/5 ดาว)", $ticketId, 'success');

        Session::setFlash('success', "ตรวจรับงานซ่อมเรียบร้อยแล้ว ขอบคุณสำหรับการประเมินความพึงพอใจ ({$rating}/5 ดาว)");
        $this->redirect("index.php?page=tickets-show&id={$ticketId}");
    }

    /**
     * Teacher requests rework if issue persists (System B)
     */
    public function rework(): void
    {
        Auth::requireLogin();
        $this->validateCsrf();

        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $reason = trim((string)($_POST['rework_reason'] ?? ''));

        $ticket = RepairTicket::getDetailedById($ticketId);
        if (!$ticket) {
            Session::setFlash('danger', 'ไม่พบใบแจ้งซ่อม');
            $this->redirect('index.php?page=tickets');
        }

        $currentUserId = (int)Auth::id();
        if ((int)$ticket['member_id'] !== $currentUserId && !Auth::isAdmin()) {
            $this->forbidden('คุณไม่มีสิทธิ์ดำเนินการนี้');
        }

        if (empty($reason)) {
            Session::setFlash('danger', 'กรุณาระบุเหตุผลหรืออาการที่ยังไม่เรียบร้อย');
            $this->redirect("index.php?page=tickets-show&id={$ticketId}");
        }

        RepairTicket::requestRework($ticketId, $reason, $currentUserId);

        // Notify technician and admin
        if (!empty($ticket['technician_id'])) {
            Notification::send((int)$ticket['technician_id'], 'งานซ่อมถูกส่งกลับแก้ไข (Rework)', "ผู้แจ้งขอให้แก้ไขเพิ่มเติมสำหรับ [{$ticket['ticket_no']}]: {$reason}", $ticketId, 'warning');
        }
        Notification::sendToRole('admin', 'ส่งกลับแก้ไขงานซ่อม (Rework)', "ใบแจ้งซ่อม [{$ticket['ticket_no']}] ถูกส่งกลับแก้ไข", $ticketId, 'warning');

        Session::setFlash('warning', 'ส่งคำขอแก้ไขงานซ้ำ (Rework) เรียบร้อยแล้ว แจ้งเตือนไปยังช่างผู้รับผิดชอบแล้ว');
        $this->redirect("index.php?page=tickets-show&id={$ticketId}");
    }

    /**
     * Technician deducts spare parts used for this repair (System C)
     */
    public function addPart(): void
    {
        if (!Auth::isAdmin() && !Auth::isTechnician()) {
            $this->forbidden('คุณไม่มีสิทธิ์บันทึกการเบิกอะไหล่');
        }
        $this->validateCsrf();

        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $partId = (int)($_POST['spare_part_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);

        if ($ticketId <= 0 || $partId <= 0 || $quantity <= 0) {
            Session::setFlash('danger', 'กรุณาเลือกอะไหล่และระบุจำนวนที่ต้องการเบิก');
            $this->redirect("index.php?page=tickets-show&id={$ticketId}");
        }

        try {
            TicketPart::addPart($ticketId, $partId, $quantity);
            Session::setFlash('success', 'บันทึกการเบิกอะไหล่และตัดสต็อกในคลังเรียบร้อยแล้ว');
        } catch (\Throwable $e) {
            Session::setFlash('danger', 'เกิดข้อผิดพลาดในการตัดสต็อก: ' . $e->getMessage());
        }

        $this->redirect("index.php?page=tickets-show&id={$ticketId}");
    }

    /**
     * Remove part and restore stock (System C)
     */
    public function removePart(): void
    {
        if (!Auth::isAdmin() && !Auth::isTechnician()) {
            $this->forbidden('คุณไม่มีสิทธิ์แก้ไขรายการอะไหล่');
        }
        $this->validateCsrf();

        $ticketPartId = (int)($_POST['ticket_part_id'] ?? 0);
        $ticketId = (int)($_POST['ticket_id'] ?? 0);

        if ($ticketPartId > 0) {
            TicketPart::removePart($ticketPartId);
            Session::setFlash('success', 'ลบรายการและคืนอะไหล่กลับเข้าสต็อกเรียบร้อยแล้ว');
        }

        $this->redirect("index.php?page=tickets-show&id={$ticketId}");
    }
}
