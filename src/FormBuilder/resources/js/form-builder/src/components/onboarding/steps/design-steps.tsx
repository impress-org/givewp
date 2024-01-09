import {__} from '@wordpress/i18n';
import Placement from './types/placement';

export default [
    {
        id: 'design-edit-settings',
        attachTo: {element: '#sidebar-primary', on: 'left-start' as Placement},
        title: __('Editing a form layout', 'give'),
        text: __(
            'This is where you can customize the appearance (i.e. colors and features) of your form based on the selected form layout.',
            'give'
        ),
    },
    {
        id: 'design-edit-form',
        attachTo: {element: '#editor-state-switch-schema', on: 'bottom-center' as Placement},
        title: __('Build', 'give'),
        text: __('This is where you add and edit various blocks and sections to make up your form.', 'give'),
    },
];
