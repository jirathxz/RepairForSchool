<?php
use App\Models\RepairTicket;
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ใบแจ้งซ่อม <?= htmlspecialchars($ticket['ticket_no']) ?> - เอกสารทางการ</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

  <!-- Tailwind CSS Play CDN & Font Awesome 6 -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <style>
    body {
      font-family: 'Sarabun', sans-serif;
      color: #0f172a;
      background-color: #f1f5f9;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    .a4-sheet {
      width: 210mm;
      min-height: 297mm;
      padding: 16mm 18mm;
      margin: 0 auto;
      background: #ffffff;
      box-sizing: border-box;
    }

    .doc-table th, .doc-table td {
      padding: 6px 10px;
      font-size: 10pt;
      vertical-align: top;
      border: 1px solid #cbd5e1;
    }

    .doc-table th {
      background-color: #f8fafc !important;
      font-weight: 600;
      color: #334155;
    }

    @media print {
      body {
        background: transparent !important;
        padding: 0 !important;
      }
      .no-print {
        display: none !important;
      }
      .a4-sheet {
        width: 100% !important;
        min-height: auto !important;
        margin: 0 !important;
        padding: 8mm 10mm !important;
      }
      .doc-table th, .doc-table td {
        border-color: #333333 !important;
      }
    }
  </style>
</head>
<body class="py-6">

<!-- Print Control Toolbar -->
<div class="max-w-[210mm] mx-auto mb-6 px-4 sm:px-0 no-print">
  <div class="flex items-center justify-between bg-white p-5 rounded-3xl">
    <div class="flex items-center gap-3.5">
      <div class="w-12 h-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-xl">
        <i class="fa-solid fa-print" aria-hidden="true"></i>
      </div>
      <div>
        <h2 class="text-sm font-black text-slate-900">พิมพ์ใบสั่งงานและรายงานผลการซ่อม A4</h2>
        <p class="text-xs text-slate-500">เลือกปลายทางเป็น Save as PDF หรือสั่งพิมพ์ออกทางเครื่องพิมพ์ได้ทันที</p>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <button 
        type="button" 
        onclick="window.print()"
        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white font-bold text-xs transition-all active:scale-95"
      >
        <i class="fa-solid fa-print text-xs" aria-hidden="true"></i>
        <span>สั่งพิมพ์เอกสาร</span>
      </button>
      <button 
        type="button" 
        onclick="window.close()"
        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition-all"
      >
        <i class="fa-solid fa-xmark text-xs" aria-hidden="true"></i>
        <span>ปิด</span>
      </button>
    </div>
  </div>
</div>

<!-- A4 Printable Sheet -->
<div class="a4-sheet rounded-3xl">

  <!-- Institutional Header -->
  <div class="pb-4 mb-4 text-center">
    <div class="flex justify-between items-center text-[9pt] text-slate-600 mb-1">
      <div class="text-left leading-tight">
        ฝ่ายบริหารงานทั่วไปและอาคารสถานที่<br>
        โรงเรียนในสังกัดสำนักงานคณะกรรมการการศึกษาขั้นพื้นฐาน
      </div>
      <div class="text-right">
        <span class="inline-flex items-center px-3 py-1 rounded-xl bg-slate-100 font-mono text-[9pt] font-bold text-slate-800">
          <?= htmlspecialchars($ticket['ticket_no']) ?>
        </span>
      </div>
    </div>
    <h1 class="text-xl font-black uppercase tracking-wide text-slate-900 mt-2">ใบแจ้งซ่อมบำรุงและรายงานผลการแก้ไข</h1>
    <div class="text-xs text-slate-500 mt-0.5">Repair and Maintenance Service Work Order</div>
  </div>

  <!-- Document Meta & Basic Information Table -->
  <table class="w-full doc-table mb-4 border-collapse">
    <tbody>
      <tr>
        <th style="width: 18%;">เลขที่ใบแจ้งซ่อม</th>
        <td style="width: 32%; font-family: 'JetBrains Mono', monospace; font-weight: bold;">
          <?= htmlspecialchars($ticket['ticket_no']) ?>
        </td>
        <th style="width: 18%;">วันที่แจ้งซ่อม</th>
        <td style="width: 32%;">
          <?= date('d/m/Y H:i น.', strtotime($ticket['created_at'])) ?>
        </td>
      </tr>
      <tr>
        <th>สถานะงานซ่อม</th>
        <td>
          <strong><?= RepairTicket::getStatusLabel($ticket['status']) ?></strong>
        </td>
        <th>ระดับความเร่งด่วน</th>
        <td>
          <strong>
            <?= match ($ticket['priority']) {
              'urgent' => 'ด่วนที่สุด',
              'high' => 'ด่วน',
              'normal' => 'ปกติ',
              default => 'ไม่เร่งด่วน'
            } ?>
          </strong>
        </td>
      </tr>
      <tr>
        <th>ผู้แจ้งซ่อม</th>
        <td>
          <?= htmlspecialchars($ticket['member_name']) ?>
          <?php if (!empty($ticket['member_phone'])): ?>
            · โทร <?= htmlspecialchars($ticket['member_phone']) ?>
          <?php endif; ?>
        </td>
        <th>กลุ่มสาระ หรือฝ่าย</th>
        <td><?= htmlspecialchars($ticket['member_department'] ?? '-') ?></td>
      </tr>
      <tr>
        <th>สถานที่ชำรุด</th>
        <td colspan="3">
          <strong><?= htmlspecialchars($ticket['building']) ?></strong> — ห้องและจุดติดตั้ง <?= htmlspecialchars($ticket['room']) ?>
        </td>
      </tr>
      <tr>
        <th>ประเภทอุปกรณ์</th>
        <td colspan="3">
          <?= htmlspecialchars($ticket['equipment_name']) ?>
          <?php if (!empty($ticket['asset_code'])): ?>
            <span style="display: inline-block; margin-left: 12px; padding: 2px 8px; background: #f3f4f6; border-radius: 4px; font-size: 8.5pt;">
              <strong>ครุภัณฑ์:</strong> <span style="font-family: 'JetBrains Mono', monospace; font-weight: bold;"><?= htmlspecialchars($ticket['asset_code']) ?></span> - <?= htmlspecialchars($ticket['asset_name'] ?? '') ?>
            </span>
          <?php endif; ?>
        </td>
      </tr>
    </tbody>
  </table>

  <!-- Problem Description -->
  <div class="mb-4">
    <div class="font-bold text-[10pt] mb-1">๑. รายละเอียดปัญหาและอาการชำรุด:</div>
    <div class="p-3 bg-slate-50 text-[10pt] min-h-[50px] leading-relaxed rounded-xl">
      <strong>หัวข้อ: <?= htmlspecialchars($ticket['title']) ?></strong><br>
      <?= nl2br(htmlspecialchars($ticket['description'])) ?>
    </div>
  </div>

  <!-- Before & After Photos -->
  <div class="mb-4">
    <div class="font-bold text-[10pt] mb-1">๒. ภาพถ่ายหลักฐานก่อนและหลังดำเนินการซ่อม:</div>
    <div class="grid grid-cols-2 gap-3">
      <!-- Before -->
      <div class="p-3 text-center bg-slate-50 rounded-xl">
        <div class="font-bold text-xs text-slate-700 mb-1">ภาพถ่ายก่อนการซ่อม</div>
        <?php if (!empty($beforeImages)): ?>
          <img src="uploads/before/<?= htmlspecialchars($beforeImages[0]['file_name']) ?>" alt="Before Photo" class="max-h-36 max-w-full object-contain mx-auto rounded-lg">
        <?php else: ?>
          <div class="text-slate-400 text-xs py-8 font-medium">ไม่มีภาพแนบก่อนซ่อม</div>
        <?php endif; ?>
      </div>

      <!-- After -->
      <div class="p-3 text-center bg-slate-50 rounded-xl">
        <div class="font-bold text-xs text-slate-700 mb-1">ภาพถ่ายหลังซ่อมเสร็จสิ้น</div>
        <?php if (!empty($afterImages)): ?>
          <img src="uploads/after/<?= htmlspecialchars($afterImages[0]['file_name']) ?>" alt="After Photo" class="max-h-36 max-w-full object-contain mx-auto rounded-lg">
        <?php else: ?>
          <div class="text-slate-400 text-xs py-8 font-medium">อยู่ระหว่างดำเนินการ หรือไม่มีภาพ</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Technician Resolution & Cost -->
  <div class="mb-4">
    <div class="font-bold text-[10pt] mb-1">๓. บันทึกผลการแก้ไขของช่างและค่าใช้จ่าย:</div>
    <table class="w-full doc-table border-collapse">
      <tbody>
        <tr>
          <th style="width: 25%;">ช่างผู้รับผิดชอบ</th>
          <td style="width: 75%;">
            <strong><?= htmlspecialchars($ticket['technician_name'] ?? 'ยังไม่ได้ระบุ') ?></strong>
            <?php if (!empty($ticket['technician_phone'])): ?>
              · เบอร์ติดต่อ <?= htmlspecialchars($ticket['technician_phone']) ?>
            <?php endif; ?>
          </td>
        </tr>
        <tr>
          <th>ผลการตรวจซ่อม</th>
          <td>
            <?= !empty($ticket['technician_note']) ? nl2br(htmlspecialchars($ticket['technician_note'])) : 'อยู่ระหว่างดำเนินการตรวจสอบ' ?>
          </td>
        </tr>
        <tr>
          <th>รายการอะไหล่ หรือวัสดุ</th>
          <td><?= htmlspecialchars($ticket['cost_description'] ?? 'ไม่มีค่าใช้จ่ายอะไหล่') ?></td>
        </tr>
        <tr>
          <th>ค่าใช้จ่ายรวมทั้งสิ้น</th>
          <td>
            <strong class="text-[11pt]"><?= number_format((float)$ticket['repair_cost'], 2) ?> บาท</strong>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Formal Signature Blocks -->
  <div class="mt-8 pt-4">
    <div class="grid grid-cols-3 gap-4 text-center text-[10pt]">
      <!-- Teacher / Submitter -->
      <div>
        <div>ลงชื่อ ............................................................</div>
        <div class="mt-1 font-bold"><?= htmlspecialchars($ticket['member_name']) ?></div>
        <div class="text-slate-500 text-xs">ผู้แจ้งซ่อม และตรวจรับเบื้องต้น</div>
        <div class="text-slate-400 text-xs mt-1">วันที่ ........ / ........ / ................</div>
      </div>

      <!-- Technician -->
      <div>
        <div>ลงชื่อ ............................................................</div>
        <div class="mt-1 font-bold"><?= htmlspecialchars($ticket['technician_name'] ?? '................................................') ?></div>
        <div class="text-slate-500 text-xs">ช่างผู้ดำเนินการซ่อมบำรุง</div>
        <div class="text-slate-400 text-xs mt-1">วันที่ ........ / ........ / ................</div>
      </div>

      <!-- Administrator / Director -->
      <div>
        <div>ลงชื่อ ............................................................</div>
        <div class="mt-1 font-bold">............................................................</div>
        <div class="text-slate-500 text-xs">หัวหน้างานฝ่ายบริหารทั่วไป และผู้อนุมัติ</div>
        <div class="text-slate-400 text-xs mt-1">วันที่ ........ / ........ / ................</div>
      </div>
    </div>
  </div>

</div>

</body>
</html>
