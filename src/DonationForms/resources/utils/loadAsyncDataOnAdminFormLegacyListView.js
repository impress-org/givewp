document.addEventListener('DOMContentLoaded', () => {

    const loadFormData = async (formId) => {
        const response = await fetch(ajaxurl + '?action=givewp_get_form_async_data_for_list_view&formId=' + formId);
        return await response.json()
    }

    const legacyAdminAllFormsListViewItems = document.querySelectorAll(".column-goal");
    if (legacyAdminAllFormsListViewItems.length > 0) {
        legacyAdminAllFormsListViewItems.forEach((item) => {
            const giveGoalTextElement = item.querySelector(".give-goal-text");
            const progressBar = item.querySelector(".give-admin-progress-bar");
            if (!!giveGoalTextElement && !!progressBar){
                const formId = giveGoalTextElement.getAttribute("data-form-id");
                /*getFormData(formId).then(response => {

                });*/
                progressBar.querySelector('span').style.width = '100%';
                giveGoalTextElement.querySelector('span').innerHTML = formId;

                loadFormData(formId).then(function(response){
                    console.log('response: ', response)
                    if (response.success) {
                        //alert('before update');
                        progressBar.querySelector('span').style.width = response.data.percentComplete + '%';
                        giveGoalTextElement.querySelector('span').innerHTML = response.data.amountRaised;
                    }
                });


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
            }
        });
    }

    /*async function loadData(formId){

        const response = await getData(formId);
    }*/


});
