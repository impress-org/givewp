document.addEventListener('DOMContentLoaded', () => {

    const loadFormData = async (formId) => {
        const response = await fetch(ajaxurl + '?action=givewp_get_form_async_data_for_list_view&formId=' + formId);
        return await response.json()
    }

    const fetchFormData = (formId, columnGoal, columnDonations, columnEarnings) => {
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

    const legacyAdminAllFormsListViewItems = document.querySelectorAll('.type-give_forms');
    if (legacyAdminAllFormsListViewItems.length > 0) {
        legacyAdminAllFormsListViewItems.forEach((item) => {

            if (!item.hasAttribute('id') || !item.id.includes('post-')) {
                return;
            }

            const formId = item.id.split('post-')[1];
            const columnGoal = item.querySelector('.column-goal');
            const columnDonations = item.querySelector('.column-donations');
            const columnEarnings = item.querySelector('.column-earnings');

            fetchFormData(formId, columnGoal, columnDonations, columnEarnings);

            console.log('formId: ', formId);

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
});
