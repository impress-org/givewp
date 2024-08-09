document.addEventListener('DOMContentLoaded', () => {
    const formGridItems = document.querySelectorAll(".form-grid-raised");
    if (formGridItems.length > 0) {
        formGridItems.forEach((item) => {
            const giveGoalTextElement = item.querySelector("div:nth-child(1)");
            if (!!giveGoalTextElement) {
                const formId = giveGoalTextElement.getAttribute("data-form-id");
                giveGoalTextElement.querySelector('span').innerHTML = formId;
            }
        });
    }
});
