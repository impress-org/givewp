// Code to handle showing/hiding admin notices (based on dismissed status in localStorage)

document.addEventListener('DOMContentLoaded', () => {
    alert('loadProgressBarValuesOnScroll');

    const adminAllFormsListViewItems = document.querySelectorAll("[id^='giveDonationFormsProgressBar']");
    if (adminAllFormsListViewItems.length > 0) {
        alert('is new view');
        console.log('adminAllFormsListViewItems: ', adminAllFormsListViewItems);
        adminAllFormsListViewItems.forEach((item) => {
            const formId = item.getAttribute("data-form-id");
            item.querySelector('span').innerHTML = formId;
        });
        return;
    }

    const legacyAdminAllFormsListViewItems = document.querySelectorAll(".column-goal");
    if (legacyAdminAllFormsListViewItems.length > 0) {
        legacyAdminAllFormsListViewItems.forEach((item) => {
            const giveGoalTextElement = item.querySelector(".give-goal-text");
            if (!!giveGoalTextElement){
                const formId = giveGoalTextElement.getAttribute("data-form-id");
                giveGoalTextElement.querySelector('span').innerHTML = formId;
            }
        });

        return;
    }

    const formGridItems = document.querySelectorAll(".form-grid-raised");
    if (formGridItems.length > 0) {
        alert('is form grid view');
        console.log('formGridItems: ', formGridItems);

        return;
    }

    // Select dismissible notices
    const notices = document.querySelectorAll('div[data-give-dismissible]');

    // Apply for every dismissible GiveWP notice
    notices.forEach((notice) => {
        // Generate storage id for notice
        const storageId = `give-dismissed-${notice.dataset.giveDismissible}`;

        // Retrieve timestamp of notice dismissal, if it has already happened
        const storedItem = window.localStorage.getItem(storageId);

        // If notice has not yet been dismissed continue
        if (!storedItem) {
            // Show the notice, if it has not been dismissed
            notice.classList.remove('hidden');

            // On dismissal click, add a record to local storage to that it remains hidden in the future
            notice.addEventListener('click', (e) => {
                if (e.target.classList.contains('notice-dismiss')) {
                    window.localStorage.setItem(storageId, Date.now());
                }

                if (e.target.classList.contains('give-donor-dashboard-upgrade-notice__dismiss-link')) {
                    notice.classList.add('hidden');
                    window.localStorage.setItem(storageId, Date.now());
                }
            });
        }
    });
});
