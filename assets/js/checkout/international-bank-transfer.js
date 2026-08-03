(function ($) {
    'use strict';

    /**
     * Copy text using the modern clipboard API when available.
     * Fall back to a temporary textarea for older browsers.
     */
    function copyText(text) {
        if (
            navigator.clipboard &&
            window.isSecureContext
        ) {
            return navigator.clipboard.writeText(text);
        }

        return new Promise(function (resolve, reject) {
            var textarea = document.createElement('textarea');

            textarea.value = text;
            textarea.setAttribute('readonly', '');
            textarea.style.position = 'fixed';
            textarea.style.top = '-9999px';
            textarea.style.left = '-9999px';
            textarea.style.opacity = '0';

            document.body.appendChild(textarea);

            textarea.focus();
            textarea.select();
            textarea.setSelectionRange(0, textarea.value.length);

            try {
                var successful = document.execCommand('copy');

                document.body.removeChild(textarea);

                if (successful) {
                    resolve();
                } else {
                    reject(new Error('Copy command was unsuccessful.'));
                }
            } catch (error) {
                document.body.removeChild(textarea);
                reject(error);
            }
        });
    }

    /**
     * Temporarily change a button to show that copying worked.
     */
    function showCopiedState(button) {
        var label = button.querySelector('.axiom-ibt-copy-label');
        var originalLabel = label ? label.textContent : '';

        button.classList.add('is-copied');

        if (label) {
            label.textContent = 'Copied ✓';
        }

        window.setTimeout(function () {
            button.classList.remove('is-copied');

            if (label) {
                label.textContent = originalLabel;
            }
        }, 1800);
    }

    /**
     * Copy individual values or all wire details.
     */
    $(document).on(
        'click',
        '.axiom-ibt-copy, .axiom-ibt-copy-all',
        function (event) {
            event.preventDefault();

            var button = this;
            var text = button.getAttribute('data-copy') || '';

            if (!text) {
                return;
            }

            copyText(text)
                .then(function () {
                    showCopiedState(button);
                })
                .catch(function () {
                    window.prompt(
                        'Copy the information below:',
                        text
                    );
                });
        }
    );
})(jQuery);
