document.addEventListener('DOMContentLoaded', () => {

    // Determine if an element is in the visible viewport
    function isInViewport(element) {
        const rect = element.getBoundingClientRect();
        const html = document.documentElement;
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || html.clientHeight) &&
            rect.right <= (window.innerWidth || html.clientWidth)
        );
    }

    const loadFormData = async (formId) => {
        const response = await fetch(ajaxurl + '?action=givewp_get_form_async_data_for_list_view&formId=' + formId);
        return await response.json()
    }

    const fetchFormData = (item) => {
        item.classList.add('list-view-async-data');

        const formId = item.id.split('post-')[1];
        const columnGoal = item.querySelector('.column-goal');
        const columnDonations = item.querySelector('.column-donations');
        const columnEarnings = item.querySelector('.column-earnings');

        fetch(ajaxurl + '?action=givewp_get_form_async_data_for_list_view&formId=' + formId).
        then(function(response){
            return response.json();
        })
        .then(function(response){
            console.log('response: ', response)

            if (response.success) {
                const giveGoalTextElement = columnGoal.querySelector(".give-goal-text");
                const progressBarElement = columnGoal.querySelector(".give-admin-progress-bar");
                if (!!giveGoalTextElement && !!progressBarElement){
                    progressBarElement.querySelector('span').style.width = response.data.percentComplete + '%';
                    giveGoalTextElement.querySelector('span').innerHTML = response.data.amountRaised;
                }
                columnDonations.querySelector('a').innerHTML = response.data.donationsCount;
                columnEarnings.querySelector('a').innerHTML = response.data.earnings;
            }
        });
    }

    const maybeLoadAsyncData = () => {
        const legacyAdminAllFormsListViewItems = document.querySelectorAll('.type-give_forms:not(.list-view-async-data)');
        console.log('legacyAdminAllFormsListViewItems.length: ', legacyAdminAllFormsListViewItems.length);
        if (legacyAdminAllFormsListViewItems.length > 0) {
            legacyAdminAllFormsListViewItems.forEach((item) => {

                if (!item.hasAttribute('id') || !item.id.includes('post-')) {
                    return;
                }

                //const formId = item.id.split('post-')[1];
                //const columnGoal = item.querySelector('.column-goal');
                //const columnDonations = item.querySelector('.column-donations');
                //const columnEarnings = item.querySelector('.column-earnings')

                if (isInViewport(item)) {
                    console.log('item: ', item);
                    fetchFormData(item);
                    //fetchFormData(formId, columnGoal, columnDonations, columnEarnings);
                }

                /*loadFormData(formId).then(function(response){
                    console.log('response: ', response)
                    if (response.success) {
                        const giveGoalTextElement = columnGoal.querySelector(".give-goal-text");
                        const progressBarElement = columnGoal.querySelector(".give-admin-progress-bar");
                        if (!!giveGoalTextElement && !!progressBarElement){
                            progressBarElement.querySelector('span').style.width = response.data.percentComplete + '%';
                            giveGoalTextElement.querySelector('span').innerHTML = response.data.amountRaised;
                        }

                        columnDonations.querySelector('a').innerHTML = response.data.donationsCount;
                        columnEarnings.querySelector('a').innerHTML = response.data.earnings;
                    }
                });*/



                //if (!!giveGoalTextElement && !!progressBarElement){

                    //const formId = giveGoalTextElement.getAttribute("data-form-id");
                    //console.log('formId: ', formId);

                    //progressBarElement.querySelector('span').style.width = '100%';
                    //giveGoalTextElement.querySelector('span').innerHTML = formId;

                    /*loadFormData(formId).then(function(response){
                        console.log('response: ', response)
                        if (response.success) {
                            //alert('before update');
                            progressBar.querySelector('span').style.width = response.data.percentComplete + '%';
                            giveGoalTextElement.querySelector('span').innerHTML = response.data.amountRaised;
                        }
                    });*/


                   /* fetch(ajaxurl + '?action=givewp_get_form_async_data_for_list_view&formId=' + formId).
                    then(function(response){
                        return response.json();
                    })
                    .then(function(response){
                        console.log('response: ', response)

                        if (response.success) {
                            //alert('before update');
                            progressBar.querySelector('span').style.width = response.data.percentComplete + '%';
                            giveGoalTextElement.querySelector('span').innerHTML = response.data.amountRaised;
                        }
                    });*/
                //}
            });
        }
    }

    maybeLoadAsyncData();

    // If scrolling near bottom of page, load more async data
    window.addEventListener('scroll', () => {
        if (
            window.scrollY + window.innerHeight >= document.body.offsetHeight - 1000
        ) {
            maybeLoadAsyncData();
        }
    });
});
