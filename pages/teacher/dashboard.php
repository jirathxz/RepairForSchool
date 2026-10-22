<?php
require_once __DIR__ . '/../layout/header.php';
use App\Models\RepairTicket;
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

  <!-- Header & Big CTA Banner -->
  <div class="bg-white rounded-3xl p-6 sm:p-8 flex flex-col md:flex-row md:items-center md:justify-between gap-6 relative overflow-hidden">
    <div class="relative z-10">
      <div class="flex items-center gap-2 text-xs font-bold text-violet-600 uppercase tracking-wider mb-2">
        <i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i>
        <span>ระบบแจ้งซ่อมสำหรับครูและบุคลากร</span>
      </div>
      <h1 class="text-2xl font-black text-slate-900 tracking-tight font-display">
        สวัสดีคุณ<?= htmlspecialchars($currentUser->full_name) ?>
        <?php if (!empty($currentUser->department)): ?>
          <span class="text-slate-500 font-medium text-lg">· <?= htmlspecialchars($currentUser->department) ?></span>
        <?php endif; ?>
      </h1>
      <p class="text-xs text-slate-500 mt-1 max-w-xl">
        พบอุปกรณ์ชำรุด อาคารสถานที่เสียหาย หรือระบบไฟและน้ำขัดข้อง สามารถส่งคำขอแจ้งซ่อมและติดตามขั้นตอนการแก้ไขได้ตลอด 24 ชั่วโมง
      </p>
    </div>

    <div class="relative z-10 flex-shrink-0">
      <a 
        href="index.php?page=tickets-create" 
        class="inline-flex items-center gap-2.5 px-6 py-3.5 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white font-bold text-sm transition-all active:scale-95 group"
      >
        <i class="fa-solid fa-circle-plus text-base transition-transform group-hover:rotate-90" aria-hidden="true"></i>
        <span>ส่งคำขอแจ้งซ่อมใหม่</span>
      </a>
    </div>
  </div>

  <!-- Status Metric Tiles -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <!-- Pending -->
    <div class="bg-white rounded-3xl p-6 relative overflow-hidden">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">รอดำเนินการ</span>
        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
          <i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>
        </div>
      </div>
      <div class="mt-4 flex items-baseline gap-2">
        <span class="text-3xl font-black text-amber-600 font-display tabular-nums"><?= $pendingCount ?></span>
        <span class="text-xs text-slate-400 font-medium">รายการ</span>
      </div>
      <p class="text-[11px] text-slate-400 mt-1">คำขอของคุณอยู่ระหว่างรอเจ้าหน้าที่มอบหมายงาน</p>
    </div>

    <!-- In Process -->
    <div class="bg-white rounded-3xl p-6 relative overflow-hidden">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">กำลังดำเนินการ</span>
        <div class="w-12 h-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-lg">
          <i class="fa-solid fa-gears" aria-hidden="true"></i>
        </div>
      </div>
      <div class="mt-4 flex items-baseline gap-2">
        <span class="text-3xl font-black text-violet-600 font-display tabular-nums"><?= $inProcessCount ?></span>
        <span class="text-xs text-slate-400 font-medium">รายการ</span>
      </div>
      <p class="text-[11px] text-slate-400 mt-1">ช่างรับงานและอยู่ระหว่างเข้าตรวจสอบหรือจัดหาอะไหล่</p>
    </div>

    <!-- Completed -->
    <div class="bg-white rounded-3xl p-6 relative overflow-hidden">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">เสร็จสิ้น</span>
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
          <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
        </div>
      </div>
      <div class="mt-4 flex items-baseline gap-2">
        <span class="text-3xl font-black text-emerald-600 font-display tabular-nums"><?= $completedCount ?></span>
        <span class="text-xs text-slate-400 font-medium">รายการ</span>
      </div>
      <p class="text-[11px] text-slate-400 mt-1">งานที่ดำเนินการแก้ไขเรียบร้อยแล้ว</p>
    </div>
  </div>

  <!-- Teacher's Ticket List Card -->
  <div class="bg-white rounded-3xl overflow-hidden">
    <div class="px-6 py-4 flex items-center justify-between bg-slate-50/60">
      <div class="flex items-center gap-2">
        <i class="fa-solid fa-clock-rotate-left text-violet-600" aria-hidden="true"></i>
        <h2 class="text-sm font-black text-slate-800">ประวัติการแจ้งซ่อมของฉัน</h2>
      </div>
      <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-violet-100 text-violet-700 tabular-nums">ทั้งหมด <?= count($myTickets) ?> รายการ</span>
    </div>

    <!-- Mobile card list (sm:hidden) -->
    <div class="sm:hidden divide-y divide-slate-100">
      <?php if (empty($myTickets)): ?>
        <div class="px-4 py-12 text-center text-slate-400">
          <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-xl mx-auto mb-3">
            <i class="fa-solid fa-inbox" aria-hidden="true"></i>
          </div>
          <div class="font-bold text-slate-700 text-sm">คุณยังไม่มีประวัติการแจ้งซ่อมในระบบ</div>
          <p class="text-[11px] text-slate-400 mt-1">กดปุ่มส่งคำขอแจ้งซ่อมใหม่ด้านบนได้ทันที</p>
        </div>
      <?php else: ?>
        <?php foreach ($myTickets as $t): ?>
          <a href="index.php?page=tickets-show&id=<?= $t['id'] ?>" class="flex items-start gap-3 px-5 py-4 hover:bg-slate-50 transition-colors">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="font-mono text-[11px] font-bold bg-slate-100 text-slate-700 px-2.5 py-0.5 rounded-xl tabular-nums"><?= htmlspecialchars($t['ticket_no']) ?></span>
                <?= RepairTicket::getStatusBadge($t['status']) ?>
              </div>
              <p class="font-bold text-slate-900 text-xs mt-1.5 truncate"><?= htmlspecialchars($t['title']) ?></p>
              <div class="flex items-center gap-3 mt-1 text-[11px] text-slate-500">
                <span class="flex items-center gap-1">
                  <i class="fa-solid fa-location-dot text-[10px] text-slate-400" aria-hidden="true"></i>
                  <?= htmlspecialchars($t['building']) ?> ห้อง <?= htmlspecialchars($t['room']) ?>
                </span>
                <?php if (!empty($t['technician_name'])): ?>
                  <span class="flex items-center gap-1">
                    <i class="fa-solid fa-user-gear text-[10px] text-violet-400" aria-hidden="true"></i>
                    <span class="text-violet-700 font-bold"><?= htmlspecialchars($t['technician_name']) ?></span>
                  </span>
                <?php else: ?>
                  <span class="px-2 py-0.5 rounded-xl bg-slate-100 text-slate-500 text-[10px] font-medium">รอมอบหมาย</span>
                <?php endif; ?>
              </div>
              <div class="text-[10px] text-slate-400 mt-1 tabular-nums"><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></div>
            </div>
            <i class="fa-solid fa-chevron-right text-[11px] text-slate-400 mt-1 flex-shrink-0" aria-hidden="true"></i>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Desktop table (hidden on mobile) -->
    <div class="hidden sm:block overflow-x-auto">
      <table class="min-w-full divide-y divide-slate-100">
        <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-left">
          <tr>
            <th class="px-5 py-3.5">เลขที่ใบแจ้งซ่อม</th>
            <th class="px-5 py-3.5">หัวข้อปัญหา</th>
            <th class="px-5 py-3.5">สถานที่</th>
            <th class="px-5 py-3.5">ประเภทอุปกรณ์</th>
            <th class="px-5 py-3.5">ช่างผู้รับผิดชอบ</th>
            <th class="px-5 py-3.5">สถานะปัจจุบัน</th>
            <th class="px-5 py-3.5">วันที่แจ้ง</th>
            <th class="px-5 py-3.5 text-right">รายละเอียด</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-xs">
          <?php if (empty($myTickets)): ?>
            <tr>
              <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-xl mx-auto mb-3">
                  <i class="fa-solid fa-inbox" aria-hidden="true"></i>
                </div>
                <div class="font-bold text-slate-700 text-sm">คุณยังไม่มีประวัติการแจ้งซ่อมในระบบ</div>
                <p class="text-[11px] text-slate-400 mt-1">หากมีอุปกรณ์หรือห้องเรียนชำรุด สามารถกดปุ่มส่งคำขอแจ้งซ่อมใหม่ด้านบนได้ทันที</p>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($myTickets as $t): ?>
              <tr class="hover:bg-slate-50/70 transition-colors">
                <td class="px-5 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-xl bg-slate-100 text-slate-800 font-mono text-[11px] font-bold tabular-nums">
                    <?= htmlspecialchars($t['ticket_no']) ?>
                  </span>
                </td>
                <td class="px-5 py-4 max-w-[220px]">
                  <a href="index.php?page=tickets-show&id=<?= $t['id'] ?>" class="font-bold text-slate-900 hover:text-violet-600 block truncate transition-colors focus:outline-none focus-visible:underline">
                    <?= htmlspecialchars($t['title']) ?>
                  </a>
                  <span class="text-[11px] text-slate-400 block truncate mt-0.5">
                    <?= htmlspecialchars($t['description']) ?>
                  </span>
                </td>
                <td class="px-5 py-4 whitespace-nowrap">
                  <strong class="text-slate-800 block"><?= htmlspecialchars($t['building']) ?></strong>
                  <span class="text-[11px] text-slate-400">ห้อง <?= htmlspecialchars($t['room']) ?></span>
                </td>
                <td class="px-5 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-100 text-slate-700 text-[11px] font-medium">
                    <i class="<?= htmlspecialchars($t['equipment_icon'] ?? 'fa-solid fa-wrench') ?> text-slate-400" aria-hidden="true"></i>
                    <span><?= htmlspecialchars($t['equipment_name']) ?></span>
                  </span>
                </td>
                <td class="px-5 py-4 whitespace-nowrap">
                  <?php if (!empty($t['technician_name'])): ?>
                    <div class="font-bold text-violet-700 flex items-center gap-1">
                      <i class="fa-solid fa-user-gear text-[10px]" aria-hidden="true"></i>
                      <span><?= htmlspecialchars($t['technician_name']) ?></span>
                    </div>
                    <div class="text-[10px] text-slate-400 flex items-center gap-1 mt-0.5 tabular-nums">
                      <i class="fa-solid fa-phone text-[9px]" aria-hidden="true"></i>
                      <span><?= htmlspecialchars($t['technician_phone'] ?? '-') ?></span>
                    </div>
                  <?php else: ?>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-xl bg-slate-100 text-slate-500 text-[10px] font-medium">
                      รอผู้ดูแลระบบมอบหมาย
                    </span>
                  <?php endif; ?>
                </td>
                <td class="px-5 py-4 whitespace-nowrap">
                  <?= RepairTicket::getStatusBadge($t['status']) ?>
                </td>
                <td class="px-5 py-4 whitespace-nowrap text-slate-500 text-[11px] tabular-nums">
                  <?= date('d/m/Y H:i', strtotime($t['created_at'])) ?>
                </td>
                <td class="px-5 py-4 whitespace-nowrap text-right">
                  <a 
                    href="index.php?page=tickets-show&id=<?= $t['id'] ?>" 
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl bg-slate-100 hover:bg-violet-100 text-slate-700 hover:text-violet-700 font-bold text-xs transition-all active:scale-95"
                    aria-label="ดูสถานะใบแจ้งซ่อม <?= htmlspecialchars($t['ticket_no']) ?>"
                  >
                    <i class="fa-solid fa-eye text-[10px]" aria-hidden="true"></i>
                    <span>ดูสถานะ</span>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php
require_once __DIR__ . '/../layout/footer.php';
?>
