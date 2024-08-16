document.addEventListener('DOMContentLoaded', () => {
    let abortLoadAsyncData = false;

    const giveListTable = document.querySelector('.giveListTable');
    const giveListTableIsLoadingEvent = new Event('giveListTableIsLoading');

    function isInViewport(element) {
        const {top, bottom} = element.getBoundingClientRect();
        const vHeight = window.innerHeight || document.documentElement.clientHeight;

        return (top > 0 || bottom > 0) && top < vHeight;
    }

    function isPlaceholder(element) {
        return !!element && Boolean(element.querySelector('.js-give-async-data'));
    }

    const loadFormData = (
        formId,
        itemElement,
        amountRaisedElement = null,
        progressBarElement = null,
        goalAchievedElement = null,
        donationsElement = null,
        earningsElement = null
    ) => {
        if (
            !isPlaceholder(amountRaisedElement) &&
            !isPlaceholder(donationsElement) &&
            !isPlaceholder(earningsElement)
        ) {
            return;
        }

        console.log('item: ', itemElement);

        itemElement.classList.add('give-async-data-fetch-triggered');

        const controller = new AbortController();
        const signal = controller.signal;

        url =
            window.GiveDonationFormsAsyncData.ajaxUrl +
            '?action=givewp_get_form_async_data_for_list_view&formId=' +
            formId +
            '&nonce=' +
            window.GiveDonationFormsAsyncData.ajaxNonce;

        fetch(url, {signal})
            .then(function (response) {
                return response.json();
            })
            .then(function (response) {
                console.log('Response: ', response);

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
                itemElement.classList.remove('give-async-data-fetch-triggered');
                console.log('Error: ', error);
            })
            .finally(() => {
                console.log('Request finalized.');
            });

        addEventListener('beforeunload', (event) => {
            abortLoadAsyncData = true;
            controller.abort('Async request aborted due to exit page.');
        });

        if (giveListTable) {
            giveListTable.addEventListener('giveListTableIsLoading', (event) => {
                abortLoadAsyncData = true;
                controller.abort('Async request aborted due to table loading.');
            });
        }
    };

    const maybeLoadAsyncData = () => {
        if (abortLoadAsyncData) {
            console.log('abortLoadAsyncData');
            return;
        }

        handleAdminFormsListViewItems();
        handleAdminLegacyFormsListViewItems();
        handleFormGridItems();
    };

    function handleAdminFormsListViewItems() {
        const adminFormsListViewItems = document.querySelectorAll('tr:not(.give-async-data-fetch-triggered)');
        if (adminFormsListViewItems.length > 0) {
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
                    childList: true,
                    characterData: true,
                };

                // Pass in the target node, as well as the observer options
                observer.observe(giveListTable, config);
            }
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

    maybeLoadAsyncData();

    window.addEventListener('scroll', () => {
        maybeLoadAsyncData();
    });
});
