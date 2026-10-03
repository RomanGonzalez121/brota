import Alpine from 'alpinejs';

const root = document.documentElement;
const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const finePointer = () => window.matchMedia('(hover: hover) and (pointer: fine)').matches;

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
 | Parallax for decorative leaves: data-parallax="18" lets an element drift up
 | to 18px with the pointer, and lag a little behind the scroll. The position
 | eases towards its goal every frame (a damped follow), so it never snaps to
 | the cursor. It writes `translate` on the element itself, and the loop stops
 | as soon as everything has settled.
 */
const drifting = [...document.querySelectorAll('[data-parallax]')].map((element) => ({
    element,
    depth: Number(element.dataset.parallax),
    x: 0,
    y: 0,
}));

if (drifting.length && ! reducedMotion()) {
    let pointerX = 0;
    let pointerY = 0;
    let frame = null;

    const step = () => {
        let moving = false;

        for (const item of drifting) {
            const goalX = pointerX * item.depth;
            const goalY = pointerY * item.depth + window.scrollY * item.depth * 0.008;

            item.x += (goalX - item.x) * 0.07;
            item.y += (goalY - item.y) * 0.07;
            item.element.style.translate = `${item.x.toFixed(2)}px ${item.y.toFixed(2)}px`;

            if (Math.abs(goalX - item.x) > 0.05 || Math.abs(goalY - item.y) > 0.05) {
                moving = true;
            }
        }

        frame = moving ? requestAnimationFrame(step) : null;
    };

    const wake = () => {
        frame ??= requestAnimationFrame(step);
    };

    if (finePointer()) {
        window.addEventListener('pointermove', (event) => {
            pointerX = (event.clientX / window.innerWidth - 0.5) * 2;
            pointerY = (event.clientY / window.innerHeight - 0.5) * 2;
            wake();
        }, { passive: true });
    }

    window.addEventListener('scroll', wake, { passive: true });
}

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

/*
 | Leaves fall across the screen once, the first time a reward is seen. The
 | storage key makes it a one-off: a celebration that repeats on every visit
 | stops being one.
 */
Alpine.data('leafFall', (key) => ({
    playing: false,

    init() {
        if (reducedMotion()) {
            return;
        }

        try {
            if (localStorage.getItem(key)) {
                return;
            }

            localStorage.setItem(key, '1');
        } catch {
            return;
        }

        this.playing = true;
        setTimeout(() => (this.playing = false), 7000);
    },
}));

/*
 | The toast: shows the message a redirect left behind, and any message sent
 | later with a `toast` window event.
 */
Alpine.data('toast', (initial, initialError) => ({
    visible: false,
    message: initial ?? '',
    error: initialError,
    timer: null,

    init() {
        if (this.message) {
            this.$nextTick(() => this.open());
        }
    },

    open() {
        this.visible = true;
        clearTimeout(this.timer);
        this.timer = setTimeout(() => (this.visible = false), 4500);
    },

    show({ message, error = false }) {
        this.message = message;
        this.error = error;
        this.open();
    },
}));

/*
 | Adds a dish to the cart without leaving the page. The server answers with
 | the new totals, and a `cart-updated` event lets the header, the dish and
 | the floating cart bar update themselves. If the request fails, the form is
 | submitted the ordinary way.
 */
Alpine.data('addToCart', () => ({
    busy: false,
    done: false,

    async add() {
        if (this.busy) {
            return;
        }

        const form = this.$root;
        this.busy = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (! response.ok) {
                throw new Error(`Cart request failed: ${response.status}`);
            }

            const detail = await response.json();

            window.dispatchEvent(new CustomEvent('cart-updated', { detail }));
            window.dispatchEvent(new CustomEvent('toast', { detail: { message: detail.message } }));

            this.done = true;
            setTimeout(() => (this.done = false), 1400);
        } catch {
            form.submit();
        } finally {
            this.busy = false;
        }
    },
}));

/*
 | Restarts a CSS animation on an element (removing and re-adding the class
 | only works after the browser has seen the element without it).
 */
window.replay = (element, className) => {
    if (! element) {
        return;
    }

    element.classList.remove(className);
    element.getBoundingClientRect();
    element.classList.add(className);
};

/*
 | The notification bell swings only when there is something new since the
 | last screen, not on every page load.
 */
Alpine.data('bell', (unread) => ({
    open: false,
    ring: false,

    init() {
        try {
            const seen = Number(sessionStorage.getItem('bell-seen') ?? 0);
            this.ring = unread > seen;
            sessionStorage.setItem('bell-seen', String(unread));
        } catch {
            this.ring = false;
        }
    },
}));

/*
 | Top-bar hover: a single highlight glides to whichever link is under the
 | pointer. The first time it appears it is placed without sliding (it should
 | not fly in from the corner); between links it moves; on leaving it fades.
 */
Alpine.data('navGlider', () => ({
    visible: false,
    leaving: null,

    moveTo(link) {
        const glider = this.$refs.glider;

        clearTimeout(this.leaving);

        const place = () => {
            glider.style.width = `${link.offsetWidth}px`;
            glider.style.height = `${link.offsetHeight}px`;
            glider.style.transform = `translate(${link.offsetLeft}px, ${link.offsetTop}px)`;
        };

        if (this.visible) {
            place();
        } else {
            glider.style.transitionProperty = 'opacity';
            place();
            glider.getBoundingClientRect();
            glider.style.transitionProperty = '';
            this.visible = true;
        }

        glider.style.opacity = '1';
    },

    rest() {
        this.$refs.glider.style.opacity = '0';
        this.leaving = setTimeout(() => (this.visible = false), 220);
    },
}));

Alpine.start();
