/**
 * This file contains all the logic to load async data on the project's available campaign list views.
 *
 * The async data are loaded (only for the items visible on the screen) on the following conditions:
 *
 * 1) At the page's first load
 * 2) When the user scrolls the mouse
 * 3) When the user resizes the screen
 *
 * @unreleased
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
     * @unreleased
     */
    function isInViewport(element) {
        const {top, bottom} = element.getBoundingClientRect();
        const vHeight = window.innerHeight || document.documentElement.clientHeight;

        return (top > 0 || bottom > 0) && top < vHeight;
    }

    /**
     * Check if an element is a placeholder waiting to have the value updated.
     *
     * @unreleased
     */
    function isPlaceholder(element) {
        return !!element && Boolean(element.querySelector('.js-give-async-data'));
    }

    /**
     * This function fetch the async data from the server and set the values to the proper elements in the DOM.
     *
     * @unreleased
     */
    const loadCampaignData = (
        campaignId,
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
        if (window.GiveCampaignsAsyncData.throttlingEnabled && throttleTimer) {
            window.GiveCampaignsAsyncData.scriptDebug && console.log('throttleTimer start: ', throttleTimer);
            return;
        }

        throttleTimer = true;
        window.GiveCampaignsAsyncData.scriptDebug && console.log('request start: ', new Date().toLocaleTimeString());

        window.GiveCampaignsAsyncData.scriptDebug && console.log('item: ', itemElement);

        // This class ensures that once the element has the fetch request triggered we'll not try to fetch it again.
        itemElement.classList.add('give-async-data-fetch-triggered');

        // It can be used to abort the async request when necessary.
        const controller = new AbortController();
        const signal = controller.signal;

        fetch(
            `${window.GiveCampaignsAsyncData.ajaxUrl}?action=givewp_get_campaign_async_data_for_list_view&campaignId=${campaignId}&nonce=${window.GiveCampaignsAsyncData.ajaxNonce}`,
            {signal}
        )
            .then(function (response) {
                return response.json();
            })
            .then(function (response) {
                window.GiveCampaignsAsyncData.scriptDebug && console.log('Response: ', response);

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
                window.GiveCampaignsAsyncData.scriptDebug && console.log('Error: ', error);
            })
            .finally(() => {
                window.GiveCampaignsAsyncData.scriptDebug &&
                    console.log('request end: ', new Date().toLocaleTimeString());
                if (window.GiveCampaignsAsyncData.throttlingEnabled && throttleTimer) {
                    throttleTimer = false;
                    window.GiveCampaignsAsyncData.scriptDebug && console.log('throttleTimer end: ', throttleTimer);
                    maybeLoadAsyncData();
                }
                window.GiveCampaignsAsyncData.scriptDebug && console.log('Request finalized.');
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
     * Handle the async data logic for ALL campaign list views available.
     *
     * @unreleased
     */
    const maybeLoadAsyncData = () => {
        // If the async requests were aborted on the "beforeunload" or "giveListTableIsLoading" event, we don't want to create more async requests
        if (abortLoadAsyncData) {
            window.GiveCampaignsAsyncData.scriptDebug && console.log('abortLoadAsyncData');
            return;
        }

        handleAdminCampaignsListViewItems();
    };

    /**
     * Check for changes in the "giveListTable" classes to trigger the "giveListTableIsLoadingEvent" when appropriated.
     *
     * @unreleased
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
     * Load the async data of all campaigns (visible on the screen) from the admin campaign list view - giveListTable.
     *
     * @unreleased
     */
    function handleAdminCampaignsListViewItems() {
        const adminCampaignsListViewItems = document.querySelectorAll('tr:not(.give-async-data-fetch-triggered)');
        if (adminCampaignsListViewItems.length > 0) {
            maybeTriggerGiveListTableIsLoadingEvent();

            adminCampaignsListViewItems.forEach((itemElement) => {
                const select = itemElement.querySelector('.giveListTableSelect');

                if (!select) {
                    return;
                }

                const campaignId = select.getAttribute('data-id');
                const amountRaisedElement = itemElement.querySelector("[id^='giveCampaignsProgressBar'] > span");
                const progressBarElement = itemElement.querySelector('.goalProgress > span');
                const goalAchievedElement = itemElement.querySelector('.goalProgress--achieved');
                const donationsElement = itemElement.querySelector('.column-donations-count-value');
                const earningsElement = itemElement.querySelector('.column-earnings-value');

                if (isInViewport(itemElement)) {
                    loadCampaignData(
                        campaignId,
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

    // Trigger the async logic every time the Campaigns page gets updated
    window.onload = function () {
        const campaignsPage = document.querySelector('#give-admin-campaigns-root');
        if (!!campaignsPage) {
            // create an Observer instance
            const resizeObserver = new ResizeObserver((entries) => {
                const queryString = window.location.search;
                const params = new URLSearchParams(queryString);
                const isCampaignsPage = !params.has('id');
                if (isCampaignsPage) {
                    window.GiveCampaignsAsyncData.scriptDebug &&
                        console.log('Campaigns Page height changed:', entries[0].target.clientHeight);
                    maybeLoadAsyncData();
                }
            });

            // start observing a DOM node
            resizeObserver.observe(campaignsPage);
        }
    };
});
