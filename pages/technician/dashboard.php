<?php
require_once __DIR__ . '/../layout/header.php';
use App\Models\RepairTicket;
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

  <!-- Header -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 text-xs font-bold text-violet-600 uppercase tracking-wider mb-1">
        <i class="fa-solid fa-wrench" aria-hidden="true"></i>
        <span>ระบบสำหรับเจ้าหน้าที่ช่าง</span>
      </div>
      <h1 class="text-2xl font-black text-slate-900 tracking-tight font-display">แดชบอร์ดงานซ่อมของฉัน</h1>
      <p class="text-xs text-slate-500 mt-0.5">ยินดีต้อนรับ <?= htmlspecialchars($currentUser->full_name) ?> — จัดการงานซ่อมที่ได้รับมอบหมาย อัพเดทผลงาน และบันทึกค่าใช้จ่าย</p>
    </div>
    <div>
      <a 
        href="index.php?page=tickets" 
        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all"
      >
        <i class="fa-solid fa-list-check text-slate-500" aria-hidden="true"></i>
        <span>ดูงานแจ้งซ่อมทั้งหมดในระบบ</span>
      </a>
    </div>
  </div>

  <!-- Metric Counters -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
        <span class="text-xs text-slate-400 font-medium">งาน</span>
      </div>
      <p class="text-[11px] text-slate-400 mt-1">งานที่ต้องเข้าตรวจสอบและลงมือแก้ไข</p>
    </div>

    <!-- Completed -->
    <div class="bg-white rounded-3xl p-6 relative overflow-hidden">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">ซ่อมเสร็จสิ้น</span>
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
          <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
        </div>
      </div>
      <div class="mt-4 flex items-baseline gap-2">
        <span class="text-3xl font-black text-emerald-600 font-display tabular-nums"><?= $completedCount ?></span>
        <span class="text-xs text-slate-400 font-medium">งาน</span>
      </div>
      <p class="text-[11px] text-slate-400 mt-1">งานที่คุณส่งมอบและแก้ไขเรียบร้อยแล้ว</p>
    </div>

    <!-- Total Assigned -->
    <div class="bg-white rounded-3xl p-6 relative overflow-hidden">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">รวมงานที่ได้รับมอบหมาย</span>
        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center text-lg">
          <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
        </div>
      </div>
      <div class="mt-4 flex items-baseline gap-2">
        <span class="text-3xl font-black text-slate-900 font-display tabular-nums"><?= count($myTickets) ?></span>
        <span class="text-xs text-slate-400 font-medium">งาน</span>
      </div>
      <p class="text-[11px] text-slate-400 mt-1">ประวัติงานที่ผ่านมาทั้งหมดของคุณ</p>
    </div>
  </div>

  <!-- Assigned Tickets Table Card -->
  <div class="bg-white rounded-3xl overflow-hidden">
    <div class="px-6 py-4 flex items-center justify-between bg-slate-50/60">
      <div class="flex items-center gap-2">
        <i class="fa-solid fa-list-check text-violet-600" aria-hidden="true"></i>
        <h2 class="text-sm font-black text-slate-800">รายการงานซ่อมที่ได้รับมอบหมาย</h2>
      </div>
      <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-violet-100 text-violet-700 tabular-nums"><?= count($myTickets) ?> รายการ</span>
    </div>

    <!-- Mobile card list (sm:hidden) -->
    <div class="sm:hidden divide-y divide-slate-100">
      <?php if (empty($myTickets)): ?>
        <div class="px-4 py-12 text-center text-slate-400">
          <div class="w-12 h-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-xl mx-auto mb-3">
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
          </div>
          <div class="font-bold text-slate-700 text-sm">ขณะนี้ยังไม่มีงานซ่อมค้างในมือของคุณ</div>
          <p class="text-[11px] text-slate-400 mt-1">เมื่อผู้ดูแลระบบมอบหมายงาน รายการจะแสดงขึ้นที่นี่ทันที</p>
        </div>
      <?php else: ?>
        <?php foreach ($myTickets as $t): ?>
          <?php $isOverdue = (!empty($t['due_date']) && $t['status'] !== 'completed' && $t['due_date'] < date('Y-m-d')); ?>
          <a href="index.php?page=tickets-show&id=<?= $t['id'] ?>" class="flex items-start gap-3 px-5 py-4 <?= $isOverdue ? 'bg-rose-50/30' : '' ?> hover:bg-slate-50 transition-colors">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <span class="font-mono text-[11px] font-bold bg-slate-100 text-slate-700 px-2.5 py-0.5 rounded-xl tabular-nums"><?= htmlspecialchars($t['ticket_no']) ?></span>
                <?php if ($isOverdue): ?><span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-rose-500 text-white text-[9px] font-bold" title="เกินกำหนด">!</span><?php endif; ?>
                <?= RepairTicket::getStatusBadge($t['status']) ?>
                <?= RepairTicket::getPriorityBadge($t['priority']) ?>
              </div>
              <p class="font-bold text-slate-900 text-xs mt-1.5 truncate"><?= htmlspecialchars($t['title']) ?></p>
              <div class="flex items-center gap-3 mt-1 text-[11px] text-slate-500">
                <span class="flex items-center gap-1">
                  <i class="fa-solid fa-location-dot text-[10px] text-slate-400" aria-hidden="true"></i>
                  <?= htmlspecialchars($t['building']) ?> ห้อง <?= htmlspecialchars($t['room']) ?>
                </span>
                <span class="flex items-center gap-1">
                  <i class="fa-solid fa-user text-[10px] text-slate-400" aria-hidden="true"></i>
                  <?= htmlspecialchars($t['member_name']) ?>
                </span>
              </div>
              <?php if (!empty($t['due_date'])): ?>
                <div class="text-[10px] <?= $isOverdue ? 'text-rose-600 font-bold' : 'text-slate-400' ?> mt-1 tabular-nums">
                  <i class="fa-solid fa-calendar-day text-[9px]" aria-hidden="true"></i>
                  กำหนดเสร็จ <?= date('d/m/Y', strtotime($t['due_date'])) ?>
                </div>
              <?php endif; ?>
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
            <th class="px-5 py-3.5">เลขที่ใบซ่อม</th>
            <th class="px-5 py-3.5">หัวข้อและอาการ</th>
            <th class="px-5 py-3.5">สถานที่</th>
            <th class="px-5 py-3.5">ผู้แจ้ง เบอร์ติดต่อ</th>
            <th class="px-5 py-3.5">กำหนดเสร็จ</th>
            <th class="px-5 py-3.5">สถานะ</th>
            <th class="px-5 py-3.5">ความเร่งด่วน</th>
            <th class="px-5 py-3.5 text-right">การจัดการ</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-xs">
          <?php if (empty($myTickets)): ?>
            <tr>
              <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                <div class="w-12 h-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-xl mx-auto mb-3">
                  <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                </div>
                <div class="font-bold text-slate-700 text-sm">ขณะนี้ยังไม่มีงานซ่อมค้างในมือของคุณ</div>
                <p class="text-[11px] text-slate-400 mt-1">เมื่อผู้ดูแลระบบมอบหมายงาน รายการจะแสดงขึ้นที่นี่ทันที</p>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($myTickets as $t): ?>
              <?php $isOverdue = (!empty($t['due_date']) && $t['status'] !== 'completed' && $t['due_date'] < date('Y-m-d')); ?>
              <tr class="hover:bg-slate-50/70 transition-colors <?= $isOverdue ? 'bg-rose-50/20' : '' ?>">
                <td class="px-5 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-xl bg-slate-100 text-slate-800 font-mono text-[11px] font-bold tabular-nums">
                    <?= htmlspecialchars($t['ticket_no']) ?>
                  </span>
                  <?php if ($isOverdue): ?>
                    <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-rose-500 text-white text-[9px] ml-1 font-bold" title="เกินกำหนด">!</span>
                  <?php endif; ?>
                </td>
                <td class="px-5 py-4 max-w-[240px]">
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
                  <div class="font-bold text-slate-800"><?= htmlspecialchars($t['member_name']) ?></div>
                  <?php if (!empty($t['member_phone'])): ?>
                    <div class="text-[11px] text-violet-600 flex items-center gap-1 mt-0.5 font-bold">
                      <i class="fa-solid fa-phone text-[9px]" aria-hidden="true"></i>
                      <span><?= htmlspecialchars($t['member_phone']) ?></span>
                    </div>
                  <?php endif; ?>
                </td>
                <td class="px-5 py-4 whitespace-nowrap tabular-nums">
                  <?php if (!empty($t['due_date'])): ?>
                    <span class="<?= $isOverdue ? 'text-rose-600 font-bold' : 'text-slate-700 font-semibold' ?>">
                      <?= date('d/m/Y', strtotime($t['due_date'])) ?>
                    </span>
                  <?php else: ?>
                    <span class="text-slate-400">-</span>
                  <?php endif; ?>
                </td>
                <td class="px-5 py-4 whitespace-nowrap">
                  <?= RepairTicket::getStatusBadge($t['status']) ?>
                </td>
                <td class="px-5 py-4 whitespace-nowrap">
                  <?= RepairTicket::getPriorityBadge($t['priority']) ?>
                </td>
                <td class="px-5 py-4 whitespace-nowrap text-right">
                  <a 
                    href="index.php?page=tickets-show&id=<?= $t['id'] ?>" 
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white font-bold text-xs transition-all active:scale-95"
                    aria-label="บันทึกผลการซ่อมสำหรับใบซ่อม <?= htmlspecialchars($t['ticket_no']) ?>"
                  >
                    <i class="fa-solid fa-pen-to-square text-[10px]" aria-hidden="true"></i>
                    <span>บันทึกผลการซ่อม</span>
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
