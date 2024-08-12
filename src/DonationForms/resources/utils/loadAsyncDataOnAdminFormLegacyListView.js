document.addEventListener('DOMContentLoaded', () => {

    let abortLoadAsyncData = false;

    function isInViewport(element) {
        const { top, bottom } = element.getBoundingClientRect();
        const vHeight = (window.innerHeight || document.documentElement.clientHeight);

        return (
            (top > 0 || bottom > 0) &&
            top < vHeight
        );
    }

    const fetchFormData = ( formId, listViewItemElement, donationsElement, earningsElement, amountRaisedElement = null, progressBarElement = null) => {

        listViewItemElement.classList.add('list-view-async-data-loaded');

        const controller = new AbortController();
        const signal = controller.signal;

        fetch(ajaxurl + '?action=givewp_get_form_async_data_for_list_view&formId=' + formId, { signal }).
        then(function(response){
            return response.json();
        })
        .then(function(response){
            console.log('response: ', response)

            if (response.success) {
                donationsElement.querySelector('a').innerHTML = response.data.donationsCount;
                earningsElement.querySelector('a').innerHTML = response.data.earnings;

                if (!!amountRaisedElement && !!progressBarElement){
                    amountRaisedElement.querySelector('span').innerHTML = response.data.amountRaised;
                    progressBarElement.querySelector('span').style.width = response.data.percentComplete + '%';
                }
            }
        }) .catch(error => {
            console.log('error: ', error);
        }).finally(() => {
            console.log('finally!');
        });

        addEventListener("beforeunload", (event) => {
            abortLoadAsyncData = true;
            controller.abort('Async request aborted due to exit page.');
        });
    }

    const maybeLoadAsyncData = () => {

        if (abortLoadAsyncData) {
            console.log('abortLoadAsyncData');
            return;
        }

        const legacyAdminAllFormsListViewItems = document.querySelectorAll('.type-give_forms:not(.list-view-async-data-loaded)');
        if (legacyAdminAllFormsListViewItems.length > 0) {
            legacyAdminAllFormsListViewItems.forEach((listViewItemElement) => {

                if (!listViewItemElement.hasAttribute('id') || !listViewItemElement.id.includes('post-')) {
                    return;
                }

                const formId = listViewItemElement.id.split('post-')[1];
                const donationsElement = listViewItemElement.querySelector('.column-donations');
                const earningsElement = listViewItemElement.querySelector('.column-earnings');

                const goalElement = listViewItemElement.querySelector('.column-goal');
                const amountRaisedElement = goalElement.querySelector(".give-goal-text");
                const progressBarElement = goalElement.querySelector(".give-admin-progress-bar");

                if (isInViewport(listViewItemElement)) {
                    console.log('item: ', listViewItemElement);
                    fetchFormData(formId, listViewItemElement, donationsElement, earningsElement, amountRaisedElement, progressBarElement);
                }
            });
        }
    }

    maybeLoadAsyncData();

    window.addEventListener('scroll', () => {
        if (
            window.scrollY + window.innerHeight >= document.body.offsetHeight - 1000
        ) {
            maybeLoadAsyncData();
        }
    });
});
