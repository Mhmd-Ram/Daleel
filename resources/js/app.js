/**
 * Front-end motion.
 *
 * - Scroll-reveal: `.reveal` elements fade/slide in once as they enter view.
 * - Header: gains `.is-scrolled` once a top sentinel leaves the viewport.
 * - Menu: a toggle opens/closes a full-screen overlay menu (desktop + mobile).
 * - Particles: a field of soft, multi-colored dots that fall continuously.
 *
 * Everything degrades gracefully: without JS the page is fully visible, and
 * users who prefer reduced motion get content revealed instantly.
 */
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function setupScrollReveal() {
    const targets = document.querySelectorAll('.reveal');

    if (prefersReducedMotion || !('IntersectionObserver' in window)) {
        targets.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries, obs) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    obs.unobserve(entry.target);
                }
            });
        },
        { rootMargin: '0px 0px -10% 0px', threshold: 0.15 },
    );

    targets.forEach((el) => observer.observe(el));
}

function setupHeaderState() {
    const header = document.querySelector('[data-header]');
    const sentinel = document.querySelector('[data-scroll-sentinel]');

    if (!header || !sentinel || !('IntersectionObserver' in window)) {
        return;
    }

    const observer = new IntersectionObserver(
        ([entry]) => header.classList.toggle('is-scrolled', !entry.isIntersecting),
        { threshold: 0 },
    );

    observer.observe(sentinel);
}

function setupMenu() {
    const toggle = document.querySelector('[data-menu-toggle]');
    const menu = document.querySelector('[data-menu]');

    if (!toggle || !menu) {
        return;
    }

    const open = () => {
        toggle.setAttribute('aria-expanded', 'true');
        menu.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        menu.querySelector('a, button')?.focus({ preventScroll: true });
    };

    const close = () => {
        toggle.setAttribute('aria-expanded', 'false');
        menu.classList.remove('is-open');
        document.body.style.overflow = '';
    };

    toggle.addEventListener('click', () => {
        const isOpen = toggle.getAttribute('aria-expanded') === 'true';
        isOpen ? close() : open();
    });

    menu.querySelectorAll('a').forEach((link) => link.addEventListener('click', close));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && menu.classList.contains('is-open')) {
            close();
            toggle.focus();
        }
    });
}

function setupParticles() {
    const layer = document.querySelector('[data-particles]');

    if (!layer) {
        return;
    }

    const COUNT = 200;
    const random = (min, max) => Math.random() * (max - min) + min;
    // Vivid, evenly-bright confetti color: random hue, fixed saturation/lightness.
    const randomColor = () => `hsl(${Math.round(random(0, 360))}, 85%, 62%)`;
    let viewportHeight = window.innerHeight;

    const dots = [];
    const fragment = document.createDocumentFragment();

    for (let i = 0; i < COUNT; i++) {
        const el = document.createElement('span');
        el.className = 'eh-dot';

        const dot = {
            el,
            x: random(0, 100),
            size: random(2, 7),
            speed: random(14, 62), // px per second
            y: random(-viewportHeight, viewportHeight),
            baseOpacity: random(0.3, 0.9),
            twinkleAmp: random(0, 0.32),
            twinkleSpeed: random(0.3, 1.2),
            phase: random(0, Math.PI * 2),
        };

        el.style.left = `${dot.x}%`;
        el.style.width = `${dot.size}px`;
        el.style.height = `${dot.size}px`;
        el.style.backgroundColor = randomColor();
        el.style.transform = `translate3d(0, ${dot.y}px, 0)`;
        el.style.opacity = `${dot.baseOpacity}`;

        fragment.appendChild(el);
        dots.push(dot);
    }

    layer.appendChild(fragment);
    window.addEventListener('resize', () => { viewportHeight = window.innerHeight; });

    // Reduced motion: leave the field static.
    if (prefersReducedMotion) {
        return;
    }

    let last = performance.now();

    const tick = (now) => {
        const dt = Math.min((now - last) / 1000, 0.05); // clamp after tab switches
        last = now;

        for (let i = 0; i < dots.length; i++) {
            const dot = dots[i];

            dot.y += dot.speed * dt;
            if (dot.y > viewportHeight + dot.size) {
                dot.y = -dot.size;
                dot.x = random(0, 100);
                dot.el.style.left = `${dot.x}%`;
                dot.el.style.backgroundColor = randomColor(); // new color each loop
            }

            const twinkle = dot.baseOpacity + Math.sin(now / 1000 * dot.twinkleSpeed + dot.phase) * dot.twinkleAmp;
            const opacity = Math.min(0.95, Math.max(0.18, twinkle));

            dot.el.style.transform = `translate3d(0, ${dot.y}px, 0)`;
            dot.el.style.opacity = opacity.toFixed(3);
        }

        requestAnimationFrame(tick);
    };

    requestAnimationFrame(tick);
}

setupScrollReveal();
setupHeaderState();
setupMenu();
setupParticles();
