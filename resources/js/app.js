// Small progressive enhancements for the JourneyOps demo. Every page works without JavaScript.

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// Reveal sections as they scroll into view.
const revealables = document.querySelectorAll('[data-reveal]');
if ('IntersectionObserver' in window && !reduceMotion) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
    revealables.forEach((el) => observer.observe(el));
} else {
    revealables.forEach((el) => el.classList.add('is-visible'));
}

// Before / after switch for journey traces.
document.querySelectorAll('[data-trace]').forEach((trace) => {
    const tabs = trace.querySelectorAll('[data-trace-tab]');
    const panels = trace.querySelectorAll('[data-trace-panel]');

    const show = (name) => {
        tabs.forEach((tab) => {
            const active = tab.dataset.traceTab === name;
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
            tab.tabIndex = active ? 0 : -1;
        });
        panels.forEach((panel) => {
            const active = panel.dataset.tracePanel === name;
            panel.hidden = !active;
            if (active) {
                // Restart the step animation for the newly shown branch.
                panel.querySelectorAll('[data-trace-step]').forEach((step) => {
                    step.style.animation = 'none';
                    void step.offsetWidth;
                    step.style.animation = '';
                });
            }
        });
    };

    let interacted = false;
    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => { interacted = true; show(tab.dataset.traceTab); });
        tab.addEventListener('keydown', (event) => {
            if (event.key !== 'ArrowRight' && event.key !== 'ArrowLeft') return;
            event.preventDefault();
            const next = tabs[(index + (event.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length];
            interacted = true;
            next.focus();
            show(next.dataset.traceTab);
        });
    });

    const autoplay = trace.dataset.traceAutoplay;
    if (autoplay && !reduceMotion) {
        setTimeout(() => { if (!interacted) show(autoplay); }, 3200);
    }
});

// Copy-to-clipboard helpers (demo credentials, commands).
document.querySelectorAll('[data-copy]').forEach((button) => {
    button.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(button.dataset.copy);
            const original = button.textContent;
            button.textContent = 'Copied';
            setTimeout(() => { button.textContent = original; }, 1400);
        } catch {
            // Clipboard unavailable: the value stays visible on the page.
        }
    });
});
