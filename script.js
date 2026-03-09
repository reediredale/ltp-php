// Mobile Menu Toggle
const menuToggle = document.querySelector('.menu-toggle');
const navLinks = document.querySelector('.nav-links');
const menuOverlay = document.querySelector('.menu-overlay');
const body = document.body;

function openMenu() {
    navLinks.classList.add('active');
    menuToggle.classList.add('active');
    menuOverlay.classList.add('active');
    body.classList.add('menu-open');
    menuToggle.setAttribute('aria-expanded', 'true');
}

function closeMenu() {
    navLinks.classList.remove('active');
    menuToggle.classList.remove('active');
    menuOverlay.classList.remove('active');
    body.classList.remove('menu-open');
    menuToggle.setAttribute('aria-expanded', 'false');
}

if (menuToggle) {
    // Toggle menu when clicking hamburger button
    menuToggle.addEventListener('click', () => {
        if (navLinks.classList.contains('active')) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    // Close menu when clicking overlay
    menuOverlay.addEventListener('click', closeMenu);

    // Close menu when clicking on a link
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', closeMenu);
    });

    // Close menu when pressing Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && navLinks.classList.contains('active')) {
            closeMenu();
        }
    });
}
