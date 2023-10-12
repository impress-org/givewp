import {__} from "@wordpress/i18n";
import Placement from "./types/placement";

export default [
    {
        id: 'design-edit-settings',
        attachTo: { element: '#sidebar-primary', on: 'left-start' as Placement },
        title: __('Editing a form design', 'give'),
        text: __('This is where you can customize the appearance (i.e. colors and features) of your form based on the selected form design.', 'give'),
    },
    {
        id: 'design-edit-form',
        attachTo: { element: '#editor-state-toggle', on: 'bottom-end' as Placement },
        title: __('Edit form', 'give'),
        text: __('This is where you add and edit various blocks and sections to make up your form.', 'give'),
    },
]
