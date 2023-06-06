import {__} from "@wordpress/i18n";
import {compose} from "@wordpress/compose";
import withButtons from "./withButtons";
import withText from "./withText";
import withDefaults from "./withDefaults";

type Placement = 'top'|'top-start'|'top-end'|'bottom'|'bottom-start'|'bottom-end'|'right'|'right-start'|'right-end'|'left'|'left-start'|'left-end';

export default Object.values(compose(
    withText,
    withButtons,
    withDefaults({
        canClickTarget: false,
        scrollTo: false,
        cancelIcon: {
            enabled: false,
        },
        arrow: false,
    }),
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
        title: __('Quick Inserter', 'give'),
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
    {
        id: 'designTab',
        attachTo: { element: '.components-tab-panel__tabs button:last-of-type', on: 'left' as Placement },
        title: __('Design Mode', 'give'),
        text: __('Select the design tab to switch the canvas into a preview of what the form will look like, and access various settings for the visual aspect of the form.', 'give'),
        beforeShowPromise: function () {
            return new Promise<void>(function (resolve) {
                // @ts-ignore
                document.querySelector('.components-tab-panel__tabs button:last-of-type').click();
                resolve();
            });
        },
    },
    {
        id: 'formTemplate',
        attachTo: { element: '.components-panel__row', on: 'left-start' as Placement },
        title: __('Form Design', 'give'),
        text: __('Select the design of the form based on what you need. More form designs are coming soon!', 'give'),
    },
    {
        id: 'formDesign',
        attachTo: { element: 'iframe', on: 'right-start' as Placement },
        title: __('Live Preview', 'give'),
        text: __('As you make changes or select a different design, those changes happen live on the canvas.', 'give'),
    },
    {
        id: 'editingAFormDesign',
        attachTo: { element: '.givewp-next-gen-sidebar-primary', on: 'left-start' as Placement },
        title: __('Design Settings', 'give'),
        text: __('Individual form designs have various settings and options to allow you to customize appearance via features like goal progress bars, headings, and custom CSS.', 'give'),
    },
    {
        id: 'congrats',
        title: __('You\'re Ready to Build!', 'give'),
        text: __('The visual donation form builder lets you build and customize forms to more easily raise money online. If you need it, access this tour again with the three-dot menu in the top right of the editor screen. Happy fundraising!', 'give'),
        beforeShowPromise: function () {
            return new Promise<void>(function (resolve) {
                // @ts-ignore
                document.querySelector('.components-tab-panel__tabs button:first-of-type').click();
                document.getElementById('AddBlockButtonContainer').querySelector('button').click();
                resolve();
            });
        },
    },
]))
