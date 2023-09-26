import {__} from "@wordpress/i18n";
import {compose} from "@wordpress/compose";
import withButtons from "./withButtons";
import withText from "./withText";
import withDefaults from "./withDefaults";

type Placement = 'top'|'top-start'|'top-end'|'bottom'|'bottom-start'|'bottom-end'|'right'|'right-start'|'right-end'|'left'|'left-start'|'left-end';

const designSteps = Object.values(compose(
    withText,
    withButtons,
    withDefaults,
)([
    {
        id: 'welcome',
        title: __('Choose your form design', 'give'),
        text: __('Select one that suits your taste and requirements for your cause.', 'give'),
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
]))

const schemaSteps = Object.values(compose(
    withText,
    withButtons,
    withDefaults,
)([
    {
        id: 'welcome',
        title: __('Welcome to the visual donation form builder!', 'give'),
        text: __('The following is a quick (less than a minute) tour of the visual donation form builder, to introduce the tools for creating engaging donation forms.', 'give'),
    },
    {
        id: 'canvas',
        attachTo: { element: '#form-blocks-canvas', on: 'right-start' as Placement },
        title: __('Canvas', 'give'),
        text: __('Add, reorder, and edit blocks and sections here to make up your form.', 'give'),
    },
    {
        id: 'section',
        attachTo: { element: '.block-editor-block-list__layout .wp-block > div', on: 'right-start' as Placement },
        title: __('Form Sections', 'give'),
        text: __('Forms are broken into sections, which contain blocks for fields or content.', 'give'),
        modalOverlayOpeningRadius: 5, // Match the border radius of the section block element
    },
    {
        id: 'addButton',
        attachTo: { element: '#AddBlockButtonContainer', on: 'bottom' as Placement },
        title: __('Sidebar Toggles', 'give'),
        text: __('These two buttons give you the ability to add and reorder sections and blocks to the canvas, with drag-and-drop ease.', 'give'),
    },
    {
        id: 'addBlock',
        attachTo: { element: '#sidebar-secondary', on: 'right-start' as Placement },
        title: __('Add section/block', 'give'),
        text: __('Drag and drop the block you need onto the canvas. Input fields that can only be inserted once are greyed out when in use.', 'give'),
        beforeShowPromise: function () {
            return new Promise<void>(function (resolve) {
                document.getElementById('AddBlockButtonContainer').querySelector('button').click();
                resolve();
            });
        },
    },
    {
        id: 'editingABlock',
        attachTo: { element: '#sidebar-primary', on: 'left-start' as Placement },
        title: __('Block Settings', 'give'),
        text: __('Select a block to edit the settings for that individual block on the Block tab of the editor. Settings will vary depending on the type of block selected.', 'give'),
        beforeShowPromise: function () {
            return new Promise<void>(function (resolve) {
                document.dispatchEvent(new CustomEvent('selectAmountBlock'));
                resolve();
            });
        },
    },
]))

export {
    designSteps,
    schemaSteps,
}
