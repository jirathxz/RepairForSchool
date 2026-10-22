# DESIGN.md — Modern Flat UI Design System

## 1. Design Philosophy
- **Style:** Modern Flat UI (Clean, Minimal, Purposeful)
- **Visuals:** Surface-contrast separation rather than borders or shadows
- **Tone:** Professional institutional interface for school repair and asset management

---

## 2. Color Palette (80 / 20 Rule)

### 80% — White & Gray Spectrum (Surfaces, Typography, Structural Elements)
- **Pure White:** `#ffffff` (`bg-white`) — Main card surfaces, inputs, modal backgrounds
- **Light Gray Canvas:** `#f8fafc` / `#f1f5f9` (`bg-slate-50`, `bg-slate-100`) — Page background, subtle segment highlights
- **Mid Gray Accents:** `#e2e8f0` (`bg-slate-200`) — Hover states, pill backgrounds
- **Muted Slate:** `#64748b` (`text-slate-500`) — Secondary text, metadata, labels
- **Deep Slate:** `#0f172a` (`text-slate-900`, `bg-slate-900`) — Headers, high-contrast headings, main navbar

### 20% — Violet Spectrum (Primary Actions, Active States, Key Metrics)
- **Primary Violet:** `#7c3aed` (`bg-violet-600`, `text-violet-600`) — Primary CTA buttons, key numbers, active tabs
- **Deep Violet:** `#6d28d9` (`hover:bg-violet-700`) — Hover and focus states
- **Light Violet Soft:** `#f5f3ff` (`bg-violet-50`, `text-violet-700`) — Role badges, selected highlights
- **Violet Borderless Pill:** `#ede9fe` (`bg-violet-100`, `text-violet-800`) — Tags and counters

---

## 3. Core Style Constraints

1. **No Border (`border-0`):**
   - Cards, tables, modals, badges, and headers do NOT use outline borders.
   - Contrast is established using background tone differences (e.g. `bg-white` on `bg-slate-100`).
2. **No Shadow (`shadow-none`):**
   - Eliminate all drop shadows (`shadow-sm`, `shadow-md`, `shadow-lg`, `shadow-xl`).
   - Pure flat aesthetic.
3. **No Gradient:**
   - No gradient backgrounds (`bg-gradient-to-...` or CSS gradients).
   - Solid, reliable colors only.
4. **No Emoji, Use Vector Icons:**
   - Strictly no unicode emojis (e.g. ⚡, 🎯, 🚀, 📋, 👨‍🏫, 🔧).
   - Use Font Awesome 6 icons (`<i class="fa-solid fa-..."></i>`).
5. **No Language Parentheses `()`:**
   - Never write role or status translations in parentheses (e.g. avoid `ผู้ดูแลระบบ (Admin)` or `รอดำเนินการ (Pending)`).
   - Use clean, direct language: `ผู้ดูแลระบบ`, `ช่างเทคนิค`, `ครูและบุคลากร`.
6. **Responsive Layout:**
   - Mobile-first flexible grid and flexbox, accessible 44px touch targets.
