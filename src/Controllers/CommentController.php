<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\RepairTicket;
use App\Models\TicketComment;
use App\Models\Notification;

/**
 * Comment Controller
 * Handles conversation notes and inquiries on repair tickets.
 */
class CommentController extends Controller
{
    /**
     * Store a comment on a ticket
     */
    public function store(): void
    {
        Auth::requireLogin();
        $this->validateCsrf();
        $user = Auth::user();

        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $commentText = trim((string)($_POST['comment'] ?? ''));

        if ($ticketId <= 0 || $commentText === '') {
            Session::setFlash('danger', 'กรุณากรอกข้อความแสดงความคิดเห็น');
            $this->redirectBack("index.php?page=tickets-show&id={$ticketId}");
        }

        $ticket = RepairTicket::getDetailedById($ticketId);
        if (!$ticket) {
            Session::setFlash('danger', 'ไม่พบใบแจ้งซ่อมที่ต้องการแสดงความคิดเห็น');
            $this->redirect('index.php?page=tickets');
        }

        // Authorization check: Teacher can only comment on own tickets
        if (Auth::isTeacher() && (int)$ticket['member_id'] !== (int)$user->id) {
            Session::setFlash('danger', 'คุณสามารถแสดงความคิดเห็นได้เฉพาะใบแจ้งซ่อมของตนเองเท่านั้น');
            $this->redirect('index.php?page=teacher-dashboard');
        }

        TicketComment::addComment($ticketId, (int)$user->id, $commentText);

        $shortComment = mb_substr($commentText, 0, 40) . (mb_strlen($commentText) > 40 ? '...' : '');

        // Notify technician if comment is by teacher/admin
        if (!empty($ticket['technician_id']) && (int)$ticket['technician_id'] !== (int)$user->id) {
            Notification::send((int)$ticket['technician_id'], 'มีความคิดเห็นใหม่ในงานซ่อม', "{$user->full_name}: {$shortComment}", $ticketId, 'info', "index.php?page=tickets-show&id={$ticketId}#comments-section");
        }
        // Notify teacher if comment is by technician/admin
        if ((int)$ticket['member_id'] !== (int)$user->id) {
            Notification::send((int)$ticket['member_id'], 'มีความคิดเห็นใหม่ในใบแจ้งซ่อม', "{$user->full_name}: {$shortComment}", $ticketId, 'info', "index.php?page=tickets-show&id={$ticketId}#comments-section");
        }

        Session::setFlash('success', 'เพิ่มข้อความความคิดเห็นเรียบร้อยแล้ว');

        $this->redirect("index.php?page=tickets-show&id={$ticketId}#comments-section");
    }
}
