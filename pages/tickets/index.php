<?php
require_once __DIR__ . '/../layout/header.php';
use App\Core\Auth;
use App\Models\RepairTicket;
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

  <!-- Title & Top Actions -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <div class="flex items-center gap-2 text-xs font-bold text-violet-600 uppercase tracking-wider mb-1">
        <i class="fa-solid fa-list-check" aria-hidden="true"></i>
        <span>ระบบบริหารงานซ่อม</span>
      </div>
      <h1 class="text-2xl font-black text-slate-900 tracking-tight font-display">รายการใบแจ้งซ่อม</h1>
      <p class="text-xs text-slate-500 mt-0.5">ค้นหา กรองสถานะ ติดตามความคืบหน้า และส่งออกรายงานสรุปงานซ่อมบำรุง</p>
    </div>
    <div class="flex flex-wrap items-center gap-2.5">
      <?php if (Auth::isAdmin()): ?>
        <?php
          $exportParams = http_build_query($filters);
        ?>
        <a 
          href="index.php?page=tickets-export&<?= $exportParams ?>" 
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all"
        >
          <i class="fa-solid fa-file-excel text-emerald-600" aria-hidden="true"></i>
          <span>ส่งออกไฟล์ CSV</span>
        </a>
      <?php endif; ?>

      <a 
        href="index.php?page=tickets-create" 
        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-600/30 active:scale-95"
      >
        <i class="fa-solid fa-circle-plus" aria-hidden="true"></i>
        <span>ส่งคำขอแจ้งซ่อมใหม่</span>
      </a>
    </div>
  </div>

  <!-- Filter Card -->
  <div class="bg-white rounded-3xl p-5 sm:p-6">
    <form action="index.php" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
      <input type="hidden" name="page" value="tickets">

      <!-- Search Keyword (3 cols) -->
      <div class="sm:col-span-2 lg:col-span-3">
        <label for="filter_search" class="block text-xs font-bold text-slate-600 mb-1.5">
          <i class="fa-solid fa-magnifying-glass text-slate-400 mr-1" aria-hidden="true"></i>ค้นหา
        </label>
        <input 
          id="filter_search"
          type="search" 
          name="search" 
          class="w-full px-4 py-2.5 text-xs rounded-2xl bg-slate-100 focus:bg-white text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all" 
          placeholder="คำสำคัญ หรือรหัส หรือชื่อ หรือห้อง" 
          value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
        >
      </div>

      <!-- Filter Status (2 cols) -->
      <div class="lg:col-span-2">
        <label for="filter_status" class="block text-xs font-bold text-slate-600 mb-1.5">สถานะ</label>
        <select id="filter_status" name="status" class="w-full px-4 py-2.5 text-xs rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all">
          <option value="">ทุกสถานะ</option>
          <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>รอดำเนินการ</option>
          <option value="in_process" <?= ($filters['status'] ?? '') === 'in_process' ? 'selected' : '' ?>>กำลังซ่อม</option>
          <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>เสร็จสิ้น</option>
        </select>
      </div>

      <!-- Filter Priority (2 cols) -->
      <div class="lg:col-span-2">
        <label for="filter_priority" class="block text-xs font-bold text-slate-600 mb-1.5">ความเร่งด่วน</label>
        <select id="filter_priority" name="priority" class="w-full px-4 py-2.5 text-xs rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all">
          <option value="">ทุกระดับ</option>
          <option value="urgent" <?= ($filters['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>ด่วนที่สุด</option>
          <option value="high" <?= ($filters['priority'] ?? '') === 'high' ? 'selected' : '' ?>>ด่วน</option>
          <option value="normal" <?= ($filters['priority'] ?? '') === 'normal' ? 'selected' : '' ?>>ปกติ</option>
          <option value="low" <?= ($filters['priority'] ?? '') === 'low' ? 'selected' : '' ?>>ไม่เร่งด่วน</option>
        </select>
      </div>

      <!-- Filter Technician (2 cols) -->
      <?php if (!Auth::isTechnician()): ?>
        <div class="lg:col-span-2">
          <label for="filter_technician" class="block text-xs font-bold text-slate-600 mb-1.5">ช่างผู้รับผิดชอบ</label>
          <select id="filter_technician" name="technician_id" class="w-full px-4 py-2.5 text-xs rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all">
            <option value="">ช่างทุกคน</option>
            <?php foreach ($technicians as $tech): ?>
              <option value="<?= $tech->id ?>" <?= ((string)($filters['technician_id'] ?? '') === (string)$tech->id) ? 'selected' : '' ?>>
                <?= htmlspecialchars($tech->full_name) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php endif; ?>

      <!-- Filter Building (2 cols) -->
      <div class="<?= Auth::isTechnician() ? 'lg:col-span-3' : 'lg:col-span-2' ?>">
        <label for="filter_building" class="block text-xs font-bold text-slate-600 mb-1.5">อาคาร</label>
        <select id="filter_building" name="building" class="w-full px-4 py-2.5 text-xs rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all">
          <option value="">ทุกอาคาร</option>
          <?php foreach ($buildings as $b): ?>
            <option value="<?= htmlspecialchars($b['building']) ?>" <?= (($filters['building'] ?? '') === $b['building']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($b['building']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Filter Action Buttons (1 col) -->
      <div class="flex items-center gap-2 <?= Auth::isTechnician() ? 'lg:col-span-2' : 'lg:col-span-1' ?>">
        <button 
          type="submit" 
          class="flex-1 min-h-[42px] py-2.5 px-3 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold transition-all text-center flex items-center justify-center gap-1.5 active:scale-95"
          title="กรองข้อมูล"
        >
          <i class="fa-solid fa-filter text-[10px]" aria-hidden="true"></i>
          <span>กรอง</span>
        </button>
        <a 
          href="index.php?page=tickets" 
          class="min-w-[42px] min-h-[42px] p-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 text-xs font-bold transition-all flex items-center justify-center"
          title="ล้างค่าการกรอง"
          aria-label="ล้างค่าการกรอง"
        >
          <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
        </a>
      </div>
    </form>
  </div>

  <!-- Tickets Listing Card (Desktop Table + Mobile Cards) -->
  <div class="bg-white rounded-3xl overflow-hidden">
    <div class="px-6 py-4 flex items-center justify-between bg-slate-50/60">
      <div class="flex items-center gap-2">
        <span class="text-xs font-black text-slate-800">ผลการค้นหา</span>
        <span class="px-2 py-0.5 rounded-full bg-violet-100 text-violet-700 text-[11px] font-bold tabular-nums"><?= count($tickets) ?> รายการ</span>
      </div>
    </div>

    <!-- 1. Desktop & Tablet View -->
    <div class="hidden sm:block overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-slate-50/80 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
          <tr>
            <th class="px-5 py-3.5">รหัสแจ้งซ่อม</th>
            <th class="px-5 py-3.5">หัวข้อ รายละเอียด</th>
            <th class="px-5 py-3.5">สถานที่</th>
            <th class="px-5 py-3.5">ประเภทอุปกรณ์</th>
            <th class="px-5 py-3.5">ผู้แจ้ง</th>
            <th class="px-5 py-3.5">ผู้รับผิดชอบ</th>
            <th class="px-5 py-3.5">กำหนดเสร็จ</th>
            <th class="px-5 py-3.5">สถานะ</th>
            <th class="px-5 py-3.5">ระดับ</th>
            <th class="px-5 py-3.5 text-right">จัดการ</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <?php if (empty($tickets)): ?>
            <tr>
              <td colspan="10" class="px-6 py-12 text-center text-slate-400">
                <i class="fa-regular fa-folder-open text-3xl mb-2 text-slate-300" aria-hidden="true"></i>
                <p class="font-bold">ไม่พบข้อมูลใบแจ้งซ่อมตามเงื่อนไขที่ระบุ</p>
                <a href="index.php?page=tickets" class="inline-block mt-2 text-violet-600 hover:underline text-xs font-bold">ล้างตัวกรองทั้งหมด</a>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($tickets as $t): ?>
              <?php 
                $isOverdue = (!empty($t['due_date']) && $t['status'] !== 'completed' && $t['due_date'] < date('Y-m-d'));
              ?>
              <tr class="hover:bg-slate-50/70 transition-colors <?= $isOverdue ? 'bg-rose-50/20' : '' ?>">
                <td class="px-5 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-xl bg-slate-100 text-slate-800 font-mono text-[11px] font-bold tabular-nums">
                    <?= htmlspecialchars($t['ticket_no']) ?>
                  </span>
                  <?php if ($isOverdue): ?>
                    <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-rose-500 text-white text-[9px] ml-1 font-bold" title="เกินกำหนด">!</span>
                  <?php endif; ?>
                  <div class="text-slate-400 text-[10px] mt-0.5 tabular-nums">
                    <?= date('d/m/y H:i', strtotime($t['created_at'])) ?>
                  </div>
                </td>
                <td class="px-5 py-4 max-w-[220px]">
                  <a href="index.php?page=tickets-show&id=<?= $t['id'] ?>" class="font-bold text-slate-900 hover:text-violet-600 block truncate transition-colors focus:outline-none focus-visible:underline">
                    <?= htmlspecialchars($t['title']) ?>
                  </a>
                  <span class="text-[11px] text-slate-400 block truncate mt-0.5">
                    <?= htmlspecialchars($t['description']) ?>
                  </span>
                  <?php if ((int)$t['image_count'] > 0 || (int)$t['comment_count'] > 0): ?>
                    <div class="flex items-center gap-2 mt-1 text-[10px]">
                      <?php if ((int)$t['image_count'] > 0): ?>
                        <span class="text-violet-600 flex items-center gap-1 font-bold">
                          <i class="fa-solid fa-image text-[9px]" aria-hidden="true"></i>
                          <span class="tabular-nums"><?= $t['image_count'] ?></span> ภาพ
                        </span>
                      <?php endif; ?>
                      <?php if ((int)$t['comment_count'] > 0): ?>
                        <span class="text-slate-500 flex items-center gap-1">
                          <i class="fa-solid fa-comments text-[9px]" aria-hidden="true"></i>
                          <span class="tabular-nums"><?= $t['comment_count'] ?></span>
                        </span>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>
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
                  <div class="font-bold text-slate-800"><?= htmlspecialchars($t['member_name']) ?></div>
                  <div class="text-[11px] text-slate-400"><?= htmlspecialchars($t['member_department'] ?? '') ?></div>
                </td>
                <td class="px-5 py-4 whitespace-nowrap">
                  <?php if (!empty($t['technician_name'])): ?>
                    <div class="font-bold text-violet-700 flex items-center gap-1">
                      <i class="fa-solid fa-user-gear text-[10px]" aria-hidden="true"></i>
                      <span><?= htmlspecialchars($t['technician_name']) ?></span>
                    </div>
                    <div class="text-[10px] text-slate-400 tabular-nums"><?= htmlspecialchars($t['technician_phone'] ?? '') ?></div>
                  <?php else: ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-xl bg-slate-100 text-slate-500 text-[10px] font-semibold">
                      ยังไม่มอบหมาย
                    </span>
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
                  <div class="inline-flex items-center gap-1.5">
                    <a 
                      href="index.php?page=tickets-show&id=<?= $t['id'] ?>" 
                      class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-slate-100 hover:bg-violet-100 text-slate-600 hover:text-violet-700 text-xs transition-colors"
                      title="ดูรายละเอียด"
                      aria-label="ดูรายละเอียดใบแจ้งซ่อม <?= htmlspecialchars($t['ticket_no']) ?>"
                    >
                      <i class="fa-solid fa-eye text-[11px]" aria-hidden="true"></i>
                    </a>
                    <a 
                      href="index.php?page=tickets-print&id=<?= $t['id'] ?>" 
                      target="_blank" 
                      class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 text-xs transition-colors"
                      title="พิมพ์ใบแจ้งซ่อม A4"
                      aria-label="พิมพ์ใบแจ้งซ่อม <?= htmlspecialchars($t['ticket_no']) ?>"
                    >
                      <i class="fa-solid fa-print text-[11px]" aria-hidden="true"></i>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- 2. Mobile Card List View -->
    <div class="block sm:hidden divide-y divide-slate-100">
      <?php if (empty($tickets)): ?>
        <div class="p-8 text-center text-slate-400">
          <i class="fa-regular fa-folder-open text-3xl mb-2 text-slate-300" aria-hidden="true"></i>
          <p class="font-bold text-xs">ไม่พบข้อมูลใบแจ้งซ่อมตามเงื่อนไขที่ระบุ</p>
          <a href="index.php?page=tickets" class="inline-block mt-2 text-violet-600 hover:underline text-xs font-bold">ล้างตัวกรองทั้งหมด</a>
        </div>
      <?php else: ?>
        <?php foreach ($tickets as $t): ?>
          <?php 
            $isOverdue = (!empty($t['due_date']) && $t['status'] !== 'completed' && $t['due_date'] < date('Y-m-d'));
          ?>
          <div class="p-4 space-y-3 hover:bg-slate-50/70 transition-colors <?= $isOverdue ? 'bg-rose-50/20' : '' ?>">
            
            <!-- Mobile Header: No, Priority, Date -->
            <div class="flex items-center justify-between gap-2">
              <div class="flex items-center gap-1.5">
                <span class="inline-flex items-center px-2.5 py-1 rounded-xl bg-slate-100 text-slate-800 font-mono text-xs font-bold tabular-nums">
                  <?= htmlspecialchars($t['ticket_no']) ?>
                </span>
                <?php if ($isOverdue): ?>
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-rose-500 text-white text-[10px] font-bold">เกินกำหนด</span>
                <?php endif; ?>
              </div>
              <div class="flex items-center gap-1.5">
                <?= RepairTicket::getPriorityBadge($t['priority']) ?>
                <?= RepairTicket::getStatusBadge($t['status']) ?>
              </div>
            </div>

            <!-- Title & Description -->
            <div>
              <a href="index.php?page=tickets-show&id=<?= $t['id'] ?>" class="font-bold text-slate-900 text-sm block hover:text-violet-600 leading-snug">
                <?= htmlspecialchars($t['title']) ?>
              </a>
              <?php if (!empty($t['description'])): ?>
                <p class="text-xs text-slate-500 line-clamp-2 mt-1 leading-relaxed">
                  <?= htmlspecialchars($t['description']) ?>
                </p>
              <?php endif; ?>
            </div>

            <!-- Location & Meta Chips -->
            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600">
              <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-slate-100 text-slate-700 text-[11px]">
                <i class="fa-solid fa-location-dot text-slate-400" aria-hidden="true"></i>
                <span><?= htmlspecialchars($t['building']) ?> ห้อง <?= htmlspecialchars($t['room']) ?></span>
              </span>
              <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-slate-100 text-slate-700 text-[11px]">
                <i class="<?= htmlspecialchars($t['equipment_icon'] ?? 'fa-solid fa-wrench') ?> text-slate-400" aria-hidden="true"></i>
                <span><?= htmlspecialchars($t['equipment_name']) ?></span>
              </span>
              <?php if (!empty($t['technician_name'])): ?>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-violet-50 text-violet-700 text-[11px] font-bold">
                  <i class="fa-solid fa-user-gear text-violet-500" aria-hidden="true"></i>
                  <span><?= htmlspecialchars($t['technician_name']) ?></span>
                </span>
              <?php endif; ?>
            </div>

            <!-- Mobile Action Bar -->
            <div class="pt-2 flex items-center gap-2">
              <a 
                href="index.php?page=tickets-show&id=<?= $t['id'] ?>" 
                class="flex-1 min-h-[44px] px-4 py-2 rounded-2xl bg-violet-50 hover:bg-violet-100 text-violet-700 text-xs font-bold flex items-center justify-center gap-2 transition-colors"
              >
                <i class="fa-solid fa-eye text-xs" aria-hidden="true"></i>
                <span>ดูรายละเอียดงาน</span>
              </a>
              <a 
                href="index.php?page=tickets-print&id=<?= $t['id'] ?>" 
                target="_blank" 
                class="min-w-[44px] min-h-[44px] px-3.5 py-2 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold flex items-center justify-center transition-colors"
                title="พิมพ์ใบแจ้งซ่อม A4"
                aria-label="พิมพ์ใบแจ้งซ่อม <?= htmlspecialchars($t['ticket_no']) ?>"
              >
                <i class="fa-solid fa-print" aria-hidden="true"></i>
              </a>
            </div>

          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

</div>

<?php
require_once __DIR__ . '/../layout/footer.php';
?>
