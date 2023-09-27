import {__} from "@wordpress/i18n";
import Placement from "./types/placement";
import DesignWelcome from "./components/DesignWelcome";

export default [
    {
        id: 'welcome',
        title: __('Choose your form design', 'give'),
        text: __('Select one that suits your taste and requirements for your cause.', 'give'),
        component: <DesignWelcome />,
        beforeShowPromise: function () {
            return new Promise<void>(function (resolve) {
                // @ts-ignore
                window.onboardingResetDesign();
                resolve();
            });
        },
    },
    {
        id: 'edit-design',
        attachTo: { element: '#sidebar-primary', on: 'left-start' as Placement },
        title: __('Editing a form design', 'give'),
        text: __('This is where you can customize the appearance (i.e. colors and features) of your form based on the selected form design.', 'give'),
    },
    {
        id: 'edit-form',
        attachTo: { element: '#editor-state-toggle', on: 'bottom' as Placement },
        title: __('Edit form', 'give'),
        text: __('This is where you add and edit various blocks and sections to make up your form.', 'give'),
    },
]
