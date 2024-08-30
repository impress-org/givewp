/**
 * This file contains all the logic to load async data on the project's available form list views, including form grid and admin form list views.
 *
 * The async data are loaded (only for the items visible on the screen) on the following conditions:
 *
 * 1) At the page's first load
 * 2) When the user adds a block in the WP block editor
 * 3) When the user scrolls the mouse
 * 4) When the user resizes the screen
 *
 * @since 3.16.0
 */
document.addEventListener('DOMContentLoaded', () => {
    /**
     * We are declaring it at the top to use it in more than one function.
     */
    let throttleTimer = false;
    let abortLoadAsyncData = false;
    const giveListTable = document.querySelector('.giveListTable');
    const giveListTableIsLoadingEvent = new Event('giveListTableIsLoading');

    /**
     * This function check if the element is visible on the screen.
     *
     * @since 3.16.0
     */
    function isInViewport(element) {
        const {top, bottom} = element.getBoundingClientRect();
        const vHeight = window.innerHeight || document.documentElement.clientHeight;

        return (top > 0 || bottom > 0) && top < vHeight;
    }

    /**
     * Check if an element is a placeholder waiting to have the value updated.
     *
     * @since 3.16.0
     */
    function isPlaceholder(element) {
        return !!element && Boolean(element.querySelector('.js-give-async-data'));
    }

    /**
     * This function fetch the async data from the server and set the values to the proper elements in the DOM.
     *
     * @since 3.16.0
     */
    const loadFormData = (
        formId,
        itemElement,
        amountRaisedElement = null,
        progressBarElement = null,
        goalAchievedElement = null,
        donationsElement = null,
        earningsElement = null
    ) => {
        // If we don't have any of these elements with a placeholder waiting to be updated, then return.
        if (
            !isPlaceholder(amountRaisedElement) &&
            !isPlaceholder(donationsElement) &&
            !isPlaceholder(earningsElement)
        ) {
            return;
        }

        // Limit requests to run one per time.
        if (window.GiveDonationFormsAsyncData.throttlingEnabled && throttleTimer) {
            window.GiveDonationFormsAsyncData.scriptDebug && console.log('throttleTimer start: ', throttleTimer);
            return;
        }

        throttleTimer = true;
        window.GiveDonationFormsAsyncData.scriptDebug &&
            console.log('request start: ', new Date().toLocaleTimeString());

        window.GiveDonationFormsAsyncData.scriptDebug && console.log('item: ', itemElement);

        // This class ensures that once the element has the fetch request triggered we'll not try to fetch it again.
        itemElement.classList.add('give-async-data-fetch-triggered');

        // It can be used to abort the async request when necessary.
        const controller = new AbortController();
        const signal = controller.signal;

        fetch(
            `${window.GiveDonationFormsAsyncData.ajaxUrl}?action=givewp_get_form_async_data_for_list_view&formId=${formId}&nonce=${window.GiveDonationFormsAsyncData.ajaxNonce}`,
            {signal}
        )
            .then(function (response) {
                return response.json();
            })
            .then(function (response) {
                window.GiveDonationFormsAsyncData.scriptDebug && console.log('Response: ', response);

                // Replace the placeholders with the real data returned by the server.
                if (response.success) {
                    if (isPlaceholder(amountRaisedElement)) {
                        amountRaisedElement.innerHTML = response.data.amountRaised;
                    }

                    if (
                        !!progressBarElement &&
                        progressBarElement.style.width !== response.data.percentComplete + '%'
                    ) {
                        progressBarElement.style.width = response.data.percentComplete + '%';
                    }

                    if (!!goalAchievedElement && response.data.percentComplete >= 100) {
                        goalAchievedElement.style.opacity = '1';
                    }

                    if (isPlaceholder(donationsElement)) {
                        donationsElement.innerHTML = response.data.donationsCount;
                    }

                    if (isPlaceholder(earningsElement)) {
                        earningsElement.innerHTML = response.data.revenue;
                    }
                }
            })
            .catch((error) => {
                // When there is an error remove the class that prevents fetch request duplication, so we can try fetching it again in the next try.
                itemElement.classList.remove('give-async-data-fetch-triggered');
                window.GiveDonationFormsAsyncData.scriptDebug && console.log('Error: ', error);
            })
            .finally(() => {
                window.GiveDonationFormsAsyncData.scriptDebug &&
                    console.log('request end: ', new Date().toLocaleTimeString());
                if (window.GiveDonationFormsAsyncData.throttlingEnabled && throttleTimer) {
                    throttleTimer = false;
                    window.GiveDonationFormsAsyncData.scriptDebug && console.log('throttleTimer end: ', throttleTimer);
                    maybeLoadAsyncData();
                }
                window.GiveDonationFormsAsyncData.scriptDebug && console.log('Request finalized.');
            });

        // Make sure to abort all unfinished async requests when leave or refresh the page.
        addEventListener('beforeunload', (event) => {
            abortLoadAsyncData = true;
            controller.abort('Async request aborted due to exit page.');
        });

        // Make sure to abort all unfinished async requests when changing the giveListTable pagination.
        if (giveListTable) {
            giveListTable.addEventListener('giveListTableIsLoading', (event) => {
                abortLoadAsyncData = true;
                controller.abort('Async request aborted due to table loading.');
            });
        }
    };

    /**
     * Handle the async data logic for ALL form list views available.
     *
     * @since 3.16.0
     */
    const maybeLoadAsyncData = () => {
        // If the async requests were aborted on the "beforeunload" or "giveListTableIsLoading" event, we don't want to create more async requests
        if (abortLoadAsyncData) {
            window.GiveDonationFormsAsyncData.scriptDebug && console.log('abortLoadAsyncData');
            return;
        }

        handleAdminFormsListViewItems();
        handleAdminLegacyFormsListViewItems();
        handleFormGridItems();
    };

    /**
     * Check for changes in the "giveListTable" classes to trigger the "giveListTableIsLoadingEvent" when appropriated.
     *
     * @since 3.16.0
     */
    function maybeTriggerGiveListTableIsLoadingEvent() {
        if (giveListTable) {
            const observer = new MutationObserver(function (mutations) {
                if (giveListTable.classList.contains('giveListTableIsLoading')) {
                    giveListTable.dispatchEvent(giveListTableIsLoadingEvent);
                }

                if (giveListTable.classList.contains('giveListTableIsLoaded')) {
                    abortLoadAsyncData = false;
                    maybeLoadAsyncData();
                }
            });

            // Configuration of the observer
            const config = {
                attributes: true,
                childList: false,
                characterData: false,
            };

            // Pass in the target node, as well as the observer options
            observer.observe(giveListTable, config);
        }
    }

    /**
     * Load the async data of all forms (visible on the screen) from the NEW admin form list view - giveListTable.
     *
     * @since 3.16.0
     */
    function handleAdminFormsListViewItems() {
        const adminFormsListViewItems = document.querySelectorAll('tr:not(.give-async-data-fetch-triggered)');
        if (adminFormsListViewItems.length > 0) {
            maybeTriggerGiveListTableIsLoadingEvent();

            adminFormsListViewItems.forEach((itemElement) => {
                const select = itemElement.querySelector('.giveListTableSelect');

                if (!select) {
                    return;
                }

                const formId = select.getAttribute('data-id');
                const amountRaisedElement = itemElement.querySelector("[id^='giveDonationFormsProgressBar'] > span");
                const progressBarElement = itemElement.querySelector('.goalProgress > span');
                const goalAchievedElement = itemElement.querySelector('.goalProgress--achieved');
                const donationsElement = itemElement.querySelector('.column-donations-count-value');
                const earningsElement = itemElement.querySelector('.column-earnings-value');

                if (isInViewport(itemElement)) {
                    loadFormData(
                        formId,
                        itemElement,
                        amountRaisedElement,
                        progressBarElement,
                        goalAchievedElement,
                        donationsElement,
                        earningsElement
                    );
                }
            });
        }
    }

    /**
     * Load the async data of all forms (visible on the screen) from the LEGACY admin form list view.
     *
     * @since 3.16.0
     */
    function handleAdminLegacyFormsListViewItems() {
        const adminLegacyFormsListViewItems = document.querySelectorAll(
            '.type-give_forms:not(.give-async-data-fetch-triggered)'
        );
        if (adminLegacyFormsListViewItems.length > 0) {
            adminLegacyFormsListViewItems.forEach((itemElement) => {
                if (!itemElement.hasAttribute('id') || !itemElement.id.includes('post-')) {
                    return;
                }

                const formId = itemElement.id.split('post-')[1];
                const goalElement = itemElement.querySelector('.column-goal');
                const amountRaisedElement = goalElement.querySelector('.give-goal-text > span');
                const progressBarElement = goalElement.querySelector('.give-admin-progress-bar > span');
                const goalAchievedElement = goalElement.querySelector('.give-admin-goal-achieved');
                const donationsElement = itemElement.querySelector('.column-donations > a');
                const earningsElement = itemElement.querySelector('.column-earnings > a');

                if (isInViewport(itemElement)) {
                    loadFormData(
                        formId,
                        itemElement,
                        amountRaisedElement,
                        progressBarElement,
                        goalAchievedElement,
                        donationsElement,
                        earningsElement
                    );
                }
            });
        }
    }

    /**
     * Load the async data in all form grid items that have the progress bar enabled.
     *
     * @since 3.16.0
     */
    function handleFormGridItems() {
        const formGridItems = document.querySelectorAll('.give-grid__item:not(.give-async-data-fetch-triggered)');

        if (formGridItems.length > 0) {
            formGridItems.forEach((itemElement) => {
                const giveCard = itemElement.querySelector('.give-card');

                if (!giveCard || !giveCard.hasAttribute('id') || !giveCard.id.includes('give-card-')) {
                    return;
                }

                const formId = giveCard.id.split('give-card-')[1];
                const formGridRaised = itemElement.querySelector('.form-grid-raised');

                if (!formGridRaised) {
                    return;
                }

                const amountRaisedElement = formGridRaised
                    .querySelector('div:nth-child(1)')
                    .querySelector('span:nth-child(1)');
                const progressBarElement = itemElement.querySelector('.give-progress-bar').querySelector('span');
                const donationsElement = formGridRaised
                    .querySelector('div:nth-child(2)')
                    .querySelector('span:nth-child(1)');

                if (isInViewport(itemElement)) {
                    loadFormData(formId, itemElement, amountRaisedElement, progressBarElement, null, donationsElement);
                }
            });
        }
    }

    // Trigger the async logic at the page's first load.
    maybeLoadAsyncData();

    // Trigger the async logic every time the user scrolls the mouse.
    window.addEventListener(
        'scroll',
        () => {
            maybeLoadAsyncData();
        },
        true
    );

    // Trigger the async logic every time the user resize the screen.
    window.addEventListener(
        'resize',
        () => {
            maybeLoadAsyncData();
        },
        true
    );

    // Trigger the async logic every time the user add a new Form Grid Block to the WordPress Block Editor - Gutenberg.
    window.onload = function () {
        const wpBlockEditorContent = document.querySelector('.wp-block-post-content');
        if (!!wpBlockEditorContent) {
            // create an Observer instance
            const resizeObserver = new ResizeObserver((entries) => {
                window.GiveDonationFormsAsyncData.scriptDebug &&
                    console.log('WP Block Editor height changed:', entries[0].target.clientHeight);
                maybeLoadAsyncData();
            });

            // start observing a DOM node
            resizeObserver.observe(wpBlockEditorContent);
        }
    };
});
