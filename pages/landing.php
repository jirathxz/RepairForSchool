<?php
require_once __DIR__ . '/layout/header.php';
?>

<!-- Hero Section -->
<section class="relative overflow-hidden bg-slate-900 text-white min-h-[520px] sm:min-h-[calc(100dvh-4rem)] flex items-center">

  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24 z-10 w-full">
    <div class="max-w-3xl">
      
      <!-- Institutional Slogan Badge -->
      <div class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-slate-800 mb-6">
        <i class="fa-solid fa-school text-violet-400 text-xs" aria-hidden="true"></i>
        <span class="text-xs font-bold text-slate-200 tracking-wide">ระบบงานซ่อมบำรุงโรงเรียนยุคดิจิทัล</span>
      </div>

      <!-- Hero Headline -->
      <h1 class="font-display font-black text-3xl sm:text-5xl lg:text-6xl tracking-tight leading-[1.18] mb-6 text-white">
        ซ่อมไว แจ้งง่าย <br class="hidden sm:inline">
        <span class="text-violet-400">
          โปร่งใสทุกขั้นตอน
        </span>
        <span class="block sm:inline sm:ml-2">เพื่อสถานศึกษา</span>
      </h1>

      <!-- Subtitle -->
      <p class="text-slate-300 text-base sm:text-lg leading-relaxed mb-8 max-w-2xl font-normal">
        แพลตฟอร์มบริหารจัดการงานแจ้งซ่อมบำรุง อาคารสถานที่ โสตทัศนูปกรณ์ และเทคโนโลยีสารสนเทศสำหรับสถานศึกษา เชื่อมโยงครู ช่างเทคนิค และผู้บริหารโรงเรียนในระบบเดียว
      </p>

      <!-- CTA Action Buttons -->
      <div class="flex flex-wrap items-center gap-3 sm:gap-4 mb-10">
        <a href="index.php?page=login" class="inline-flex items-center gap-2.5 px-7 py-3.5 min-h-[44px] rounded-2xl bg-violet-600 hover:bg-violet-700 text-white font-bold text-sm transition-all whitespace-nowrap active:scale-95">
          <i class="fa-solid fa-right-to-bracket text-xs" aria-hidden="true"></i>
          <span>เข้าสู่ระบบใช้งาน</span>
        </a>

        <a href="#quick-login" class="inline-flex items-center gap-2 px-6 py-3.5 min-h-[44px] rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white font-bold text-sm transition-all whitespace-nowrap">
          <i class="fa-solid fa-bolt text-amber-400 text-xs" aria-hidden="true"></i>
          <span>ทดสอบระบบด่วน</span>
        </a>

        <a href="#features" class="inline-flex items-center gap-2 px-5 py-3.5 min-h-[44px] text-slate-400 hover:text-white text-sm font-bold transition-colors whitespace-nowrap">
          <span>ดูจุดเด่นระบบ</span>
          <i class="fa-solid fa-arrow-down text-xs" aria-hidden="true"></i>
        </a>
      </div>

      <!-- Trust Badges -->
      <div class="pt-6 flex flex-wrap items-center gap-6 text-xs text-slate-400 font-medium">
        <span class="flex items-center gap-1.5"><i class="fa-solid fa-check text-violet-400" aria-hidden="true"></i> สิทธิ์แยกตามบทบาท RBAC</span>
        <span class="flex items-center gap-1.5"><i class="fa-solid fa-check text-violet-400" aria-hidden="true"></i> ภาพหลักฐานก่อนและหลังซ่อม</span>
        <span class="flex items-center gap-1.5"><i class="fa-solid fa-check text-violet-400" aria-hidden="true"></i> รายงานไฟล์ CSV</span>
        <span class="flex items-center gap-1.5"><i class="fa-solid fa-check text-violet-400" aria-hidden="true"></i> พิมพ์ใบส่งงาน A4</span>
      </div>

    </div>
  </div>
</section>

<!-- Live Statistics Bar -->
<section id="metrics" class="bg-white py-12 relative z-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 lg:gap-8 text-center">
      
      <!-- Metric 1: Total Real Tickets -->
      <div class="p-6 rounded-3xl bg-slate-50">
        <div class="font-display font-black text-3xl sm:text-4xl text-slate-900 tracking-tight flex items-center justify-center gap-1">
          <span class="reactbits-countup tabular-nums" data-to="<?= (int)($kpi['total'] ?? 0) ?>" data-duration="1.5"><?= (int)($kpi['total'] ?? 0) ?></span>
          <span class="text-violet-600 text-xl font-bold">งาน</span>
        </div>
        <p class="text-xs text-slate-500 font-bold mt-1">รายการแจ้งซ่อมทั้งหมด</p>
      </div>

      <!-- Metric 2: Completed Rate -->
      <div class="p-6 rounded-3xl bg-slate-50">
        <?php
          $completionRate = !empty($kpi['total']) && $kpi['total'] > 0 
            ? round(($kpi['completed'] / $kpi['total']) * 100, 0) 
            : 100;
        ?>
        <div class="font-display font-black text-3xl sm:text-4xl text-slate-900 tracking-tight flex items-center justify-center gap-1">
          <span class="reactbits-countup tabular-nums" data-to="<?= $completionRate ?>" data-duration="1.5"><?= $completionRate ?></span>
          <span class="text-emerald-600 text-xl font-bold">%</span>
        </div>
        <p class="text-xs text-slate-500 font-bold mt-1">อัตราปิดงานซ่อมสำเร็จ</p>
      </div>

      <!-- Metric 3: Active In-Process -->
      <div class="p-6 rounded-3xl bg-slate-50">
        <div class="font-display font-black text-3xl sm:text-4xl text-slate-900 tracking-tight flex items-center justify-center gap-1">
          <span class="reactbits-countup tabular-nums" data-to="<?= (int)($kpi['in_process'] ?? 0) ?>" data-duration="1.5"><?= (int)($kpi['in_process'] ?? 0) ?></span>
          <span class="text-violet-600 text-xl font-bold">งาน</span>
        </div>
        <p class="text-xs text-slate-500 font-bold mt-1">กำลังดำเนินการตรวจซ่อม</p>
      </div>

      <!-- Metric 4: Supported Categories -->
      <div class="p-6 rounded-3xl bg-slate-50">
        <div class="font-display font-black text-3xl sm:text-4xl text-slate-900 tracking-tight flex items-center justify-center gap-1">
          <span class="reactbits-countup tabular-nums" data-to="<?= count($equipmentTypes ?? []) ?: 8 ?>" data-duration="1.2"><?= count($equipmentTypes ?? []) ?: 8 ?></span>
          <span class="text-violet-600 text-xl font-bold">หมวด</span>
        </div>
        <p class="text-xs text-slate-500 font-bold mt-1">ครอบคลุมทุกอุปกรณ์</p>
      </div>

    </div>
  </div>
</section>

<!-- Role Cards Section -->
<section id="features" class="py-20 bg-slate-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="text-center max-w-2xl mx-auto mb-14">
      <span class="text-xs font-bold text-violet-600 uppercase tracking-wider">ออกแบบเพื่อทุกคนในโรงเรียน</span>
      <h2 class="font-display font-black text-2xl sm:text-4xl text-slate-900 mt-2 mb-3">
        ตอบโจทย์ 3 บทบาทการทำงานอย่างลงตัว
      </h2>
      <p class="text-slate-600 text-sm sm:text-base">
        เชื่อมต่อการสื่อสารระหว่างผู้แจ้งซ่อม เจ้าหน้าที่ผู้ลงมือแก้ไข และผู้บริหารอย่างเป็นระบบ
      </p>
    </div>

    <!-- Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
      
      <!-- Card 1: Teacher -->
      <div class="rounded-3xl bg-white p-8 flex flex-col justify-between">
        <div>
          <div class="w-12 h-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-xl mb-4">
            <i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i>
          </div>
          <span class="inline-block px-3 py-1 rounded-xl text-[11px] font-bold bg-violet-100 text-violet-700 mb-2">บทบาทครู และบุคลากร</span>
          <h3 class="font-display font-black text-lg text-slate-900 mb-2.5">แจ้งปัญหาง่าย ติดตามได้ทุกวินาที</h3>
          <p class="text-slate-600 text-sm leading-relaxed mb-4">
            ส่งคำขอแจ้งซ่อมภายใน 1 นาที ระบุอาคารและห้อง แนบภาพถ่ายความเสียหายก่อนซ่อม และสอบถามความคืบหน้ากับช่างได้โดยตรง
          </p>
        </div>
        <ul class="space-y-2 text-xs text-slate-600 pt-4">
          <li class="flex items-center gap-2"><i class="fa-solid fa-check text-violet-600" aria-hidden="true"></i><span>แบบฟอร์มเลือกอาคารและห้องเรียนสะดวก</span></li>
          <li class="flex items-center gap-2"><i class="fa-solid fa-check text-violet-600" aria-hidden="true"></i><span>พรีวิวภาพก่อนอัพโหลดทันที</span></li>
          <li class="flex items-center gap-2"><i class="fa-solid fa-check text-violet-600" aria-hidden="true"></i><span>ติดตามสถานะแบบเรียลไทม์</span></li>
        </ul>
      </div>

      <!-- Card 2: Technician -->
      <div class="rounded-3xl bg-white p-8 flex flex-col justify-between">
        <div>
          <div class="w-12 h-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-xl mb-4">
            <i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>
          </div>
          <span class="inline-block px-3 py-1 rounded-xl text-[11px] font-bold bg-violet-100 text-violet-700 mb-2">บทบาทเจ้าหน้าที่ช่าง</span>
          <h3 class="font-display font-black text-lg text-slate-900 mb-2.5">จัดการงานในมือ อัพเดทผลชัดเจน</h3>
          <p class="text-slate-600 text-sm leading-relaxed mb-4">
            ดูรายการงานซ่อมที่ได้รับมอบหมาย บันทึกผลการตรวจเช็ค บันทึกค่าใช้จ่ายและรายการอะไหล่ พร้อมแนบภาพถ่ายหลังซ่อมเสร็จสิ้น
          </p>
        </div>
        <ul class="space-y-2 text-xs text-slate-600 pt-4">
          <li class="flex items-center gap-2"><i class="fa-solid fa-check text-violet-600" aria-hidden="true"></i><span>แผงควบคุมงานที่ต้องดำเนินการและกำหนดวันเสร็จ</span></li>
          <li class="flex items-center gap-2"><i class="fa-solid fa-check text-violet-600" aria-hidden="true"></i><span>บันทึกค่าใช้จ่ายอะไหล่และอุปกรณ์</span></li>
          <li class="flex items-center gap-2"><i class="fa-solid fa-check text-violet-600" aria-hidden="true"></i><span>แนบภาพหลังซ่อมและเปลี่ยนสถานะในคลิกเดียว</span></li>
        </ul>
      </div>

      <!-- Card 3: Admin -->
      <div class="rounded-3xl bg-white p-8 flex flex-col justify-between">
        <div>
          <div class="w-12 h-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-xl mb-4">
            <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
          </div>
          <span class="inline-block px-3 py-1 rounded-xl text-[11px] font-bold bg-violet-100 text-violet-700 mb-2">บทบาทผู้ดูแล และผู้บริหาร</span>
          <h3 class="font-display font-black text-lg text-slate-900 mb-2.5">วัดผลแม่นยำ มอบหมายงานฉับไว</h3>
          <p class="text-slate-600 text-sm leading-relaxed mb-4">
            แดชบอร์ด KPI ติดตามดัชนีสุขภาพระบบงานซ่อม กระจายงานตามภาระงานช่าง ตรวจสอบงานเลยกำหนด และส่งออกรายงานสรุป CSV ทันที
          </p>
        </div>
        <ul class="space-y-2 text-xs text-slate-600 pt-4">
          <li class="flex items-center gap-2"><i class="fa-solid fa-check text-violet-600" aria-hidden="true"></i><span>ดัชนีประเมินสุขภาพระบบงานซ่อมโรงเรียน</span></li>
          <li class="flex items-center gap-2"><i class="fa-solid fa-check text-violet-600" aria-hidden="true"></i><span>มอบหมายงานพร้อมระบุวันแล้วเสร็จ</span></li>
          <li class="flex items-center gap-2"><i class="fa-solid fa-check text-violet-600" aria-hidden="true"></i><span>ส่งออกข้อมูลไฟล์ CSV และพิมพ์ใบส่งงาน A4</span></li>
        </ul>
      </div>

    </div>
  </div>
</section>

<!-- 3-Step Workflow Section -->
<section id="workflow" class="py-20 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="text-center max-w-2xl mx-auto mb-16">
      <h2 class="font-display font-black text-2xl sm:text-4xl text-slate-900 mb-3">
        ขั้นตอนการแจ้งซ่อมและส่งมอบงาน 3 ขั้นตอน
      </h2>
      <p class="text-slate-600 text-sm sm:text-base">
        ทุกขั้นตอนมีบันทึกประวัติการดำเนินงาน โปร่งใส ตรวจสอบย้อนหลังได้ 100%
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      
      <!-- Step 1 -->
      <div class="p-8 rounded-3xl bg-slate-50 text-center">
        <div class="w-12 h-12 rounded-2xl bg-violet-600 text-white font-display font-bold text-lg flex items-center justify-center mx-auto mb-4">
          1
        </div>
        <h4 class="font-display font-black text-base text-slate-900 mb-2">ครูแจ้งซ่อม และแนบภาพก่อนซ่อม</h4>
        <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
          ระบุอาคาร ห้องเรียน อาการที่พบ และถ่ายภาพจุดชำรุดแนบในระบบ สถานะเริ่มต้นเป็น <span class="text-violet-700 font-bold">รอดำเนินการ</span>
        </p>
      </div>

      <!-- Step 2 -->
      <div class="p-8 rounded-3xl bg-slate-50 text-center">
        <div class="w-12 h-12 rounded-2xl bg-slate-800 text-white font-display font-bold text-lg flex items-center justify-center mx-auto mb-4">
          2
        </div>
        <h4 class="font-display font-black text-base text-slate-900 mb-2">เจ้าหน้าที่มอบหมายงาน และกำหนดวันเสร็จ</h4>
        <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
          ผู้ดูแลระบบตรวจสอบปัญหา เลือกช่างผู้รับผิดชอบ และกำหนดวันเสร็จ สถานะปรับเป็น <span class="text-violet-700 font-bold">กำลังซ่อม</span>
        </p>
      </div>

      <!-- Step 3 -->
      <div class="p-8 rounded-3xl bg-slate-50 text-center">
        <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white font-display font-bold text-lg flex items-center justify-center mx-auto mb-4">
          3
        </div>
        <h4 class="font-display font-black text-base text-slate-900 mb-2">ช่างแก้ไข และแนบภาพหลังซ่อม</h4>
        <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
          ช่างเข้าซ่อม บันทึกค่าใช้จ่ายและแนบภาพหลังซ่อม สถานะปรับเป็น <span class="text-emerald-700 font-bold">เสร็จสิ้น</span> พร้อมพิมพ์ใบสั่งงาน A4
        </p>
      </div>

    </div>

  </div>
</section>

<!-- Equipment Categories Showcase -->
<section class="py-16 bg-slate-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-xl mx-auto mb-10">
      <h3 class="font-display font-black text-xl sm:text-2xl text-slate-900">หมวดหมู่งานซ่อมบำรุงที่รองรับ</h3>
      <p class="text-slate-500 text-xs sm:text-sm mt-1">ครอบคลุมทุกอุปกรณ์ เครื่องมือ และระบบสาธารณูปโภคภายในโรงเรียน</p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <?php foreach ($equipmentTypes as $eq): ?>
        <div class="p-5 rounded-2xl bg-white flex items-center gap-3">
          <i class="<?= htmlspecialchars($eq->icon_class ?? 'fa-solid fa-wrench') ?> text-violet-600 text-base" aria-hidden="true"></i>
          <span class="text-xs font-bold text-slate-800 leading-snug"><?= htmlspecialchars($eq->name) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Quick Test Login Section -->
<section id="quick-login" class="py-20 bg-slate-900 text-white relative overflow-hidden">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
    
    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-2xl text-xs font-bold bg-violet-900/60 text-violet-300 mb-4">
      <span>เข้าใช้งานและทดสอบระบบได้ทันที</span>
    </span>

    <h2 class="font-display font-black text-2xl sm:text-4xl text-white mb-3">
      เริ่มสัมผัสประสบการณ์ RepairForSchool วันนี้
    </h2>
    <p class="text-slate-400 text-sm sm:text-base max-w-xl mx-auto mb-10">
      คลิกเลือกบทบาทที่ต้องการเพื่อเข้าสู่ระบบทดสอบได้ทันที รหัสผ่านเริ่มต้น password123
    </p>

    <!-- Quick Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-left">
      
      <!-- Admin 1-Click -->
      <form action="index.php?page=login-submit" method="POST" class="p-6 rounded-3xl bg-slate-800 flex flex-col justify-between">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">
        <input type="hidden" name="username" value="admin">
        <input type="hidden" name="password" value="password123">
        <div>
          <div class="flex items-center justify-between mb-3">
            <span class="text-[10px] font-mono px-2.5 py-1 rounded-xl bg-slate-700 text-slate-300 font-bold">admin</span>
          </div>
          <div class="font-black text-sm text-white">ผู้ดูแลระบบ</div>
          <p class="text-xs text-slate-400 mt-1 mb-5">อ.สมเกียรติ ฝ่ายอาคารสถานที่</p>
        </div>
        <button type="submit" class="w-full py-3 px-4 min-h-[44px] rounded-2xl bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold transition-all flex items-center justify-center gap-1.5 whitespace-nowrap active:scale-95">
          <span>เข้าสู่ระบบเป็นผู้ดูแล</span>
        </button>
      </form>

      <!-- Teacher 1-Click -->
      <form action="index.php?page=login-submit" method="POST" class="p-6 rounded-3xl bg-slate-800 flex flex-col justify-between">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">
        <input type="hidden" name="username" value="teacher01">
        <input type="hidden" name="password" value="password123">
        <div>
          <div class="flex items-center justify-between mb-3">
            <span class="text-[10px] font-mono px-2.5 py-1 rounded-xl bg-slate-700 text-slate-300 font-bold">teacher01</span>
          </div>
          <div class="font-black text-sm text-white">ครู และบุคลากร</div>
          <p class="text-xs text-slate-400 mt-1 mb-5">ครูสุดา กลุ่มสาระวิทยาศาสตร์</p>
        </div>
        <button type="submit" class="w-full py-3 px-4 min-h-[44px] rounded-2xl bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold transition-all flex items-center justify-center gap-1.5 whitespace-nowrap active:scale-95">
          <span>เข้าสู่ระบบเป็นครู</span>
        </button>
      </form>

      <!-- Technician 1-Click -->
      <form action="index.php?page=login-submit" method="POST" class="p-6 rounded-3xl bg-slate-800 flex flex-col justify-between">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">
        <input type="hidden" name="username" value="tech01">
        <input type="hidden" name="password" value="password123">
        <div>
          <div class="flex items-center justify-between mb-3">
            <span class="text-[10px] font-mono px-2.5 py-1 rounded-xl bg-slate-700 text-slate-300 font-bold">tech01</span>
          </div>
          <div class="font-black text-sm text-white">ช่างเทคนิค</div>
          <p class="text-xs text-slate-400 mt-1 mb-5">ช่างสมชาย งานซ่อมบำรุง</p>
        </div>
        <button type="submit" class="w-full py-3 px-4 min-h-[44px] rounded-2xl bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold transition-all flex items-center justify-center gap-1.5 whitespace-nowrap active:scale-95">
          <span>เข้าสู่ระบบเป็นช่าง</span>
        </button>
      </form>

    </div>

    <div class="mt-8">
      <a href="index.php?page=login" class="text-xs text-slate-400 hover:text-white underline">
        หรือเข้าสู่ระบบด้วยชื่อผู้ใช้งานอื่น
      </a>
    </div>

  </div>
</section>

<!-- ReactBits Client Interactive Script: CountUp Animation -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const countUpElements = document.querySelectorAll('.reactbits-countup');
  const countObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const el = entry.target;
        const target = parseFloat(el.getAttribute('data-to')) || 0;
        const duration = parseFloat(el.getAttribute('data-duration')) || 1.5;
        const startTime = performance.now();

        const updateCounter = (now) => {
          const elapsed = (now - startTime) / 1000;
          const progress = Math.min(elapsed / duration, 1);
          const easeOut = 1 - Math.pow(1 - progress, 3);
          const current = Math.floor(easeOut * target);
          el.textContent = current.toLocaleString('en-US');

          if (progress < 1) {
            requestAnimationFrame(updateCounter);
          } else {
            el.textContent = target.toLocaleString('en-US');
          }
        };
        requestAnimationFrame(updateCounter);
        countObserver.unobserve(el);
      }
    });
  }, { threshold: 0.3 });

  countUpElements.forEach((el) => countObserver.observe(el));
});
</script>

<?php
require_once __DIR__ . '/layout/footer.php';
?>
