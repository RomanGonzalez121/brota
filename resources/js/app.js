import Alpine from 'alpinejs';

const root = document.documentElement;
const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/*
 | Scroll reveal: anything marked data-reveal fades up the first time it
 | enters the viewport. Once only, and it never blocks interaction.
 */
const revealer = new IntersectionObserver((entries) => {
    for (const entry of entries) {
        if (entry.isIntersecting) {
            entry.target.dataset.revealed = '';
            revealer.unobserve(entry.target);
        }
    }
}, { rootMargin: '0px 0px -6% 0px' });

document.querySelectorAll('[data-reveal]').forEach((element) => revealer.observe(element));

/*
 | Theme switch. The new theme is revealed in a circle that grows from the
 | button that was pressed: a clip-path on the incoming snapshot, with a
 | strong ease-out so most of the screen changes at once and the edge settles.
 */
const syncThemeToggles = () => {
    const dark = root.dataset.theme === 'dark';
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => button.setAttribute('aria-pressed', String(dark)));
};

window.toggleTheme = (button) => {
    const next = root.dataset.theme === 'dark' ? 'light' : 'dark';

    const apply = () => {
        root.dataset.theme = next;
        syncThemeToggles();
        try {
            localStorage.setItem('theme', next);
        } catch {
            // Private mode: the choice just will not be remembered.
        }
    };

    if (! document.startViewTransition || reducedMotion()) {
        apply();

        return;
    }

    const { left, top, width, height } = button.getBoundingClientRect();
    const x = left + width / 2;
    const y = top + height / 2;
    const radius = Math.hypot(Math.max(x, window.innerWidth - x), Math.max(y, window.innerHeight - y));

    root.classList.add('theme-switching');

    const transition = document.startViewTransition(apply);

    transition.ready.then(() => {
        root.animate(
            { clipPath: [`circle(0px at ${x}px ${y}px)`, `circle(${radius}px at ${x}px ${y}px)`] },
            { duration: 650, easing: 'cubic-bezier(0.23, 1, 0.32, 1)', pseudoElement: '::view-transition-new(root)' },
        );
    });

    transition.finished.finally(() => root.classList.remove('theme-switching'));
};

syncThemeToggles();

window.Alpine = Alpine;

/*
 | Counts a number up from zero, decelerating as it lands. Used for the
 | Hojitas Verdes balance, a moment people see rarely enough to enjoy.
 */
Alpine.data('countUp', (target) => ({
    value: target,

    init() {
        if (reducedMotion()) {
            return;
        }

        const duration = 1100;
        const start = performance.now();

        const tick = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            this.value = Math.round(target * (1 - Math.pow(1 - progress, 4)));

            if (progress < 1) {
                requestAnimationFrame(tick);
            }
        };

        this.value = 0;
        requestAnimationFrame(tick);
    },
}));

Alpine.start();
