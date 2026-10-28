import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

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
