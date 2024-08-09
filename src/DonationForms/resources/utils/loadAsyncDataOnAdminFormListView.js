document.addEventListener('DOMContentLoaded', () => {
    const adminAllFormsListViewItems = document.querySelectorAll("[id^='giveDonationFormsProgressBar']");
    if (adminAllFormsListViewItems.length > 0) {
        console.log('adminAllFormsListViewItems: ', adminAllFormsListViewItems);
        adminAllFormsListViewItems.forEach((item) => {
            const formId = item.getAttribute("data-form-id");
            item.querySelector('span').innerHTML = formId;
        });
    }
});
