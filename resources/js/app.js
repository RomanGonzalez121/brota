import Alpine from 'alpinejs';
import morph from '@alpinejs/morph';

Alpine.plugin(morph);

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
 | Smooth forms. Every form that posts is sent in the background: the server
 | answers with the page as it now is, and only what changed is updated in
 | place. No navigation, so no flash, no reloaded photos, no lost scroll
 | position and no entrance animations replaying.
 |
 | What changes is shown, not swapped:
 |   - a number marked data-pop rolls up when it grows and down when it shrinks;
 |   - a list item marked data-item (with an id) folds away when it is gone,
 |     and glides to its new place when it moved, like an order changing column;
 |   - anything new fades in.
 |
 | A form whose job is to take the person to another screen (sign in, pay,
 | publish) says data-smooth="off" and behaves like an ordinary form. One that
 | may be pressed again before the answer is back (the cart's plus and minus)
 | says data-smooth="queue", so no press is lost. Regions outside <main> that
 | must follow along, like the bell, are marked data-live.
 */
const EASE_OUT = 'cubic-bezier(0.23, 1, 0.32, 1)';
const EASE_IN_OUT = 'cubic-bezier(0.77, 0, 0.175, 1)';

const flashOf = (page) => {
    const flash = page.querySelector('[data-flash]');

    return flash?.dataset.flash ? { message: flash.dataset.flash, error: flash.dataset.flashError === '1' } : null;
};

const amount = (text) => Number(text.replace(/[^\d-]/g, ''));
const itemsIn = (scope) => [...scope.querySelectorAll('[data-item][id]')];

// Fades the item out while what comes after it closes the gap it leaves.
const leave = (element) => {
    const box = element.getBoundingClientRect();
    const next = element.nextElementSibling;
    const previous = element.previousElementSibling;
    const gap = next
        ? next.getBoundingClientRect().top - box.top
        : (previous ? box.bottom - previous.getBoundingClientRect().bottom : box.height);
    const pull = reducedMotion() ? 0 : Math.max(0, Math.min(gap, box.height + 48));
    const margin = parseFloat(getComputedStyle(element).marginBottom) || 0;

    element.style.pointerEvents = 'none';

    return Promise.all([
        element.animate({ opacity: 0 }, { duration: 160, easing: EASE_OUT, fill: 'forwards' }).finished,
        element.animate({ marginBottom: `${margin - pull}px` }, { duration: 260, easing: EASE_OUT, fill: 'forwards' }).finished,
    ]);
};

const enter = (element) => {
    if (! (element instanceof HTMLElement) || element.matches('[data-reveal], input') || getComputedStyle(element).animationName !== 'none') {
        return;
    }

    element.animate(
        reducedMotion() ? { opacity: [0, 1] } : { opacity: [0, 1], transform: ['translateY(8px)', 'none'] },
        { duration: 260, easing: EASE_OUT },
    );
};

let sending = Promise.resolve();
let navigating = false;

const parse = (html) => new DOMParser().parseFromString(html, 'text/html');

/*
 | Brings <main> (and the live regions) in line with a freshly rendered copy of
 | the page. `arrived` marks new list items so they are noticed: it is set when
 | the page updates on its own, not as the answer to something the person did.
 */
const update = async (page, { arrived = false } = {}) => {
    const next = page.querySelector('main');
    const current = document.querySelector('main');

    await Promise.all(itemsIn(current).filter((item) => ! page.getElementById(item.id)).map(leave));

    const before = new Map(itemsIn(current).map((item) => [item.id, item.getBoundingClientRect()]));
    const rolled = [];
    const added = [];

    const updating = (from, to) => {
        if (from.nodeType !== Node.ELEMENT_NODE) {
            return;
        }

        // What was already revealed or uncloaked stays that way; it must not hide and animate again.
        if (from.hasAttribute('data-revealed')) {
            to.setAttribute('data-revealed', '');
        }

        if (! from.hasAttribute('x-cloak')) {
            to.removeAttribute('x-cloak');
        }

        if (from.hasAttribute('data-pop') && from.textContent.trim() !== to.textContent.trim()) {
            rolled.push([from, amount(to.textContent) >= amount(from.textContent)]);
        }
    };

    Alpine.morph(current, next, {
        key: (element) => (element.hasAttribute('data-item') ? element.id : ''),
        updating,
        added(element) {
            if (element.nodeType !== Node.ELEMENT_NODE) {
                return;
            }

            // An item that only changed place is already known: it glides there instead of appearing.
            if (before.has(element.id)) {
                [element, ...element.querySelectorAll('[data-reveal]')].forEach((part) => part.setAttribute('data-revealed', ''));
            } else {
                added.push(element);
            }
        },
    });

    document.querySelectorAll('[data-live][id]').forEach((region) => {
        const fresh = page.getElementById(region.id);

        if (fresh && ! current.contains(region)) {
            Alpine.morph(region, fresh, { updating });
        }
    });

    document.title = page.title;
    current.querySelectorAll('[data-reveal]:not([data-revealed])').forEach((element) => revealer.observe(element));
    rolled.forEach(([element, up]) => window.replay(element, up ? 'animate-roll-up' : 'animate-roll-down'));
    added.forEach(enter);

    if (arrived) {
        added.filter((element) => element.matches('[data-item]')).forEach((element) => element.setAttribute('data-arrived', ''));
    }

    if (! reducedMotion()) {
        for (const item of itemsIn(current)) {
            const was = before.get(item.id);
            const now = item.getBoundingClientRect();

            // Nothing to glide to when it ended up somewhere that is not on screen (another tab on a phone).
            if (was && now.width > 0 && (Math.abs(was.left - now.left) > 1 || Math.abs(was.top - now.top) > 1)) {
                item.animate(
                    { transform: [`translate(${was.left - now.left}px, ${was.top - now.top}px)`, 'none'] },
                    { duration: 340, easing: EASE_IN_OUT },
                );
            }
        }
    }

    const count = page.querySelector('[data-cart-count]')?.dataset.cartCount;
    const cartLink = document.querySelector('[data-cart-count]');

    if (cartLink && count !== undefined && count !== cartLink.dataset.cartCount) {
        cartLink.dataset.cartCount = count;
        window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: Number(count) } }));
    }
};

const send = async (form, submitter) => {
    if (navigating || ! form.isConnected) {
        return;
    }

    submitter?.setAttribute('aria-busy', 'true');

    try {
        const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'text/html' } });
        const page = parse(await response.text());
        const next = page.querySelector('main');
        const toast = flashOf(page);

        // The action led somewhere else (a rejected order goes back to the board): go there.
        if (! next || ! document.querySelector('main') || new URL(response.url).pathname !== window.location.pathname) {
            if (toast) {
                sessionStorage.setItem('pending-toast', JSON.stringify(toast));
            }

            navigating = true;
            window.location.assign(response.url);

            return;
        }

        await update(page);

        // It went through: put the form back to its saved values and let sheets and
        // inline editors close. With validation errors, what was typed stays as it is.
        if (! next.querySelector('[aria-invalid="true"]')) {
            if (form.isConnected) {
                form.reset();
            }

            window.dispatchEvent(new CustomEvent('smooth-done'));
        }

        if (toast) {
            window.dispatchEvent(new CustomEvent('toast', { detail: toast }));
        }
    } catch {
        // The request itself failed: fall back to an ordinary reload of where we are.
        navigating = true;
        window.location.reload();
    } finally {
        delete form.dataset.busy;
        submitter?.removeAttribute('aria-busy');
    }
};

/*
 | Brings the current screen up to date without anyone pressing anything, and
 | waits its turn behind any form that is being sent. If it cannot, the screen
 | simply stays as it was.
 */
window.refreshPage = () => {
    sending = sending.then(async () => {
        if (navigating) {
            return;
        }

        try {
            const response = await fetch(window.location.href, { headers: { Accept: 'text/html' } });
            const page = parse(await response.text());

            if (response.ok && page.querySelector('main') && new URL(response.url).pathname === window.location.pathname) {
                await update(page, { arrived: true });
            }
        } catch {
            // Offline or interrupted: the next check tries again.
        }
    });

    return sending;
};

/*
 | "4 min", "2 h": how long ago something happened, kept current while the
 | screen stays open. It turns into a warning once it passes its limit. The
 | text just changes; a clock that animated every tick would be noise.
 */
const elapsedLabel = (minutes) => {
    if (minutes < 1) {
        return 'recién';
    }

    if (minutes < 60) {
        return `${minutes} min`;
    }

    return minutes < 1440 ? `${Math.floor(minutes / 60)} h` : `${Math.floor(minutes / 1440)} d`;
};

setInterval(() => {
    document.querySelectorAll('[data-since]').forEach((clock) => {
        const minutes = Math.floor((Date.now() / 1000 - Number(clock.dataset.since)) / 60);

        clock.querySelector('[data-since-label]').textContent = elapsedLabel(minutes);
        clock.toggleAttribute('data-late', minutes >= Number(clock.dataset.lateAfter));
    });
}, 30000);

/*
 | The restaurant's board. Every few seconds it asks whether anything changed
 | (a new order, a delivery marked by the courier) and, if so, brings the
 | screen up to date in place. On phones it also remembers which lane is open.
 */
Alpine.data('orderBoard', (pendingUrl) => ({
    lane: 0,

    init() {
        setInterval(async () => {
            try {
                const response = await fetch(pendingUrl, { headers: { Accept: 'application/json' } });

                if (! response.ok) {
                    return;
                }

                const now = await response.json();

                if (now.stamp === this.$root.dataset.stamp) {
                    return;
                }

                const more = now.count > Number(this.$root.dataset.waiting);

                await window.refreshPage();

                if (more) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Entró un pedido nuevo.' } }));
                }
            } catch {
                // Offline: the next check tries again.
            }
        }, 8000);
    },
}));

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (
        ! (form instanceof HTMLFormElement)
        || event.defaultPrevented
        || form.method.toLowerCase() !== 'post'
        || form.dataset.smooth === 'off'
    ) {
        return;
    }

    event.preventDefault();

    if (form.dataset.busy && form.dataset.smooth !== 'queue') {
        return;
    }

    form.dataset.busy = '1';

    // One at a time and in order, so each request is built from what the previous one left.
    const submitter = event.submitter;
    sending = sending.then(() => send(form, submitter));
});

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
        // A smooth form that had to change page leaves its message here for the next one.
        try {
            const pending = JSON.parse(sessionStorage.getItem('pending-toast') ?? 'null');
            sessionStorage.removeItem('pending-toast');

            if (pending && ! this.message) {
                this.message = pending.message;
                this.error = pending.error;
            }
        } catch {
            // No storage: the message is simply not carried over.
        }

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
