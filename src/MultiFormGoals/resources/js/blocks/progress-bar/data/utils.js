const {useSelect} = wp.data;
import {__} from '@wordpress/i18n';

/**
 * Get array of form options for a select control
 *
 * @since 3.1.0 Use title.raw instead of title.rendered
 * @since 2.9.0
 * @return {array} Array of options for a select control
 */
export const useFormOptions = () => {
    const formOptions = useSelect((select) => {
        const records = select('core').getEntityRecords('postType', 'give_forms');
        if (records) {
            return records.map((record) => {
                return {
                    label: record.title.raw ? record.title.raw : __('(no title)'),
                    value: record.id,
                };
            });
        }
        return [];
    }, []);
    return formOptions;
};

/**
 * Get array of form tag options for a select control
 *
 * @since 2.9.0
 * @return {array} Array of options for a select control
 */
export const useTagOptions = () => {
    const tagOptions = useSelect((select) => {
        const records = select('core').getEntityRecords('taxonomy', 'give_forms_tag', {per_page: 100});
        if (records) {
            return records.map((record) => {
                return {
                    label: record.name ? record.name : __('(no title)'),
                    value: record.id,
                };
            });
        }
        return [];
    }, []);
    return tagOptions;
};

/**
 * Get array of form category options for a select control
 *
 * @since 2.9.0
 * @return {array} Array of options for a select control
 */
export const useCategoryOptions = () => {
    const categoryOptions = useSelect((select) => {
        const records = select('core').getEntityRecords('taxonomy', 'give_forms_category', {per_page: 100});
        if (records) {
            return records.map((record) => {
                return {
                    label: record.name ? record.name : __('(no title)'),
                    value: record.id,
                };
            });
        }
        return [];
    }, []);
    return categoryOptions;
};
