/**
 * WordPress dependencies
 */
import {__} from '@wordpress/i18n';

/**
 * getSiteUrl from API root
 * @returns {string} siteurl
 */
export function getSiteUrl() {
    return wpApiSettings.root.replace('/wp-json/', '');
}

/**
 * Convert forms object in option
 *
 * @since 2.7.0
 *
 * @param {object} forms
 *
 * @return {[]}
 */
export function getFormOptions(forms) {
    let formOptions = [];

    if (forms) {
        formOptions = forms.map(({id, title: {rendered: title}}) => {
            return {
                value: id,
                label: title === '' ? `${id} : ${__('No form title', 'give')}` : title,
            };
        });
    }

    // Add Default option
    formOptions.unshift({value: '0', label: __('-- Select Form --', 'give')});

    return formOptions;
}

/**
 * Returns whether or not the given form uses the legacy form template.
 *
 * Note: if selected form has legacy form template or empty (old forms) then it will return true otherwise false.
 *
 * @since 2.30.0 Filter v3 forms out of the form list.
 * @since 2.7.0
 *
 * @param {object} forms
 * @param {number} SelectedFormId
 *
 * @return {boolean}
 */
export function isLegacyForm(forms, SelectedFormId) {
    if (forms) {
        const data = forms.find((form) => parseInt(form.id) === parseInt(SelectedFormId));

        return (
            data && data.excerpt.rendered !== '<p>[]</p>\n' && (!data.formTemplate || data.formTemplate === 'legacy')
        );
    }

    return false;
}

export function isTemplateForm(forms, SelectedFormId) {
    if (forms) {
        const data = forms.find((form) => parseInt(form.id) === parseInt(SelectedFormId));

        return data && data.formTemplate !== '';
    }

    return false;
}

export function isVisualBuilderForm(forms, SelectedFormId) {
    if (forms) {
        const data = forms.find((form) => parseInt(form.id) === parseInt(SelectedFormId));
        console.log(data);
        return data && data.formTemplate === '';
    }

    return false;
}
