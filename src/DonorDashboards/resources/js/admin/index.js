// Code to handle showing/hiding admin notices (based on dismissed status in localStorage)

document.addEventListener('DOMContentLoaded', () => {
    // Select dismissable notices
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
