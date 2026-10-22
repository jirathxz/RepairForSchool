</main>

<!-- Tailwind Styled Institutional Footer -->
<footer class="mt-auto bg-white text-slate-500 text-xs py-5 no-print">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3">
    
    <div class="flex items-center gap-2">
      <span class="font-semibold text-slate-800">RepairForSchool</span>
      <span class="text-slate-300">|</span>
      <span>ระบบบริหารจัดการงานแจ้งซ่อมบำรุงโรงเรียน</span>
    </div>

    <div class="flex items-center gap-4 text-slate-400">
      <span class="inline-flex items-center gap-1.5 text-emerald-600 font-medium">
        <i class="fa-solid fa-shield-check text-xs"></i>
        <span>ระบบมีความปลอดภัย</span>
      </span>
      <span>•</span>
      <span>MariaDB 11.4 Engine</span>
      <?php if (\App\Core\Auth::isAdmin()): ?>
      <span>•</span>
      <a href="http://localhost:8090/phpmyadmin" target="_blank" class="text-slate-500 hover:text-violet-600 transition-colors inline-flex items-center gap-1">
        <span>phpMyAdmin</span>
      </a>
      <?php endif; ?>
    </div>

  </div>
</footer>

<!-- App Script (Vanilla interactions) -->
<script src="assets/js/app.js"></script>

<script>
  // Dropdown Toggle with ARIA state management
  function toggleUserDropdown() {
    const menu = document.getElementById('userDropdownMenu');
    const btn = document.getElementById('userMenuButton');
    // Close notification dropdown if open
    const notifMenu = document.getElementById('notificationDropdownMenu');
    if (notifMenu) notifMenu.classList.add('hidden');

    if (menu) {
      const isHidden = menu.classList.toggle('hidden');
      if (btn) btn.setAttribute('aria-expanded', !isHidden ? 'true' : 'false');
    }
  }

  function toggleNotificationDropdown() {
    const menu = document.getElementById('notificationDropdownMenu');
    const btn = document.getElementById('notificationMenuButton');
    // Close user dropdown if open
    const userMenu = document.getElementById('userDropdownMenu');
    if (userMenu) userMenu.classList.add('hidden');

    if (menu) {
      const isHidden = menu.classList.toggle('hidden');
      if (btn) btn.setAttribute('aria-expanded', !isHidden ? 'true' : 'false');
    }
  }

  function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    const btn = document.getElementById('mobileMenuToggle');
    if (menu) {
      const isHidden = menu.classList.toggle('hidden');
      if (btn) btn.setAttribute('aria-expanded', !isHidden ? 'true' : 'false');
    }
  }

  // Navigation Dropdowns Toggle
  function toggleNavMenu(id) {
    const navMenus = ['maintNavMenu', 'systemNavMenu', 'techMaintNavMenu'];
    navMenus.forEach(mId => {
      if (mId !== id) {
        const el = document.getElementById(mId);
        if (el) el.classList.add('hidden');
      }
    });
    const target = document.getElementById(id);
    if (target) {
      target.classList.toggle('hidden');
    }
  }

  // Close dropdown when clicking outside
  document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('userMenuWrapper');
    const menu = document.getElementById('userDropdownMenu');
    const btn = document.getElementById('userMenuButton');
    if (wrapper && menu && !wrapper.contains(e.target)) {
      menu.classList.add('hidden');
      if (btn) btn.setAttribute('aria-expanded', 'false');
    }

    const notifWrapper = document.getElementById('notificationMenuWrapper');
    const notifMenu = document.getElementById('notificationDropdownMenu');
    const notifBtn = document.getElementById('notificationMenuButton');
    if (notifWrapper && notifMenu && !notifWrapper.contains(e.target)) {
      notifMenu.classList.add('hidden');
      if (notifBtn) notifBtn.setAttribute('aria-expanded', 'false');
    }

    // Close navigation dropdowns when clicking outside
    ['maintNavWrapper', 'systemNavWrapper', 'techMaintNavWrapper'].forEach(wId => {
      const w = document.getElementById(wId);
      if (w && !w.contains(e.target)) {
        const dMenu = w.querySelector('[role="menu"]');
        if (dMenu) dMenu.classList.add('hidden');
      }
    });
  });

  // Keyboard accessibility: Close overlays on Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' || e.key === 'Esc') {
      const menu = document.getElementById('userDropdownMenu');
      const btn = document.getElementById('userMenuButton');
      if (menu && !menu.classList.contains('hidden')) {
        menu.classList.add('hidden');
        if (btn) {
          btn.setAttribute('aria-expanded', 'false');
          btn.focus();
        }
      }

      const notifMenu = document.getElementById('notificationDropdownMenu');
      const notifBtn = document.getElementById('notificationMenuButton');
      if (notifMenu && !notifMenu.classList.contains('hidden')) {
        notifMenu.classList.add('hidden');
        if (notifBtn) {
          notifBtn.setAttribute('aria-expanded', 'false');
          notifBtn.focus();
        }
      }

      ['maintNavMenu', 'systemNavMenu', 'techMaintNavMenu'].forEach(mId => {
        const el = document.getElementById(mId);
        if (el && !el.classList.contains('hidden')) {
          el.classList.add('hidden');
        }
      });

      const mobileMenu = document.getElementById('mobileMenu');
      const mobileBtn = document.getElementById('mobileMenuToggle');
      if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
        mobileMenu.classList.add('hidden');
        if (mobileBtn) {
          mobileBtn.setAttribute('aria-expanded', 'false');
          mobileBtn.focus();
        }
      }

      const userModal = document.getElementById('addUserModal');
      if (userModal && !userModal.classList.contains('hidden') && typeof closeUserModal === 'function') {
        closeUserModal();
      }
    }
  });
</script>

</body>
</html>
