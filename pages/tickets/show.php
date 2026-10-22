<?php
require_once __DIR__ . '/../layout/header.php';
use App\Core\Auth;
use App\Models\RepairTicket;

$isOverdue = (!empty($ticket['due_date']) && $ticket['status'] !== 'completed' && $ticket['due_date'] < date('Y-m-d'));
$canAssign = Auth::isAdmin();
$canUpdateTech = Auth::isAdmin() || (Auth::isTechnician() && (int)$ticket['technician_id'] === (int)Auth::id());
$isSubmitterOrAdmin = Auth::isAdmin() || ((int)$ticket['member_id'] === (int)Auth::id());
$reworkCount = (int)($ticket['rework_count'] ?? 0);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

  <!-- Breadcrumbs & Top Action Bar -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1.5">
        <a href="index.php" class="hover:text-violet-600 transition-colors font-medium">หน้าหลัก</a>
        <i class="fa-solid fa-chevron-right text-[10px] text-slate-400" aria-hidden="true"></i>
        <a href="index.php?page=tickets" class="hover:text-violet-600 transition-colors font-medium">ใบแจ้งซ่อม</a>
        <i class="fa-solid fa-chevron-right text-[10px] text-slate-400" aria-hidden="true"></i>
        <span class="text-slate-900 font-bold font-mono"><?= htmlspecialchars($ticket['ticket_no']) ?></span>
      </nav>

      <div class="flex items-center gap-2.5 flex-wrap">
        <h1 class="text-2xl font-black text-slate-900 tracking-tight font-display">
          ใบแจ้งซ่อม <span class="font-mono text-violet-600"><?= htmlspecialchars($ticket['ticket_no']) ?></span>
        </h1>
        <?= RepairTicket::getStatusBadge($ticket['status']) ?>
        <?= RepairTicket::getPriorityBadge($ticket['priority']) ?>
        <?php if ($isOverdue): ?>
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700">
            <i class="fa-solid fa-triangle-exclamation text-rose-600" aria-hidden="true"></i>
            <span>เลยกำหนดแล้วเสร็จ</span>
          </span>
        <?php endif; ?>
      </div>
    </div>

    <!-- Actions: Print, Back -->
    <div class="flex items-center gap-2 flex-wrap">
      <a 
        href="index.php?page=tickets-print&id=<?= $ticket['id'] ?>" 
        target="_blank" 
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all"
      >
        <i class="fa-solid fa-print text-slate-500" aria-hidden="true"></i>
        <span>พิมพ์ใบแจ้งซ่อม A4</span>
      </a>
      <a 
        href="index.php?page=tickets" 
        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition-all"
      >
        <i class="fa-solid fa-arrow-left text-[10px]" aria-hidden="true"></i>
        <span>กลับ</span>
      </a>
    </div>
  </div>

  <?php if ($reworkCount > 0 && $ticket['status'] !== 'completed'): ?>
    <div class="p-4 rounded-3xl bg-amber-50 text-amber-900 text-xs flex items-center justify-between gap-3">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-xl bg-amber-200 text-amber-800 flex items-center justify-center font-bold text-sm flex-shrink-0">
          <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
        </div>
        <div>
          <strong class="text-sm font-black block">ใบแจ้งซ่อมนี้ถูกส่งกลับแก้ไขซ้ำ รอบที่ <?= $reworkCount ?></strong>
          <p class="text-amber-700 text-xs mt-0.5">ผู้แจ้งขอให้ดำเนินการแก้ไขเพิ่มเติม ช่างเทคนิคกำลังตรวจสอบแก้ไขอีกครั้ง</p>
        </div>
      </div>
      <span class="px-3 py-1 rounded-full bg-amber-200 text-amber-800 text-[11px] font-bold flex-shrink-0">
        รอบที่ <?= $reworkCount ?>
      </span>
    </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
    
    <!-- Left Column: Details, Images, Notes, Comments, Logs (8 cols) -->
    <div class="lg:col-span-8 space-y-6">

      <!-- System B: Work Acceptance & 5-Star Rating Card -->
      <?php if ($ticket['status'] === 'completed'): ?>
        <?php if (empty($ticket['accepted_at'])): ?>
          <div class="bg-white rounded-3xl overflow-hidden">
            <div class="px-6 py-4 bg-violet-600 text-white flex items-center justify-between">
              <div class="flex items-center gap-2 font-black text-sm">
                <i class="fa-solid fa-clipboard-check text-base" aria-hidden="true"></i>
                <span>การตรวจรับงานซ่อมและประเมินผล</span>
              </div>
              <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-white/20 text-white">
                รอตรวจรับงาน
              </span>
            </div>

            <div class="p-6 space-y-5">
              <?php if ($isSubmitterOrAdmin): ?>
                <div>
                  <p class="text-xs text-slate-600 leading-relaxed">
                    ช่างเทคนิคได้ทำการซ่อมแซมและส่งมอบงานเรียบร้อยแล้ว โปรดตรวจสอบการทำงานของอุปกรณ์ หากใช้งานได้ตามปกติ ให้ประเมินความพึงพอใจและกดยืนยันตรวจรับงาน
                  </p>
                </div>

                <!-- Accept & Rating Form -->
                <form action="index.php?page=tickets-accept" method="POST" class="space-y-4 p-5 rounded-2xl bg-slate-50">
                  <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">
                  <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">

                  <div>
                    <label class="block text-xs font-bold text-slate-700 mb-2">
                      ระดับความพึงพอใจในงานซ่อมและการบริการ <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-5 gap-2">
                      <?php
                        $ratingOptions = [
                          5 => ['label' => 'ยอดเยี่ยม', 'stars' => 5],
                          4 => ['label' => 'ดีมาก', 'stars' => 4],
                          3 => ['label' => 'ปานกลาง', 'stars' => 3],
                          2 => ['label' => 'พอใช้', 'stars' => 2],
                          1 => ['label' => 'ปรับปรุง', 'stars' => 1],
                        ];
                        foreach ($ratingOptions as $val => $opt):
                      ?>
                        <label class="cursor-pointer">
                          <input type="radio" name="rating" value="<?= $val ?>" <?= $val === 5 ? 'checked' : '' ?> class="peer sr-only">
                          <div class="p-3 rounded-2xl bg-white peer-checked:bg-violet-600 peer-checked:text-white text-center transition-all flex flex-col items-center justify-center gap-1 hover:bg-slate-100">
                            <div class="flex items-center gap-0.5 text-xs text-amber-400 peer-checked:text-amber-200">
                              <i class="fa-solid fa-star" aria-hidden="true"></i>
                            </div>
                            <span class="text-xs font-bold font-mono"><?= $val ?> ดาว</span>
                            <span class="text-[10px] text-slate-500 peer-checked:text-violet-100 font-medium"><?= $opt['label'] ?></span>
                          </div>
                        </label>
                      <?php endforeach; ?>
                    </div>
                  </div>

                  <div>
                    <label for="rating_comment" class="block text-xs font-bold text-slate-700 mb-1.5">
                      ข้อคิดเห็นหรือข้อเสนอแนะเพิ่มเติม
                    </label>
                    <textarea 
                      id="rating_comment" 
                      name="rating_comment" 
                      rows="2" 
                      class="w-full px-4 py-2.5 rounded-2xl bg-white text-slate-800 text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all" 
                      placeholder="เช่น ซ่อมได้รวดเร็วเรียบร้อยดี อุปกรณ์กลับมาใช้งานได้ปกติ"
                    ></textarea>
                  </div>

                  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-1">
                    <button 
                      type="submit" 
                      class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white font-bold text-xs transition-all active:scale-95"
                    >
                      <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                      <span>ยืนยันตรวจรับงานซ่อม</span>
                    </button>

                    <button 
                      type="button" 
                      onclick="document.getElementById('reworkFormSection').classList.toggle('hidden')" 
                      class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-2xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs transition-all"
                    >
                      <i class="fa-solid fa-rotate-left text-slate-500" aria-hidden="true"></i>
                      <span>งานยังไม่เรียบร้อย ขอส่งกลับแก้ไข</span>
                    </button>
                  </div>
                </form>

                <!-- Rework Form (Hidden toggle) -->
                <div id="reworkFormSection" class="hidden p-5 rounded-2xl bg-rose-50 space-y-3">
                  <div class="flex items-center gap-2 text-rose-900 font-black text-xs">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600" aria-hidden="true"></i>
                    <span>ส่งกลับแก้ไขงานซ่อม</span>
                  </div>
                  <p class="text-[11px] text-rose-700 leading-relaxed">
                    หากอุปกรณ์ยังใช้งานไม่ได้ตามปกติ หรือชิ้นส่วนยังมีปัญหา ให้ระบุสาเหตุเพื่อให้ช่างนำกลับไปแก้ไขเพิ่มเติม
                  </p>
                  <form action="index.php?page=tickets-rework" method="POST" class="space-y-3">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">
                    <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                    <textarea 
                      name="rework_reason" 
                      rows="3" 
                      required 
                      class="w-full px-4 py-2.5 rounded-2xl bg-white text-slate-800 text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-500/20 transition-all" 
                      placeholder="ระบุอาการหรือจุดที่ยังทำงานไม่เรียบร้อยให้ชัดเจน"
                    ></textarea>
                    <div class="flex justify-end gap-2">
                      <button 
                        type="button" 
                        onclick="document.getElementById('reworkFormSection').classList.add('hidden')" 
                        class="px-4 py-2 rounded-2xl bg-white text-slate-600 text-xs font-bold hover:bg-slate-100 transition-colors"
                      >
                        ยกเลิก
                      </button>
                      <button 
                        type="submit" 
                        class="px-5 py-2 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition-all active:scale-95"
                      >
                        <i class="fa-solid fa-paper-plane mr-1" aria-hidden="true"></i>
                        <span>ยืนยันส่งกลับแก้ไข</span>
                      </button>
                    </div>
                  </form>
                </div>

              <?php else: ?>
                <div class="p-6 text-center text-xs text-slate-500 space-y-1">
                  <i class="fa-solid fa-hourglass-half text-2xl text-violet-600 mb-2 block" aria-hidden="true"></i>
                  <p class="font-bold text-slate-800">งานซ่อมเสร็จสิ้นแล้ว</p>
                  <p>อยู่ระหว่างรอผู้แจ้งซ่อม <?= htmlspecialchars($ticket['member_name']) ?> ดำเนินการตรวจรับงานและประเมินผล</p>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php else: ?>
          <!-- Acceptance Result Card (Accepted) -->
          <div class="bg-white rounded-3xl overflow-hidden">
            <div class="px-6 py-4 bg-emerald-50 text-emerald-900 flex items-center justify-between">
              <div class="flex items-center gap-2 font-black text-sm">
                <i class="fa-solid fa-circle-check text-emerald-600" aria-hidden="true"></i>
                <span>ผ่านการตรวจรับงานซ่อมแล้ว</span>
              </div>
              <span class="text-xs text-emerald-700 font-medium">
                ตรวจรับเมื่อ <?= date('d/m/Y H:i น.', strtotime($ticket['accepted_at'])) ?>
              </span>
            </div>

            <div class="p-6 space-y-4">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-2xl bg-slate-50">
                <div>
                  <span class="text-xs text-slate-500 block mb-1">ผลการประเมินความพึงพอใจ</span>
                  <div class="flex items-center gap-2">
                    <div class="flex items-center text-amber-400 text-sm gap-0.5">
                      <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?php if ($i <= (int)$ticket['rating']): ?>
                          <i class="fa-solid fa-star" aria-hidden="true"></i>
                        <?php else: ?>
                          <i class="fa-regular fa-star text-slate-300" aria-hidden="true"></i>
                        <?php endif; ?>
                      <?php endfor; ?>
                    </div>
                    <span class="text-sm font-black text-slate-900"><?= (int)$ticket['rating'] ?> ดาวเต็ม 5</span>
                  </div>
                </div>

                <div class="text-right text-xs">
                  <span class="text-slate-400 block mb-0.5">ผู้ตรวจรับงาน</span>
                  <strong class="text-slate-900 font-bold"><?= htmlspecialchars($ticket['acceptor_name'] ?? $ticket['member_name']) ?></strong>
                </div>
              </div>

              <?php if (!empty($ticket['rating_comment'])): ?>
                <div class="p-4 rounded-2xl bg-slate-50 text-xs text-slate-700 space-y-1">
                  <span class="text-slate-400 font-bold block text-[10px] uppercase tracking-wider">ความคิดเห็นของผู้ตรวจรับ</span>
                  <p class="leading-relaxed"><?= htmlspecialchars($ticket['rating_comment']) ?></p>
                </div>
              <?php endif; ?>

              <?php if ($reworkCount > 0): ?>
                <p class="text-[11px] text-slate-400">
                  <i class="fa-solid fa-circle-info mr-1" aria-hidden="true"></i>ใบแจ้งซ่อมนี้ผ่านการส่งกลับแก้ไขรวม <?= $reworkCount ?> ครั้ง ก่อนการตรวจรับเสร็จสมบูรณ์
                </p>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      <?php endif; ?>

      <!-- 1. Problem Information Card -->
      <div class="bg-white rounded-3xl overflow-hidden">
        <div class="px-6 py-4 flex items-center justify-between bg-slate-50/60">
          <div class="flex items-center gap-2 text-slate-800 font-black text-sm">
            <i class="fa-solid fa-circle-info text-violet-600" aria-hidden="true"></i>
            <span>ข้อมูลรายละเอียดปัญหา</span>
          </div>
          <span class="text-slate-400 text-xs">
            แจ้งเมื่อ <?= date('d/m/Y H:i น.', strtotime($ticket['created_at'])) ?>
          </span>
        </div>

        <div class="p-6 space-y-5">
          <h2 class="text-xl font-black text-slate-900"><?= htmlspecialchars($ticket['title']) ?></h2>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-5 rounded-2xl bg-slate-50 text-xs">
            <div>
              <span class="text-slate-400 block mb-1">
                <i class="fa-solid fa-wrench mr-1" aria-hidden="true"></i>ประเภทอุปกรณ์ งานซ่อม
              </span>
              <strong class="text-slate-800 text-sm flex items-center gap-1.5">
                <i class="<?= htmlspecialchars($ticket['equipment_icon'] ?? 'fa-solid fa-wrench') ?> text-violet-600" aria-hidden="true"></i>
                <span><?= htmlspecialchars($ticket['equipment_name']) ?></span>
              </strong>
            </div>

            <div>
              <span class="text-slate-400 block mb-1">
                <i class="fa-solid fa-location-dot mr-1" aria-hidden="true"></i>สถานที่จุดชำรุด
              </span>
              <strong class="text-slate-800 text-sm">
                <?= htmlspecialchars($ticket['building']) ?> ห้อง <?= htmlspecialchars($ticket['room']) ?>
              </strong>
            </div>

            <?php if (!empty($ticket['asset_code'])): ?>
              <div class="sm:col-span-2 p-3.5 rounded-2xl bg-violet-50 flex items-center justify-between gap-3 flex-wrap">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-violet-600 text-white flex items-center justify-center text-lg font-bold flex-shrink-0">
                    <i class="fa-solid fa-qrcode" aria-hidden="true"></i>
                  </div>
                  <div>
                    <span class="text-[10px] font-bold text-violet-700 uppercase tracking-wider block">ครุภัณฑ์ที่เชื่อมโยง</span>
                    <strong class="text-slate-900 font-mono text-sm mr-2"><?= htmlspecialchars($ticket['asset_code']) ?></strong>
                    <span class="text-slate-800 font-bold"><?= htmlspecialchars($ticket['asset_name'] ?? '') ?></span>
                    <?php if (!empty($ticket['asset_brand']) || !empty($ticket['asset_model'])): ?>
                      <span class="text-slate-500 text-xs block mt-0.5"><?= htmlspecialchars(trim(($ticket['asset_brand'] ?? '') . ' ' . ($ticket['asset_model'] ?? ''))) ?></span>
                    <?php endif; ?>
                  </div>
                </div>
                <a href="index.php?page=assets&search=<?= urlencode($ticket['asset_code']) ?>" class="px-3.5 py-2 rounded-xl bg-white hover:bg-violet-100 text-violet-700 text-xs font-bold transition-colors flex items-center gap-1.5">
                  <i class="fa-solid fa-arrow-up-right-from-square text-[10px]" aria-hidden="true"></i>
                  <span>ดูข้อมูลครุภัณฑ์</span>
                </a>
              </div>
            <?php endif; ?>
          </div>

          <div>
            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">รายละเอียดอาการที่ระบุ</span>
            <div class="p-5 rounded-2xl bg-slate-50 text-slate-800 text-sm whitespace-pre-wrap leading-relaxed">
              <?= htmlspecialchars($ticket['description']) ?>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. Before & After Images Comparison Card -->
      <div class="bg-white rounded-3xl overflow-hidden">
        <div class="px-6 py-4 bg-slate-50/60 flex items-center gap-2 text-slate-800 font-black text-sm">
          <i class="fa-solid fa-images text-violet-600" aria-hidden="true"></i>
          <span>ภาพถ่ายหลักฐานก่อนและหลังซ่อม</span>
        </div>

        <div class="p-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

            <!-- Before Image -->
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                  <i class="fa-solid fa-camera text-slate-400" aria-hidden="true"></i>
                  <span>ภาพก่อนซ่อม</span>
                </span>
                <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-600 text-[10px] font-bold">
                  แนบโดยผู้แจ้ง
                </span>
              </div>

              <?php if (!empty($beforeImages)): ?>
                <?php foreach ($beforeImages as $img): ?>
                  <div class="group relative rounded-2xl overflow-hidden bg-slate-100 aspect-video flex items-center justify-center">
                    <img 
                      src="uploads/before/<?= htmlspecialchars($img['file_name']) ?>" 
                      alt="ภาพก่อนซ่อมของใบแจ้งซ่อม <?= htmlspecialchars($ticket['ticket_no']) ?>: <?= htmlspecialchars($img['caption'] ?? 'ภาพความเสียหาย') ?>" 
                      loading="lazy"
                      width="640"
                      height="360"
                      class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                    >
                    <a 
                      href="uploads/before/<?= htmlspecialchars($img['file_name']) ?>" 
                      target="_blank" 
                      class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold gap-1.5 focus:opacity-100"
                      aria-label="คลิกดูภาพก่อนซ่อมขนาดเต็มของใบแจ้งซ่อม <?= htmlspecialchars($ticket['ticket_no']) ?>"
                    >
                      <i class="fa-solid fa-up-right-and-down-left-from-center" aria-hidden="true"></i>
                      <span>คลิกดูภาพเต็ม</span>
                    </a>
                  </div>
                  <p class="text-[11px] text-slate-400">
                    <?= htmlspecialchars($img['caption'] ?? 'ภาพความเสียหาย') ?>
                    อัพโหลดโดย <?= htmlspecialchars($img['uploader_name']) ?>
                  </p>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="rounded-2xl p-8 text-center bg-slate-50 flex flex-col items-center justify-center aspect-video text-slate-400">
                  <i class="fa-solid fa-image text-3xl mb-2 opacity-40" aria-hidden="true"></i>
                  <span class="text-xs font-bold">ไม่มีการแนบภาพถ่ายก่อนซ่อม</span>
                </div>
              <?php endif; ?>
            </div>

            <!-- After Image -->
            <div class="space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                  <i class="fa-solid fa-circle-check text-emerald-600" aria-hidden="true"></i>
                  <span>ภาพหลังซ่อมเสร็จ</span>
                </span>
                <span class="px-2.5 py-1 rounded-xl bg-violet-50 text-violet-700 text-[10px] font-bold">
                  แนบโดยช่าง
                </span>
              </div>

              <?php if (!empty($afterImages)): ?>
                <?php foreach ($afterImages as $img): ?>
                  <div class="group relative rounded-2xl overflow-hidden bg-slate-100 aspect-video flex items-center justify-center">
                    <img 
                      src="uploads/after/<?= htmlspecialchars($img['file_name']) ?>" 
                      alt="ภาพหลังซ่อมของใบแจ้งซ่อม <?= htmlspecialchars($ticket['ticket_no']) ?>: <?= htmlspecialchars($img['caption'] ?? 'ภาพหลังซ่อม') ?>" 
                      loading="lazy"
                      width="640"
                      height="360"
                      class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                    >
                    <a 
                      href="uploads/after/<?= htmlspecialchars($img['file_name']) ?>" 
                      target="_blank" 
                      class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-bold gap-1.5 focus:opacity-100"
                      aria-label="คลิกดูภาพหลังซ่อมขนาดเต็มของใบแจ้งซ่อม <?= htmlspecialchars($ticket['ticket_no']) ?>"
                    >
                      <i class="fa-solid fa-up-right-and-down-left-from-center" aria-hidden="true"></i>
                      <span>คลิกดูภาพเต็ม</span>
                    </a>
                  </div>
                  <p class="text-[11px] text-slate-400">
                    <?= htmlspecialchars($img['caption'] ?? 'ภาพหลังซ่อม') ?>
                    อัพโหลดโดย <?= htmlspecialchars($img['uploader_name']) ?>
                  </p>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="rounded-2xl p-8 text-center bg-slate-50 flex flex-col items-center justify-center aspect-video text-slate-400">
                  <i class="fa-solid fa-screwdriver-wrench text-3xl mb-2 opacity-40" aria-hidden="true"></i>
                  <span class="text-xs font-bold">ยังไม่มีภาพหลังการซ่อม</span>
                  <span class="text-[10px] text-slate-400 mt-0.5">ช่างจะอัพโหลดเมื่อดำเนินการเสร็จสิ้น</span>
                </div>
              <?php endif; ?>
            </div>

          </div>
        </div>
      </div>

      <!-- 3. Technician Solution Notes & Costs -->
      <?php if (!empty($ticket['technician_note']) || (float)$ticket['repair_cost'] > 0): ?>
        <div class="bg-white rounded-3xl overflow-hidden">
          <div class="px-6 py-4 bg-slate-50/60 flex items-center gap-2 text-slate-800 font-black text-sm">
            <i class="fa-solid fa-wrench text-violet-600" aria-hidden="true"></i>
            <span>บันทึกผลการซ่อมและค่าใช้จ่าย</span>
          </div>
          <div class="p-6 space-y-4">
            <?php if (!empty($ticket['technician_note'])): ?>
              <div>
                <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">ผลการตรวจซ่อม วิธีการแก้ไข:</span>
                <div class="p-5 rounded-2xl bg-slate-50 text-slate-800 text-sm whitespace-pre-wrap leading-relaxed">
                  <?= htmlspecialchars($ticket['technician_note']) ?>
                </div>
              </div>
            <?php endif; ?>

            <?php if ((float)$ticket['repair_cost'] > 0 || !empty($ticket['cost_description'])): ?>
              <div class="p-5 rounded-2xl bg-violet-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                  <span class="font-bold text-violet-900 text-sm block">
                    <i class="fa-solid fa-coins text-violet-600 mr-1.5" aria-hidden="true"></i>ค่าใช้จ่ายในการซ่อมรวม:
                  </span>
                  <p class="text-xs text-violet-700 mt-0.5">
                    <?= htmlspecialchars($ticket['cost_description'] ?? 'ค่าอะไหล่และอุปกรณ์') ?>
                  </p>
                </div>
                <div class="text-2xl font-black text-violet-700 font-display tabular-nums">
                  <?= number_format((float)$ticket['repair_cost'], 2) ?> <span class="text-sm font-normal">บาท</span>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Spare Parts Used Card (System C) -->
      <div class="bg-white rounded-3xl overflow-hidden">
        <div class="px-6 py-4 bg-slate-50/60 flex items-center justify-between">
          <div class="flex items-center gap-2 text-slate-800 font-black text-sm">
            <i class="fa-solid fa-boxes-stacked text-violet-600" aria-hidden="true"></i>
            <span>รายการอะไหล่ที่เบิกใช้ในการซ่อม</span>
          </div>
          <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-violet-100 text-violet-700 tabular-nums">
            <?= count($ticketParts) ?> รายการ
          </span>
        </div>

        <div class="p-6">
          <?php if (!empty($ticketParts)): ?>
            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs text-slate-700">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                  <tr>
                    <th class="px-4 py-3">รหัสอะไหล่</th>
                    <th class="px-4 py-3">ชื่อรายการ</th>
                    <th class="px-4 py-3 text-center">จำนวน</th>
                    <th class="px-4 py-3 text-right">ราคาต่อหน่วย</th>
                    <th class="px-4 py-3 text-right">รวมเงิน</th>
                    <?php if ($canUpdateTech && $ticket['status'] !== 'completed'): ?>
                      <th class="px-4 py-3 text-center">จัดการ</th>
                    <?php endif; ?>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <?php 
                    $totalPartsCost = 0;
                    foreach ($ticketParts as $tp): 
                      $sub = (float)$tp['subtotal'];
                      $totalPartsCost += $sub;
                  ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                      <td class="px-4 py-3 font-mono font-bold text-slate-900"><?= htmlspecialchars($tp['part_code']) ?></td>
                      <td class="px-4 py-3 font-bold text-slate-800">
                        <?= htmlspecialchars($tp['part_name']) ?>
                        <?php if (!empty($tp['category'])): ?>
                          <span class="text-[10px] text-slate-400 font-normal block"><?= htmlspecialchars($tp['category']) ?></span>
                        <?php endif; ?>
                      </td>
                      <td class="px-4 py-3 text-center font-bold text-slate-900 tabular-nums">
                        <?= number_format((int)$tp['quantity_used']) ?> <?= htmlspecialchars($tp['unit']) ?>
                      </td>
                      <td class="px-4 py-3 text-right tabular-nums text-slate-600 font-mono">
                        <?= number_format((float)$tp['unit_price_at_time'], 2) ?>
                      </td>
                      <td class="px-4 py-3 text-right tabular-nums font-bold text-violet-700 font-mono">
                        <?= number_format($sub, 2) ?> บาท
                      </td>
                      <?php if ($canUpdateTech && $ticket['status'] !== 'completed'): ?>
                        <td class="px-4 py-3 text-center">
                          <form action="index.php?page=tickets-remove-part" method="POST" onsubmit="return confirm('ยืนยันการลบรายการอะไหล่นี้และคืนสต็อกกลับเข้าคลัง?');" class="inline">
                            <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">
                            <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                            <input type="hidden" name="ticket_part_id" value="<?= $tp['id'] ?>">
                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="ลบรายการและคืนสต็อก">
                              <i class="fa-solid fa-trash-can text-xs"></i>
                            </button>
                          </form>
                        </td>
                      <?php endif; ?>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-slate-50 font-bold text-slate-900">
                  <tr>
                    <td colspan="4" class="px-4 py-3 text-right">รวมค่าอะไหล่ทั้งหมด</td>
                    <td class="px-4 py-3 text-right font-mono text-violet-700 text-sm"><?= number_format($totalPartsCost, 2) ?> บาท</td>
                    <?php if ($canUpdateTech && $ticket['status'] !== 'completed'): ?><td></td><?php endif; ?>
                  </tr>
                </tfoot>
              </table>
            </div>
          <?php else: ?>
            <div class="py-6 text-center text-xs text-slate-400">
              <i class="fa-solid fa-boxes-stacked text-2xl mb-2 opacity-30 block"></i>
              <span>ยังไม่มีการเบิกใช้อะไหล่จากคลังในใบแจ้งซ่อมนี้</span>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- 4. Comments & Interactive Thread Card -->
      <div class="bg-white rounded-3xl overflow-hidden" id="comments-section">
        <div class="px-6 py-4 bg-slate-50/60 flex items-center justify-between">
          <div class="flex items-center gap-2 text-slate-800 font-black text-sm">
            <i class="fa-solid fa-comments text-violet-600" aria-hidden="true"></i>
            <span>ข้อความสอบถามและความคิดเห็น</span>
          </div>
          <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-violet-100 text-violet-700 tabular-nums"><?= count($comments) ?></span>
        </div>

        <div class="p-6 space-y-6">
          <!-- Comments List -->
          <?php if (empty($comments)): ?>
            <p class="text-xs text-slate-400 text-center py-4 font-bold">ยังไม่มีข้อความสอบถามหรือความคิดเห็นในใบแจ้งซ่อมนี้</p>
          <?php else: ?>
            <div class="space-y-3">
              <?php foreach ($comments as $c): ?>
                <?php
                  $badgeClass = match($c['author_role']) {
                    'admin' => 'bg-violet-100 text-violet-700',
                    'technician' => 'bg-slate-200 text-slate-800',
                    default => 'bg-slate-100 text-slate-700'
                  };
                  $badgeLabel = match($c['author_role']) {
                    'admin' => 'ผู้ดูแลระบบ',
                    'technician' => 'ช่างเทคนิค',
                    default => 'ผู้แจ้งซ่อม'
                  };
                ?>
                <div class="p-5 rounded-2xl bg-slate-50 text-xs space-y-2">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                      <strong class="text-slate-900 font-black"><?= htmlspecialchars($c['author_name']) ?></strong>
                      <span class="px-2.5 py-0.5 rounded-xl text-[10px] font-bold <?= $badgeClass ?>">
                        <?= $badgeLabel ?>
                      </span>
                    </div>
                    <span class="text-[11px] text-slate-400"><?= date('d/m/Y H:i น.', strtotime($c['created_at'])) ?></span>
                  </div>
                  <div class="text-slate-800 text-xs whitespace-pre-wrap leading-relaxed"><?= htmlspecialchars($c['comment']) ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <!-- New Comment Input Form -->
          <form action="index.php?page=comments-store" method="POST" class="space-y-3 pt-2">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">
            <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
            <div>
              <label for="comment" class="block text-xs font-bold text-slate-700 mb-1.5">
                <i class="fa-solid fa-reply text-slate-400 mr-1" aria-hidden="true"></i>ส่งข้อความสอบถามหรือระบุข้อมูลเพิ่มเติม
              </label>
              <textarea 
                name="comment" 
                id="comment" 
                rows="3" 
                class="w-full px-4 py-3 rounded-2xl bg-slate-100 focus:bg-white text-slate-900 text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all" 
                placeholder="พิมพ์ข้อความสอบถามอาการ ความคืบหน้า หรือแจ้งข้อมูลเพิ่มเติมที่นี่" 
                required
              ></textarea>
            </div>
            <div class="text-right">
              <button 
                type="submit" 
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white font-bold text-xs transition-all active:scale-95"
              >
                <i class="fa-solid fa-paper-plane text-[10px]" aria-hidden="true"></i>
                <span>ส่งความคิดเห็น</span>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- 5. Modern Flat Status Timeline Card -->
      <div class="bg-white rounded-3xl overflow-hidden">
        <div class="px-6 py-4 bg-slate-50/60 flex items-center justify-between">
          <div class="flex items-center gap-2 text-slate-900 font-black text-sm">
            <i class="fa-solid fa-timeline text-violet-600" aria-hidden="true"></i>
            <span>ไทม์ไลน์ประวัติการดำเนินงาน</span>
          </div>
          <span class="text-xs font-bold text-slate-400 bg-slate-100 px-2.5 py-1 rounded-xl">
            <?= count($logs) ?> บันทึก
          </span>
        </div>
        <div class="p-6">
          <?php if (!empty($logs)): ?>
            <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-3 before:bottom-3 before:w-0.5 before:bg-slate-200">
              <?php foreach ($logs as $idx => $log): ?>
                <?php
                  $dotColor = match ($log['new_status']) {
                    'completed' => 'bg-emerald-500 ring-4 ring-emerald-50',
                    'in_process' => 'bg-violet-600 ring-4 ring-violet-50',
                    'pending' => 'bg-amber-500 ring-4 ring-amber-50',
                    default => 'bg-slate-400 ring-4 ring-slate-100',
                  };
                  $badgeColor = match ($log['new_status']) {
                    'completed' => 'bg-emerald-100 text-emerald-800',
                    'in_process' => 'bg-violet-100 text-violet-800',
                    'pending' => 'bg-amber-100 text-amber-900',
                    default => 'bg-slate-100 text-slate-700',
                  };
                ?>
                <div class="relative flex items-start gap-4 text-xs">
                  <!-- Timeline Node Dot -->
                  <div class="absolute -left-6 top-1 w-3 h-3 rounded-full <?= $dotColor ?> flex-shrink-0"></div>
                  
                  <div class="flex-1 space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                      <span class="px-2 py-0.5 rounded-lg <?= $badgeColor ?> font-bold text-[10px]">
                        <?= RepairTicket::getStatusLabel($log['new_status']) ?>
                      </span>
                      <strong class="text-slate-900 font-bold text-xs"><?= htmlspecialchars($log['notes'] ?? '-') ?></strong>
                    </div>
                    <div class="text-[11px] text-slate-400 flex items-center gap-2">
                      <span>ดำเนินการโดย: <strong class="text-slate-600 font-medium"><?= htmlspecialchars($log['changed_by_name'] ?? 'ระบบ') ?></strong></span>
                      <span>·</span>
                      <time class="tabular-nums"><?= date('d/m/Y H:i น.', strtotime($log['created_at'])) ?></time>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="text-center text-xs text-slate-400 py-4">ยังไม่มีประวัติการดำเนินงาน</p>
          <?php endif; ?>
        </div>
      </div>

    </div>

    <!-- Right Column: Control Panels (4 cols) -->
    <div class="lg:col-span-4 space-y-6">

      <!-- Admin Action: Assign Technician & Due Date -->
      <?php if ($canAssign): ?>
        <div class="bg-white rounded-3xl overflow-hidden">
          <div class="px-6 py-4 bg-slate-50/60 flex items-center gap-2 font-black text-sm text-slate-900">
            <i class="fa-solid fa-user-gear text-violet-600" aria-hidden="true"></i>
            <span>การมอบหมายงาน</span>
          </div>
          <div class="p-6">
            <form action="index.php?page=tickets-assign" method="POST" class="space-y-4">
              <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">
              <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">

              <div>
                <label for="assign_technician_id" class="block text-xs font-bold text-slate-700 mb-1.5">
                  มอบหมายให้ช่างเทคนิค <span class="text-rose-500">*</span>
                </label>
                <select id="assign_technician_id" name="technician_id" class="w-full px-4 py-2.5 text-xs rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all" required>
                  <option value="" disabled <?= empty($ticket['technician_id']) ? 'selected' : '' ?>>เลือกช่างผู้รับผิดชอบ</option>
                  <?php foreach ($technicians as $tech): ?>
                    <option value="<?= $tech->id ?>" <?= ((int)$ticket['technician_id'] === (int)$tech->id) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($tech->full_name) ?> โทร <?= htmlspecialchars($tech->phone ?? '-') ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label for="assign_due_date" class="block text-xs font-bold text-slate-700 mb-1.5">กำหนดวันแล้วเสร็จ</label>
                <input id="assign_due_date" type="date" name="due_date" class="w-full px-4 py-2.5 text-xs rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all font-mono" value="<?= htmlspecialchars($ticket['due_date'] ?? '') ?>">
              </div>

              <div>
                <label for="assign_priority" class="block text-xs font-bold text-slate-700 mb-1.5">ปรับระดับความเร่งด่วน</label>
                <select id="assign_priority" name="priority" class="w-full px-4 py-2.5 text-xs rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all">
                  <option value="normal" <?= ($ticket['priority'] === 'normal') ? 'selected' : '' ?>>ปกติ</option>
                  <option value="high" <?= ($ticket['priority'] === 'high') ? 'selected' : '' ?>>ด่วน</option>
                  <option value="urgent" <?= ($ticket['priority'] === 'urgent') ? 'selected' : '' ?>>ด่วนที่สุด</option>
                  <option value="low" <?= ($ticket['priority'] === 'low') ? 'selected' : '' ?>>ไม่เร่งด่วน</option>
                </select>
              </div>

              <button 
                type="submit" 
                class="w-full py-3 px-4 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white font-bold text-xs transition-all flex items-center justify-center gap-1.5 active:scale-95"
              >
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                <span>บันทึกการมอบหมายงาน</span>
              </button>
            </form>
          </div>
        </div>
      <?php endif; ?>

      <!-- Technician Action: Update Progress, Notes, Costs, After Photo -->
      <?php if ($canUpdateTech): ?>
        <div class="bg-white rounded-3xl overflow-hidden">
          <div class="px-6 py-4 bg-slate-50/60 flex items-center gap-2 font-black text-sm text-slate-900">
            <i class="fa-solid fa-wrench text-violet-600" aria-hidden="true"></i>
            <span>บันทึกผลการซ่อม</span>
          </div>
          <div class="p-6">
            <form action="index.php?page=tickets-update-tech" method="POST" enctype="multipart/form-data" class="space-y-4">
              <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">
              <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">

              <div>
                <label for="tech_status" class="block text-xs font-bold text-slate-700 mb-1.5">ปรับสถานะงานซ่อม</label>
                <select id="tech_status" name="status" class="w-full px-4 py-2.5 text-xs font-bold rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all">
                  <option value="in_process" <?= ($ticket['status'] === 'in_process') ? 'selected' : '' ?>>กำลังซ่อม</option>
                  <option value="completed" <?= ($ticket['status'] === 'completed') ? 'selected' : '' ?>>ซ่อมเสร็จสิ้นแล้ว</option>
                </select>
              </div>

              <div>
                <label for="tech_technician_note" class="block text-xs font-bold text-slate-700 mb-1.5">บันทึกผลการซ่อม วิธีการแก้ไข</label>
                <textarea 
                  id="tech_technician_note"
                  name="technician_note" 
                  rows="3" 
                  class="w-full px-4 py-3 text-xs rounded-2xl bg-slate-100 focus:bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all" 
                  placeholder="เช่น เปลี่ยนสายแพร์จอ ทำความสะอาดแอร์ ลงโปรแกรมใหม่"
                ><?= htmlspecialchars($ticket['technician_note'] ?? '') ?></textarea>
              </div>

              <div>
                <label for="tech_repair_cost" class="block text-xs font-bold text-slate-700 mb-1.5">ค่าใช้จ่ายในการซ่อม บาท</label>
                <input 
                  id="tech_repair_cost"
                  type="number" 
                  step="0.01" 
                  min="0" 
                  name="repair_cost" 
                  class="w-full px-4 py-2.5 text-xs rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all font-mono" 
                  value="<?= htmlspecialchars((string)($ticket['repair_cost'] ?? 0)) ?>"
                >
              </div>

              <div>
                <label for="tech_cost_description" class="block text-xs font-bold text-slate-700 mb-1.5">รายละเอียดค่าใช้จ่าย รายการอะไหล่</label>
                <input 
                  id="tech_cost_description"
                  type="text" 
                  name="cost_description" 
                  class="w-full px-4 py-2.5 text-xs rounded-2xl bg-slate-100 focus:bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all" 
                  placeholder="เช่น ค่าหลอดไฟ 2 หลอด ค่าน้ำยาแอร์" 
                  value="<?= htmlspecialchars($ticket['cost_description'] ?? '') ?>"
                >
              </div>

              <!-- Upload After Image -->
              <div class="p-4 bg-slate-100 rounded-2xl">
                <label for="after_image_input" class="block text-xs font-bold text-slate-800 mb-1 cursor-pointer">
                  <i class="fa-solid fa-camera text-violet-600 mr-1" aria-hidden="true"></i>แนบภาพหลังซ่อมเสร็จสิ้น
                </label>
                <input 
                  type="file" 
                  id="after_image_input" 
                  name="after_image" 
                  class="block w-full text-[11px] text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-[11px] file:font-bold file:bg-violet-100 file:text-violet-700 hover:file:bg-violet-200 transition-colors cursor-pointer focus:outline-none" 
                  accept="image/jpeg,image/png,image/webp"
                >
                <div id="after_image_preview" class="mt-2"></div>
              </div>

              <button 
                type="submit" 
                class="w-full py-3 px-4 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white font-bold text-xs transition-all flex items-center justify-center gap-1.5 active:scale-95"
              >
                <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                <span>บันทึกผลและอัพเดทงาน</span>
              </button>
            </form>
          </div>
        </div>
      <?php endif; ?>

      <!-- Spare Parts Requisition Panel (System C) -->
      <?php if ($canUpdateTech && $ticket['status'] !== 'completed'): ?>
        <div class="bg-white rounded-3xl overflow-hidden">
          <div class="px-6 py-4 bg-slate-50/60 flex items-center justify-between">
            <div class="flex items-center gap-2 font-black text-sm text-slate-900">
              <i class="fa-solid fa-boxes-stacked text-violet-600" aria-hidden="true"></i>
              <span>เบิกอะไหล่จากคลัง</span>
            </div>
            <span class="text-[10px] px-2.5 py-0.5 rounded-full bg-violet-100 text-violet-700 font-bold">
              ตัดสต็อกอัตโนมัติ
            </span>
          </div>
          <div class="p-6">
            <form action="index.php?page=tickets-add-part" method="POST" class="space-y-4">
              <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">
              <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">

              <div>
                <label for="spare_part_id" class="block text-xs font-bold text-slate-700 mb-1.5">
                  เลือกอะไหล่ในคลัง <span class="text-rose-500">*</span>
                </label>
                <select id="spare_part_id" name="spare_part_id" class="w-full px-4 py-2.5 text-xs rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all" required>
                  <option value="" disabled selected>เลือกรายการอะไหล่</option>
                  <?php foreach ($spareParts as $sp): ?>
                    <?php if ((int)$sp['quantity_in_stock'] > 0): ?>
                      <option value="<?= $sp['id'] ?>">
                        <?= htmlspecialchars($sp['part_code']) ?> - <?= htmlspecialchars($sp['name']) ?> คงเหลือ <?= number_format((int)$sp['quantity_in_stock']) ?> <?= htmlspecialchars($sp['unit']) ?> ราคา <?= number_format((float)$sp['unit_price'], 2) ?> บาท
                      </option>
                    <?php else: ?>
                      <option value="<?= $sp['id'] ?>" disabled class="text-slate-400">
                        <?= htmlspecialchars($sp['part_code']) ?> - <?= htmlspecialchars($sp['name']) ?> หมดสต็อก
                      </option>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </select>
              </div>

              <div>
                <label for="part_quantity" class="block text-xs font-bold text-slate-700 mb-1.5">
                  จำนวนที่เบิกใช้ <span class="text-rose-500">*</span>
                </label>
                <input 
                  id="part_quantity" 
                  type="number" 
                  name="quantity" 
                  min="1" 
                  value="1" 
                  class="w-full px-4 py-2.5 text-xs rounded-2xl bg-slate-100 focus:bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-violet-500/20 transition-all font-mono" 
                  required
                >
              </div>

              <button 
                type="submit" 
                class="w-full py-3 px-4 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white font-bold text-xs transition-all flex items-center justify-center gap-1.5 active:scale-95"
              >
                <i class="fa-solid fa-plus text-[10px]" aria-hidden="true"></i>
                <span>เบิกอะไหล่และตัดสต็อก</span>
              </button>
            </form>
          </div>
        </div>
      <?php endif; ?>

      <!-- Stakeholders Contact Card -->
      <div class="bg-white rounded-3xl overflow-hidden">
        <div class="px-6 py-4 bg-slate-50/60 flex items-center gap-2 font-black text-sm text-slate-900">
          <i class="fa-solid fa-users text-slate-400" aria-hidden="true"></i>
          <span>ผู้เกี่ยวข้องในงานนี้</span>
        </div>
        <div class="p-6 space-y-4">
          <!-- Member Info -->
          <div class="space-y-1">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">ผู้แจ้งซ่อม</span>
            <strong class="text-slate-900 font-bold text-xs block"><?= htmlspecialchars($ticket['member_name']) ?></strong>
            <span class="text-slate-500 text-[11px] block"><?= htmlspecialchars($ticket['member_department'] ?? '-') ?></span>
            <?php if (!empty($ticket['member_phone'])): ?>
              <div class="text-[11px] text-violet-600 flex items-center gap-1 mt-0.5 font-bold">
                <i class="fa-solid fa-phone text-[9px]" aria-hidden="true"></i>
                <span><?= htmlspecialchars($ticket['member_phone']) ?></span>
              </div>
            <?php endif; ?>
          </div>

          <div class="pt-3 space-y-1">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">ช่างผู้รับผิดชอบงาน</span>
            <?php if (!empty($ticket['technician_name'])): ?>
              <strong class="text-violet-700 font-bold text-xs block"><?= htmlspecialchars($ticket['technician_name']) ?></strong>
              <span class="text-slate-500 text-[11px] block"><?= htmlspecialchars($ticket['technician_department'] ?? 'งานซ่อมบำรุง') ?></span>
              <?php if (!empty($ticket['technician_phone'])): ?>
                <div class="text-[11px] text-emerald-600 flex items-center gap-1 mt-0.5 font-bold">
                  <i class="fa-solid fa-phone text-[9px]" aria-hidden="true"></i>
                  <span><?= htmlspecialchars($ticket['technician_phone']) ?></span>
                </div>
              <?php endif; ?>
            <?php else: ?>
              <span class="text-slate-400 text-xs">ยังไม่ได้มอบหมายช่าง</span>
            <?php endif; ?>
          </div>

          <div class="pt-3 space-y-1">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">กำหนดวันแล้วเสร็จ</span>
            <?php if (!empty($ticket['due_date'])): ?>
              <div class="font-bold text-xs <?= $isOverdue ? 'text-rose-600' : 'text-slate-800' ?> flex items-center gap-1.5">
                <i class="fa-solid fa-calendar-days text-[10px]" aria-hidden="true"></i>
                <span><?= date('d/m/Y', strtotime($ticket['due_date'])) ?></span>
              </div>
              <?php if ($isOverdue): ?>
                <span class="text-[10px] text-rose-500 font-bold block">เลยกำหนดแล้วเสร็จ</span>
              <?php endif; ?>
            <?php else: ?>
              <span class="text-slate-400 text-xs">ยังไม่ได้ระบุ</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>

  </div>

</div>

<?php
require_once __DIR__ . '/../layout/footer.php';
?>
