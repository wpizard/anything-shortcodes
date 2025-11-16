// Handles QR animations for <qr-code> elements rendered by AnyS.
(function () {
    /**
     * Applies animation to a QR element when requested.
     *
     * @param {HTMLElement} qrElement The <qr-code> element.
     */
    function applyQrAnimation(qrElement) {
        if (!qrElement) {
            return;
        }

        var animationAttributeValue = qrElement.getAttribute('data-anys-qr-animation');

        if (!animationAttributeValue) {
            return;
        }

        // Executes animation after the QR code finishes rendering.
        qrElement.addEventListener('codeRendered', function () {
            if (typeof qrElement.animateQRCode === 'function') {
                try {
                    qrElement.animateQRCode(animationAttributeValue);
                } catch (error) {
                    // Prevents frontend interruptions.
                }
            }
        });
    }

    /**
     * Initializes animation handlers for all QR elements.
     */
    function initializeQrAnimations() {
        var qrElements = document.querySelectorAll('qr-code[data-anys-qr-animation]');

        if (!qrElements.length) {
            return;
        }

        qrElements.forEach(function (qrElement) {
            applyQrAnimation(qrElement);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeQrAnimations);
    } else {
        initializeQrAnimations();
    }
})();
