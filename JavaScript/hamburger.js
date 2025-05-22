'use strict';

 (function() {
            const btn = document.getElementById('menuToggle');
            const overlay = document.getElementById('globalMenu');
            const backdrop = document.getElementById('menuBackdrop');
            const items = overlay.querySelectorAll('[role="menuitem"]');
            let lastFocus;

            function openMenu() {
                lastFocus = document.activeElement;
                btn.classList.add('active');
                backdrop.classList.add('active');
                overlay.classList.add('active');
                btn.setAttribute('aria-expanded', 'true');
                overlay.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                items[0].focus();
                document.addEventListener('keydown', onKeyDown);
            }

            function closeMenu() {
                btn.classList.remove('active');
                backdrop.classList.remove('active');
                overlay.classList.remove('active');
                btn.setAttribute('aria-expanded', 'false');
                overlay.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
                lastFocus && lastFocus.focus();
                document.removeEventListener('keydown', onKeyDown);
            }

            function onKeyDown(e) {
                if (e.key === 'Escape') {
                    closeMenu();
                }
                if (e.key === 'Tab') {
                    const first = items[0],
                        last = items[items.length - 1];
                    if (e.shiftKey && document.activeElement === first) {
                        e.preventDefault();
                        last.focus();
                    } else if (!e.shiftKey && document.activeElement === last) {
                        e.preventDefault();
                        first.focus();
                    }
                }
            }
            btn.addEventListener('click', () => {
                btn.classList.contains('active') ? closeMenu() : openMenu();
            });
            backdrop.addEventListener('click', closeMenu);
            items.forEach(a => a.addEventListener('click', closeMenu));
        })();