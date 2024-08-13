document.addEventListener('DOMContentLoaded', () => {

    let abortLoadAsyncData = false;

    const giveListTable = document.querySelector('.giveListTable');
    const giveListTableIsLoadingEvent = new Event("giveListTableIsLoading");

    function isInViewport(element) {
        const {top, bottom} = element.getBoundingClientRect();
        const vHeight = (window.innerHeight || document.documentElement.clientHeight);

        return (
            (top > 0 || bottom > 0) &&
            top < vHeight
        );
    }

    const loadFormData = (formId, itemElement, amountRaisedElement = null, progressBarElement = null, donationsElement = null, earningsElement = null) => {

        if (!amountRaisedElement && !progressBarElement && !donationsElement && !earningsElement) {
            return;
        }

        itemElement.classList.add('give-async-data-fetch-triggered');

        const controller = new AbortController();
        const signal = controller.signal;

        let url = '';
        if (typeof give_global_vars !== 'undefined') {
            url = give_global_vars.ajax_vars.ajaxurl + '?action=givewp_get_form_async_data_for_list_view&formId=' + formId + '&nonce=' + give_global_vars.ajax_vars.ajaxNonce;
        } else {
            url = ajaxurl + '?action=givewp_get_form_async_data_for_list_view&formId=' + formId + '&nonce=' + ajaxNonce;
        }

        fetch(url, {signal}).then(function (response) {
            return response.json();
        }).then(function (response) {
            console.log('Response: ', response);

            if (response.success) {
                if (!!amountRaisedElement && !!progressBarElement) {
                    amountRaisedElement.innerHTML = response.data.amountRaised;
                    progressBarElement.style.width = response.data.percentComplete + '%';
                }

                if (!!donationsElement) {
                    donationsElement.innerHTML = response.data.donationsCount;
                }

                if (!!earningsElement) {
                    earningsElement.innerHTML = response.data.earnings;
                }
            }
        }).catch(error => {
            itemElement.classList.remove('give-async-data-fetch-triggered');
            console.log('Error: ', error);
        }).finally(() => {
            console.log('Request finalized.');
        });

        addEventListener("beforeunload", (event) => {
            abortLoadAsyncData = true;
            controller.abort('Async request aborted due to exit page.');
        });

        if (giveListTable) {
            giveListTable.addEventListener("giveListTableIsLoading", (event) => {
                //alert('giveListTableIsLoading');
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

                const formId = select.getAttribute("data-id");
                const amountRaisedElement = itemElement.querySelector("[id^='giveDonationFormsProgressBar'] > span");
                const progressBarElement = itemElement.querySelector(".goalProgress > span");
                const donationsElement = itemElement.querySelector('.column-donations');
                const earningsElement = itemElement.querySelector('.column-earnings');

                if (isInViewport(itemElement)) {
                    console.log('item: ', itemElement);
                    loadFormData(formId, itemElement, amountRaisedElement, progressBarElement, donationsElement, earningsElement);
                }
            });
        }
    }

    function handleAdminLegacyFormsListViewItems() {
        const adminLegacyFormsListViewItems = document.querySelectorAll('.type-give_forms:not(.give-async-data-fetch-triggered)');
        if (adminLegacyFormsListViewItems.length > 0) {
            adminLegacyFormsListViewItems.forEach((itemElement) => {

                if (!itemElement.hasAttribute('id') || !itemElement.id.includes('post-')) {
                    return;
                }

                const formId = itemElement.id.split('post-')[1];
                const goalElement = itemElement.querySelector('.column-goal');
                const amountRaisedElement = goalElement.querySelector(".give-goal-text").querySelector('span');
                const progressBarElement = goalElement.querySelector(".give-admin-progress-bar").querySelector('span');
                const donationsElement = itemElement.querySelector('.column-donations').querySelector('a');
                const earningsElement = itemElement.querySelector('.column-earnings').querySelector('a');

                if (isInViewport(itemElement)) {
                    console.log('item: ', itemElement);
                    loadFormData(formId, itemElement, amountRaisedElement, progressBarElement, donationsElement, earningsElement);
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

                const amountRaisedElement = formGridRaised.querySelector("div:nth-child(1)").querySelector('span:nth-child(1)');
                const progressBarElement = itemElement.querySelector(".give-progress-bar").querySelector('span');
                const donationsElement = formGridRaised.querySelector("div:nth-child(2)").querySelector('span:nth-child(1)');

                if (isInViewport(itemElement)) {
                    console.log('item: ', itemElement);
                    loadFormData(formId, itemElement, amountRaisedElement, progressBarElement, donationsElement);
                }
            });
        }
    }

    maybeLoadAsyncData();

    // If scrolling near bottom of page, load more async data
    window.addEventListener('scroll', () => {
        //if (
        //  window.scrollY + window.innerHeight >= document.body.offsetHeight - 100
        //) {
        maybeLoadAsyncData();
        //}
    });
});
