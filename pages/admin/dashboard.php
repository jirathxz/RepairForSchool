<?php
require_once __DIR__ . '/../layout/header.php';
use App\Models\RepairTicket;
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

  <!-- Header & Action Buttons -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 text-xs font-bold text-violet-600 uppercase tracking-wider mb-1">
        <i class="fa-solid fa-gauge-high"></i>
        <span>ระบบบริหารงานซ่อมโรงเรียน</span>
      </div>
      <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight font-display">แดชบอร์ดสรุปผลการดำเนินงาน</h1>
      <p class="text-xs text-slate-500 mt-0.5">ภาพรวมสถานะการซ่อมบำรุง การกระจายงานช่าง และประสิทธิภาพการให้บริการ</p>
    </div>
    <div class="flex flex-wrap items-center gap-2.5">
      <a 
        href="index.php?page=tickets-export" 
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-semibold transition-colors"
      >
        <i class="fa-solid fa-file-excel text-emerald-600"></i>
        <span>ส่งออกรายงาน CSV</span>
      </a>
      <a 
        href="index.php?page=tickets" 
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold transition-colors"
      >
        <i class="fa-solid fa-clipboard-list"></i>
        <span>ดูใบแจ้งซ่อมทั้งหมด</span>
      </a>
    </div>
  </div>

  <!-- System Health Alert Banner (Flat, No Border, No Shadow) -->
  <?php
    $healthBg = match($healthStatus) { 'good' => 'bg-emerald-50', 'warning' => 'bg-amber-50', default => 'bg-rose-50' };
    $healthIconBg = match($healthStatus) { 'good' => 'bg-emerald-200 text-emerald-800', 'warning' => 'bg-amber-200 text-amber-800', default => 'bg-rose-200 text-rose-800' };
    $healthIcon = match($healthStatus) { 'good' => 'fa-shield-check', 'warning' => 'fa-triangle-exclamation', default => 'fa-circle-xmark' };
    $healthBadge = match($healthStatus) { 'good' => 'bg-emerald-600 text-white', 'warning' => 'bg-amber-500 text-white', default => 'bg-rose-600 text-white' };
    $healthLabel = match($healthStatus) { 'good' => 'เกณฑ์ดีเยี่ยม', 'warning' => 'เกณฑ์ปานกลาง', default => 'ต้องเฝ้าระวัง' };
  ?>
  <div class="rounded-3xl <?= $healthBg ?> p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div class="flex items-start sm:items-center gap-3.5">
      <div class="w-11 h-11 rounded-2xl <?= $healthIconBg ?> flex items-center justify-center text-lg flex-shrink-0">
        <i class="fa-solid <?= $healthIcon ?>"></i>
      </div>
      <div>
        <div class="flex items-center gap-2 flex-wrap">
          <span class="text-xs font-bold text-slate-800">ดัชนีชี้วัดสถานะงานซ่อมโรงเรียน:</span>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold <?= $healthBadge ?>">
            <?= $healthLabel ?>
          </span>
        </div>
        <p class="text-xs text-slate-600 mt-1"><?= htmlspecialchars($healthMessage) ?></p>
      </div>
    </div>
    <?php if ($overdueCount > 0): ?>
      <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-rose-200 text-rose-800 text-xs font-semibold flex-shrink-0">
        <i class="fa-solid fa-clock text-rose-600"></i>
        <span>เกินกำหนดเสร็จ <?= $overdueCount ?> รายการ</span>
      </div>
    <?php endif; ?>
  </div>

  <!-- KPI Metric Tiles (Flat Cards, No Border, No Shadow, Violet + Gray) -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    
    <!-- Total Tickets -->
    <div class="bg-violet-600 text-white rounded-3xl p-6 flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between">
          <span class="text-xs font-semibold text-violet-200 tracking-wider">งานแจ้งซ่อมสะสม</span>
          <span class="w-8 h-8 rounded-xl bg-violet-700/80 text-white flex items-center justify-center text-xs">
            <i class="fa-solid fa-folder-closed" aria-hidden="true"></i>
          </span>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
          <span class="text-3xl font-extrabold text-white font-display tracking-tight tabular-nums"><?= number_format($kpi['total']) ?></span>
          <span class="text-xs text-violet-200">รายการ</span>
        </div>
      </div>
      <div class="mt-4 pt-3 text-[11px] text-violet-200 flex items-center justify-between">
        <span>อัตราสำเร็จรวม</span>
        <strong class="text-white font-mono tabular-nums"><?= $kpi['total'] > 0 ? round(($kpi['completed'] / $kpi['total']) * 100, 1) : 100 ?>%</strong>
      </div>
    </div>

    <!-- Pending -->
    <div class="bg-white rounded-3xl p-6 flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-700 tracking-wide">รอดำเนินการ</span>
          <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center text-xs">
            <i class="fa-solid fa-hourglass-half" aria-hidden="true"></i>
          </span>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
          <span class="text-3xl font-extrabold text-amber-600 font-display tracking-tight tabular-nums"><?= number_format($kpi['pending']) ?></span>
          <span class="text-xs text-slate-500 font-medium">รายการ</span>
        </div>
      </div>
      <div class="mt-4 pt-3 flex items-center justify-between">
        <?php if ($kpi['pending'] > 0): ?>
          <a href="index.php?page=tickets&status=pending" class="text-[11px] font-bold text-amber-700 hover:text-amber-800 flex items-center gap-1 group">
            <span>มอบหมายงาน</span>
            <i class="fa-solid fa-arrow-right text-[9px] group-hover:translate-x-0.5 transition-transform" aria-hidden="true"></i>
          </a>
        <?php else: ?>
          <span class="text-[11px] text-slate-400">ไม่มีงานค้าง</span>
        <?php endif; ?>
      </div>
    </div>

    <!-- In Process -->
    <div class="bg-white rounded-3xl p-6 flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-700 tracking-wide">กำลังดำเนินการ</span>
          <span class="w-8 h-8 rounded-xl bg-violet-100 text-violet-800 flex items-center justify-center text-xs">
            <i class="fa-solid fa-wrench" aria-hidden="true"></i>
          </span>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
          <span class="text-3xl font-extrabold text-violet-600 font-display tracking-tight tabular-nums"><?= number_format($kpi['in_process']) ?></span>
          <span class="text-xs text-slate-500 font-medium">รายการ</span>
        </div>
      </div>
      <div class="mt-4 pt-3 text-[11px] text-slate-500 flex items-center justify-between">
        <span>สัดส่วนในระบบ</span>
        <strong class="text-slate-800 font-mono tabular-nums"><?= $kpi['total'] > 0 ? round(($kpi['in_process'] / $kpi['total']) * 100, 0) : 0 ?>%</strong>
      </div>
    </div>

    <!-- Completed -->
    <div class="bg-white rounded-3xl p-6 flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold text-slate-700 tracking-wide">เสร็จสิ้น</span>
          <span class="w-8 h-8 rounded-xl bg-slate-200 text-slate-800 flex items-center justify-center text-xs">
            <i class="fa-solid fa-check" aria-hidden="true"></i>
          </span>
        </div>
        <div class="mt-3 flex items-baseline gap-2">
          <span class="text-3xl font-extrabold text-slate-800 font-display tracking-tight tabular-nums"><?= number_format($kpi['completed']) ?></span>
          <span class="text-xs text-slate-500 font-medium">รายการ</span>
        </div>
      </div>
      <div class="mt-4 pt-3 text-[11px] text-slate-600 flex items-center justify-between">
        <span>ค่าใช้จ่ายรวม</span>
        <strong class="text-slate-900 font-mono tabular-nums"><?= number_format($totalCost, 0) ?> บาท</strong>
      </div>
    </div>
  </div>

  <!-- Workload & Recent Tickets Section -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    <!-- Left: Technician Workload Distribution (5 cols) -->
    <div class="lg:col-span-5 bg-white rounded-3xl flex flex-col overflow-hidden">
      <div class="px-6 py-4 flex items-center justify-between bg-slate-50">
        <div class="flex items-center gap-2">
          <i class="fa-solid fa-users-gear text-violet-600"></i>
          <h2 class="text-sm font-bold text-slate-800">ภาระงานช่างซ่อมบำรุง</h2>
        </div>
        <span class="px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-slate-200 text-slate-700"><?= count($workloads) ?> คน</span>
      </div>

      <div class="p-6 divide-y divide-slate-100">
        <?php if (empty($workloads)): ?>
          <p class="text-xs text-slate-400 text-center py-6">ยังไม่มีข้อมูลช่างเทคนิคในระบบ</p>
        <?php else: ?>
          <?php foreach ($workloads as $tech): ?>
            <?php 
              $active = (int)$tech['active_jobs'];
              $completed = (int)$tech['completed_jobs'];
              $total = (int)$tech['total_assigned'];
              $percentActive = $total > 0 ? round(($active / $total) * 100) : 0;
            ?>
            <div class="py-4 first:pt-0 last:pb-0 space-y-2">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-xs font-bold text-slate-900"><?= htmlspecialchars($tech['full_name']) ?></h3>
                  <p class="text-[11px] text-slate-500 flex items-center gap-1.5 mt-0.5">
                    <i class="fa-solid fa-phone text-[10px] text-slate-400"></i>
                    <span><?= htmlspecialchars($tech['phone'] ?? '-') ?></span>
                  </p>
                </div>
                <div class="flex items-center gap-1.5 text-[11px]">
                  <span class="px-2 py-0.5 rounded-lg bg-violet-100 text-violet-800 font-semibold">
                    กำลังทำ <?= $active ?>
                  </span>
                  <span class="px-2 py-0.5 rounded-lg bg-slate-200 text-slate-700 font-semibold">
                    เสร็จแล้ว <?= $completed ?>
                  </span>
                </div>
              </div>

              <!-- Multi-segment Progress Bar -->
              <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden flex">
                <div class="bg-violet-600 h-full" style="width: <?= $percentActive ?>%" title="กำลังดำเนินการ <?= $percentActive ?>%"></div>
                <div class="bg-slate-300 h-full" style="width: <?= 100 - $percentActive ?>%" title="เสร็จสิ้น"></div>
              </div>

              <div class="flex justify-between text-[10px] text-slate-400">
                <span>งานในมือ: <strong class="text-slate-700"><?= $active ?></strong> งาน</span>
                <span>รวมที่ได้รับมอบหมาย: <strong class="text-slate-700"><?= $total ?></strong> งาน</span>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Right: Recent Tickets (7 cols) -->
    <div class="lg:col-span-7 bg-white rounded-3xl flex flex-col overflow-hidden">
      <div class="px-6 py-4 flex items-center justify-between bg-slate-50">
        <div class="flex items-center gap-2">
          <i class="fa-solid fa-clock-rotate-left text-violet-600"></i>
          <h2 class="text-sm font-bold text-slate-800">รายการแจ้งซ่อมล่าสุด 8 รายการ</h2>
        </div>
        <a href="index.php?page=tickets" class="text-xs font-semibold text-violet-600 hover:text-violet-700 flex items-center gap-1">
          <span>ดูทั้งหมด</span>
          <i class="fa-solid fa-arrow-right text-[10px]"></i>
        </a>
      </div>

      <!-- Mobile card list (sm:hidden) -->
      <div class="sm:hidden divide-y divide-slate-100">
        <?php if (empty($recentTickets)): ?>
          <div class="px-4 py-8 text-center text-slate-400 text-xs">ยังไม่มีรายการแจ้งซ่อม</div>
        <?php else: ?>
          <?php foreach ($recentTickets as $t): ?>
            <a href="index.php?page=tickets-show&id=<?= $t['id'] ?>" class="flex items-start gap-3 px-4 py-4 hover:bg-slate-50 transition-colors">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="font-mono text-[11px] font-semibold bg-slate-100 text-slate-700 px-2 py-0.5 rounded-md tabular-nums"><?= htmlspecialchars($t['ticket_no']) ?></span>
                  <?= RepairTicket::getStatusBadge($t['status']) ?>
                  <?= RepairTicket::getPriorityBadge($t['priority']) ?>
                </div>
                <p class="font-bold text-slate-900 text-xs mt-1.5 truncate"><?= htmlspecialchars($t['title']) ?></p>
                <div class="flex items-center gap-3 mt-1 text-[11px] text-slate-500">
                  <span class="flex items-center gap-1"><i class="fa-solid fa-location-dot text-[10px] text-slate-400" aria-hidden="true"></i><?= htmlspecialchars($t['building']) ?> ห้อง <?= htmlspecialchars($t['room']) ?></span>
                  <span class="flex items-center gap-1"><i class="fa-solid fa-user text-[10px] text-slate-400" aria-hidden="true"></i><?= htmlspecialchars($t['member_name']) ?></span>
                </div>
              </div>
              <i class="fa-solid fa-chevron-right text-[11px] text-slate-400 mt-1 flex-shrink-0" aria-hidden="true"></i>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Desktop table (hidden on mobile) -->
      <div class="hidden sm:block overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100">
          <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-left">
            <tr>
              <th class="px-4 py-3">รหัส</th>
              <th class="px-4 py-3">หัวข้อและสถานที่</th>
              <th class="px-4 py-3">ผู้แจ้ง</th>
              <th class="px-4 py-3">สถานะ</th>
              <th class="px-4 py-3">ความเร่งด่วน</th>
              <th class="px-4 py-3 text-right">จัดการ</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-xs">
            <?php if (empty($recentTickets)): ?>
              <tr>
                <td colspan="6" class="px-4 py-8 text-center text-slate-400">ยังไม่มีรายการแจ้งซ่อม</td>
              </tr>
            <?php else: ?>
              <?php foreach ($recentTickets as $t): ?>
                <tr class="hover:bg-slate-50 transition-colors">
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-slate-100 text-slate-800 font-mono text-[11px] font-semibold tabular-nums">
                      <?= htmlspecialchars($t['ticket_no']) ?>
                    </span>
                    <?php if (!empty($t['due_date']) && $t['status'] !== 'completed' && $t['due_date'] < date('Y-m-d')): ?>
                      <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-rose-500 text-white text-[9px] ml-1" title="เกินกำหนดแล้ว">!</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-3 max-w-[200px]">
                    <a href="index.php?page=tickets-show&id=<?= $t['id'] ?>" class="font-bold text-slate-900 hover:text-violet-600 block truncate transition-colors">
                      <?= htmlspecialchars($t['title']) ?>
                    </a>
                    <span class="text-[11px] text-slate-500 flex items-center gap-1 truncate mt-0.5">
                      <i class="fa-solid fa-location-dot text-[10px] text-slate-400" aria-hidden="true"></i>
                      <span><?= htmlspecialchars($t['building']) ?> ห้อง <?= htmlspecialchars($t['room']) ?></span>
                    </span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <div class="font-medium text-slate-800"><?= htmlspecialchars($t['member_name']) ?></div>
                    <div class="text-[11px] text-slate-400"><?= htmlspecialchars($t['member_department'] ?? '') ?></div>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <?= RepairTicket::getStatusBadge($t['status']) ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <?= RepairTicket::getPriorityBadge($t['priority']) ?>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-right">
                    <a 
                      href="index.php?page=tickets-show&id=<?= $t['id'] ?>" 
                      class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-slate-100 hover:bg-violet-100 hover:text-violet-700 text-slate-600 text-xs transition-colors"
                      title="ดูรายละเอียดและมอบหมาย"
                      aria-label="ดูรายละเอียดและมอบหมายงาน <?= htmlspecialchars($t['ticket_no']) ?>"
                    >
                      <i class="fa-solid fa-arrow-right text-[11px]" aria-hidden="true"></i>
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

</div>

<?php
require_once __DIR__ . '/../layout/footer.php';
?>
