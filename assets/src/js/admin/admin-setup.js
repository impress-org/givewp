/**
 * Accessible Block Links
 *
 * Problem: Hyperlink a component while maintaining screen-reader accessibility and the ability to select text.
 * Solution: Use progressive enhancement to conditionally trigger the target anchor element.
 *
 * @link https://css-tricks.com/block-links-the-search-for-a-perfect-solution/
 */

Array.from(document.querySelectorAll('.setup-item')).forEach((setupItem) => {
    const actionAnchor = setupItem.querySelector('.js-action-link');

    if (actionAnchor) {
        actionAnchor.addEventListener('click', (e) => e.stopPropagation());
        setupItem.style.cursor = 'pointer';
        setupItem.addEventListener('click', (event) => {
            // eslint-disable-line no-unused-vars
            if (!window.getSelection().toString()) {
                actionAnchor.click();
            }
        });
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const trigger = document.querySelector('a.activate-license');
    const dialog = document.querySelector('#give-activate-license-modal');
    const dialogContent = dialog.querySelector('#give-license-activator-wrap');
    const closeButton = dialog.querySelector('.givewp-modal-close');
    const input = dialog.querySelector('input[type="text"]');
    const submitButton = dialog.querySelector('input[type="submit"]');

    trigger.addEventListener('click', (e) => {
        e.preventDefault();
        dialog.showModal();
    });

    dialog.addEventListener('click', () => {
        dialog.close();
    });

    closeButton.addEventListener('click', () => {
        dialog.close();
    });

    dialogContent.addEventListener('click', (e) => {
        e.stopPropagation();
    });

    input.addEventListener('input', () => {
        if (input.value) {
            submitButton.removeAttribute('disabled');
        } else {
            submitButton.setAttribute('disabled', 'disabled');
        }
    });
});
