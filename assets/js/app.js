/**
 * RepairForSchool - Client UI Interactions (Pure Vanilla JS & Tailwind)
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Image Preview Handler for Before & After uploads
    const setupImagePreview = (inputSelector, previewContainerSelector) => {
        const input = document.querySelector(inputSelector);
        const container = document.querySelector(previewContainerSelector);
        if (!input || !container) return;

        input.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('ขนาดไฟล์รูปภาพเกิน 5MB กรุณาเลือกภาพที่มีขนาดเล็กลง');
                    input.value = '';
                    container.innerHTML = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = (event) => {
                    container.innerHTML = `
                        <div class="mt-3 relative inline-block border border-slate-200 rounded-xl p-1.5 bg-white shadow-sm overflow-hidden">
                            <img src="${event.target.result}" class="rounded-lg object-contain max-h-48 max-w-full" alt="Preview">
                            <span class="absolute bottom-3 left-3 px-2 py-0.5 rounded-md bg-slate-900/80 text-white text-[10px] font-medium backdrop-blur-sm">
                                ตัวอย่างรูปภาพก่อนบันทึก
                            </span>
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                container.innerHTML = '';
            }
        });
    };

    setupImagePreview('#before_image_input', '#before_image_preview');
    setupImagePreview('#after_image_input', '#after_image_preview');

    // 2. Auto-dismiss Flash Alerts after 6 seconds
    const alerts = document.querySelectorAll('[role="alert"]:not(.alert-permanent)');
    alerts.forEach((alert) => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 500);
        }, 6000);
    });
});
