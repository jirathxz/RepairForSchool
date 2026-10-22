<?php
require_once __DIR__ . '/../layout/header.php';
?>

<div class="min-h-[calc(100dvh-12rem)] flex items-center justify-center py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
  <div class="w-full max-w-md space-y-6">

    <!-- Login Card (Flat, No Border, No Shadow) -->
    <div class="bg-white rounded-3xl overflow-hidden relative">
      <div class="p-6 sm:p-8">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
          <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-violet-600 text-white mb-4">
            <i class="fa-solid fa-wrench text-2xl"></i>
          </div>
          <h1 class="text-2xl font-bold tracking-tight text-slate-900 font-display">เข้าสู่ระบบ</h1>
          <p class="text-xs text-slate-500 mt-1">ระบบบริหารจัดการงานแจ้งซ่อมบำรุงโรงเรียน</p>
        </div>

        <!-- Form -->
        <form action="index.php?page=login-submit" method="POST" autocomplete="on" class="space-y-4">
          <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::csrfToken() ?>">
          <div>
            <label for="username" class="block text-xs font-semibold text-slate-700 mb-1.5">
              <i class="fa-solid fa-user text-slate-400 mr-1.5" aria-hidden="true"></i>ชื่อผู้ใช้งาน
            </label>
            <input 
              type="text" 
              class="w-full px-4 py-3 rounded-xl bg-slate-100 text-slate-900 text-sm placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-violet-600 transition-colors" 
              id="username" 
              name="username" 
              autocomplete="username"
              spellcheck="false"
              placeholder="admin, teacher01, tech01" 
              required 
              autofocus
            >
          </div>

          <div>
            <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
              <i class="fa-solid fa-lock text-slate-400 mr-1.5" aria-hidden="true"></i>รหัสผ่าน
            </label>
            <input 
              type="password" 
              class="w-full px-4 py-3 rounded-xl bg-slate-100 text-slate-900 text-sm placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-violet-600 transition-colors" 
              id="password" 
              name="password" 
              autocomplete="current-password"
              placeholder="กรอกรหัสผ่าน" 
              required
            >
          </div>

          <div class="pt-2">
            <button 
              type="submit" 
              class="w-full py-3.5 px-4 min-h-[44px] rounded-xl bg-violet-600 hover:bg-violet-700 active:bg-violet-800 text-white font-semibold text-sm transition-colors flex items-center justify-center gap-2 group focus:outline-none whitespace-nowrap"
            >
              <span>เข้าสู่ระบบ</span>
              <i class="fa-solid fa-arrow-right text-xs transition-transform group-hover:translate-x-0.5" aria-hidden="true"></i>
            </button>
          </div>
        </form>
      </div>

      <!-- Quick Demo Login Helpers Footer -->
      <div class="bg-slate-50 px-6 sm:px-8 py-5">
        <div class="flex items-center gap-2 text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
          <i class="fa-solid fa-bolt text-violet-600"></i>
          <span>เข้าสู่ระบบด่วนสำหรับการทดสอบ</span>
        </div>
        <p class="text-[11px] text-slate-500 mb-3">คลิกเลือกบทบาทเพื่อกรอกบัญชีทดสอบอัตโนมัติ รหัสผ่านคือ password123</p>
        
        <div class="space-y-2">
          <button 
            type="button" 
            onclick="fillLogin('admin', 'password123')"
            class="w-full text-left p-3 min-h-[44px] rounded-xl bg-violet-50 hover:bg-violet-100 text-violet-900 transition-colors flex items-center justify-between text-xs group focus:outline-none"
          >
            <span class="font-medium flex items-center gap-2">
              <i class="fa-solid fa-shield-halved text-violet-600" aria-hidden="true"></i>
              <strong>1. ผู้ดูแลระบบ</strong> — อ.สมเกียรติ
            </span>
            <span class="px-2 py-0.5 rounded-md bg-violet-200 text-violet-800 font-mono text-[10px] font-bold">admin</span>
          </button>

          <button 
            type="button" 
            onclick="fillLogin('teacher01', 'password123')"
            class="w-full text-left p-3 min-h-[44px] rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 transition-colors flex items-center justify-between text-xs group focus:outline-none"
          >
            <span class="font-medium flex items-center gap-2">
              <i class="fa-solid fa-chalkboard-user text-slate-600" aria-hidden="true"></i>
              <strong>2. ครูและบุคลากร</strong> — ครูสุดา
            </span>
            <span class="px-2 py-0.5 rounded-md bg-slate-200 text-slate-700 font-mono text-[10px] font-bold">teacher01</span>
          </button>

          <button 
            type="button" 
            onclick="fillLogin('tech01', 'password123')"
            class="w-full text-left p-3 min-h-[44px] rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 transition-colors flex items-center justify-between text-xs group focus:outline-none"
          >
            <span class="font-medium flex items-center gap-2">
              <i class="fa-solid fa-wrench text-slate-600" aria-hidden="true"></i>
              <strong>3. เจ้าหน้าที่ช่าง</strong> — ช่างสมชาย
            </span>
            <span class="px-2 py-0.5 rounded-md bg-slate-200 text-slate-700 font-mono text-[10px] font-bold">tech01</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Back to landing link -->
    <div class="text-center">
      <a href="index.php?page=landing" class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-slate-800 font-medium transition-colors">
        <i class="fa-solid fa-arrow-left text-[10px]"></i>
        <span>กลับสู่หน้าแรก</span>
      </a>
    </div>

  </div>
</div>

<script>
function fillLogin(user, pass) {
  const u = document.getElementById('username');
  const p = document.getElementById('password');
  if (u && p) {
    u.value = user;
    p.value = pass;
    u.focus();
  }
}
</script>

<?php
require_once __DIR__ . '/../layout/footer.php';
?>
