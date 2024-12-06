import {__} from '@wordpress/i18n';
import Placement from '@givewp/form-builder/components/onboarding/steps/types/placement';

const schemaSteps = [
    {
        id: 'schema-canvas',
        attachTo: {element: '#form-blocks-canvas', on: 'right-start' as Placement},
        title: __('Canvas', 'give'),
        text: __('Add, reorder, and edit blocks and sections here to make up your form.', 'give'),
    },
    {
        id: 'schema-section',
        attachTo: {element: '.block-editor-block-list__layout .wp-block > div', on: 'right-start' as Placement},
        title: __('Form Sections', 'give'),
        text: __('Forms are broken into sections, which contain blocks for fields or content.', 'give'),
        modalOverlayOpeningRadius: 5, // Match the border radius of the section block element
    },
    {
        id: 'schema-add-button',
        attachTo: {element: '#AddBlockButtonContainer', on: 'bottom-end' as Placement},
        title: __('Sidebar Toggles', 'give'),
        text: __(
            'These two buttons give you the ability to add and reorder sections and blocks to the canvas, with drag-and-drop ease.',
            'give'
        ),
    },
    {
        id: 'schema-add-block',
        attachTo: {element: '#sidebar-secondary', on: 'right-start' as Placement},
        title: __('Add section/block', 'give'),
        text: __(
            'Drag and drop the block you need onto the canvas. Input fields that can only be inserted once are greyed out when in use.',
            'give'
        ),
        beforeShowPromise: function () {
            return new Promise<void>(function (resolve) {
                const addBlockButton = document.getElementById('AddBlockButtonContainer').querySelector('button');
                if (!addBlockButton.classList.contains('is-pressed')) {
                    addBlockButton.click();
                }
                resolve();
            });
        },
    },
    {
        id: 'schema-edit-block',
        attachTo: {element: '#sidebar-primary', on: 'left-start' as Placement},
        title: __('Block Settings', 'give'),
        text: __(
            'Select a block to edit the settings for that individual block on the Block tab of the editor. Settings will vary depending on the type of block selected.',
            'give'
        ),
        beforeShowPromise: function () {
            return new Promise<void>(function (resolve) {
                document.dispatchEvent(new CustomEvent('selectAmountBlock'));
                resolve();
            });
        },
    },
];

const toolSteps = {
    id: 'schema-find-tour',
    attachTo: {element: '.givewp-block-editor-tools__tour', on: 'bottom-end' as Placement},
    title: '',
    highlightClass: 'givewp-block-editor-tools__item',
    text: __(
        'Want to view the guided tour later? Access this option in the three dots menu above at any time.',
        'give'
    ),
    beforeShowPromise: function () {
        return new Promise<void>(function (resolve) {
            document.dispatchEvent(new CustomEvent('openToolsMenu'));
            setTimeout(function () {
                resolve();
            }, 100);
        });
    },
};

const renderToolSteps = !!window.onboardingTourData.autoStartSchemaTour;

if (renderToolSteps) {
    schemaSteps.push(toolSteps);
}

export default schemaSteps;
