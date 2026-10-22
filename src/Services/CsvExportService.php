<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\RepairTicket;

/**
 * CSV Export Service
 * Exports filtered repair ticket reports with UTF-8 BOM for Microsoft Excel.
 */
class CsvExportService
{
    /**
     * Generate and stream CSV file download
     *
     * @param array $tickets List of tickets to export
     */
    public static function exportTickets(array $tickets): void
    {
        $filename = 'repair_tickets_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // UTF-8 BOM for Microsoft Excel to properly decode Thai characters
        fputs($output, "\xEF\xBB\xBF");

        // Headers
        fputcsv($output, [
            'เลขที่ใบแจ้งซ่อม',
            'หัวข้อปัญหา',
            'รายละเอียด',
            'อาคาร',
            'ห้อง/จุดติดตั้ง',
            'ประเภทอุปกรณ์',
            'ระดับความเร่งด่วน',
            'สถานะ',
            'ผู้แจ้งซ่อม',
            'เบอร์โทรผู้แจ้ง',
            'ฝ่าย/กลุ่มสาระ',
            'ช่างผู้รับผิดชอบ',
            'เบอร์โทรช่าง',
            'กำหนดวันเสร็จ',
            'ผลการซ่อม/บันทึกช่าง',
            'ค่าใช้จ่าย (บาท)',
            'รายละเอียดค่าใช้จ่าย',
            'วันที่แจ้ง',
            'วันที่มอบหมาย',
            'วันที่เสร็จสิ้น',
        ]);

        $escape = static function(mixed $val): string {
            $str = (string)($val ?? '');
            if ($str !== '' && in_array($str[0], ['=', '+', '-', '@'], true)) {
                return "'" . $str;
            }
            return $str;
        };

        foreach ($tickets as $t) {
            $priorityLabel = match ($t['priority'] ?? '') {
                'urgent' => 'ด่วนที่สุด',
                'high' => 'ด่วน',
                'normal' => 'ปกติ',
                'low' => 'ไม่เร่งด่วน',
                default => (string)($t['priority'] ?? ''),
            };

            fputcsv($output, [
                $escape($t['ticket_no'] ?? ''),
                $escape($t['title'] ?? ''),
                $escape($t['description'] ?? ''),
                $escape($t['building'] ?? ''),
                $escape($t['room'] ?? ''),
                $escape($t['equipment_name'] ?? ''),
                $escape($priorityLabel),
                $escape(RepairTicket::getStatusLabel($t['status'] ?? '')),
                $escape($t['member_name'] ?? ''),
                $escape($t['member_phone'] ?? ''),
                $escape($t['member_department'] ?? ''),
                $escape($t['technician_name'] ?? 'ยังไม่ได้มอบหมาย'),
                $escape($t['technician_phone'] ?? ''),
                $escape($t['due_date'] ?? '-'),
                $escape($t['technician_note'] ?? '-'),
                number_format((float)($t['repair_cost'] ?? 0), 2, '.', ''),
                $escape($t['cost_description'] ?? '-'),
                $escape($t['created_at'] ?? ''),
                $escape($t['assigned_at'] ?? '-'),
                $escape($t['completed_at'] ?? '-'),
            ]);
        }

        fclose($output);
        exit;
    }
}
