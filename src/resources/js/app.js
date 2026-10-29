import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// After a failed submit the page reloads at the top, so bring the first error into view.
// Runs after Alpine, so errors in sections Alpine opens on error (modal, change email) count too.
requestAnimationFrame(() => {
    const error = [...document.querySelectorAll('[data-form-error]')]
        .find((element) => element.getClientRects().length > 0);

    if (!error) {
        return;
    }

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    error.scrollIntoView({ block: 'center', behavior: reduceMotion ? 'auto' : 'smooth' });

    const field = error.parentElement?.querySelector('input:not([type="hidden"]), textarea, select');
    field?.focus({ preventScroll: true });
});

document.addEventListener('click', (event) => {
    const shareButton = event.target.closest('.share-button');

    if (!shareButton) {
        return;
    }

    const shareUrl = shareButton.getAttribute('data-share-url');
    const shareTitle = shareButton.getAttribute('data-share-title');
    const appName = shareButton.getAttribute('data-share-app-name');

    if (!shareUrl || !shareTitle || !appName) {
        return;
    }

    if (navigator.share) {
        navigator.share({
            title: `${shareTitle} | ${appName}`,
            url: shareUrl,
        }).then(() => {
            console.log('Thanks for sharing!');
        }).catch(console.error);
        return;
    }

    navigator.clipboard.writeText(shareUrl).then(() => {
        alert('Link copied to clipboard!');
    }).catch(console.error);
});

// deterrent only: the files stay public under storage/
['contextmenu', 'dragstart'].forEach((type) => {
    document.addEventListener(type, (event) => {
        if (event.target.closest?.('img[data-protected]')) {
            event.preventDefault();
        }
    });
});
