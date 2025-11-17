// Handles QR animations for <qr-code> elements rendered by AnyS.
window.anys            = window.anys || {};
window.anys.shortcodes = window.anys.shortcodes || {};

/**
 * Controls QR animations for <qr-code> elements.
 *
 * @since NEXT
 */
class QRAnimator {
    /**
     * Constructor.
     *
     * @param {Object} options Optional configuration object.
     * @param {string} [options.selector] CSS selector for QR elements.
     * @param {string} [options.animationAttr] Data attribute that holds animation name.
     * @param {string} [options.renderEvent] Event name fired when QR is rendered.
     * @param {string} [options.animationMethod] Method name on element that triggers animation.
     */
    constructor(options) {
        this.settings = Object.assign(
            {
                selector: 'qr-code[data-anys-qr-animation]',
                animationAttr: 'data-anys-qr-animation',
                renderEvent: 'codeRendered',
                animationMethod: 'animateQRCode',
            },
            options || {}
        );

        this.attachAll = this.attachAll.bind(this);
    }

    /**
     * Initializes the animator on DOM ready.
     */
    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', this.attachAll);
        } else {
            this.attachAll();
        }
    }

    /**
     * Attaches animation handlers to all matched elements.
     */
    attachAll() {
        var elements = document.querySelectorAll(this.settings.selector);

        if (!elements.length) {
            return;
        }

        elements.forEach((element) => {
            this.attach(element);
        });
    }

    /**
     * Attaches animation handler to a single element.
     *
     * @param {HTMLElement} element The QR element.
     */
    attach(element) {
        if (!element) {
            return;
        }

        var attrName  = this.settings.animationAttr;
        var animation = element.getAttribute(attrName);

        if (!animation) {
            return;
        }

        var eventName      = this.settings.renderEvent;
        var animationMethod = this.settings.animationMethod;

        element.addEventListener(
            eventName,
            function () {
                var animateFn = element[animationMethod];

                if (typeof animateFn !== 'function') {
                    return;
                }

                try {
                    animateFn.call(element, animation);
                } catch (error) {
                    // Intentionally ignores animation errors.
                }
            },
            { once: true }
        );
    }
}

// Exposes class for external usage.
window.anys.shortcodes.QRAnimator = QRAnimator;

// Creates default singleton instance.
window.anys.shortcodes.qrAnimator = new window.anys.shortcodes.QRAnimator();
window.anys.shortcodes.qrAnimator.init();
