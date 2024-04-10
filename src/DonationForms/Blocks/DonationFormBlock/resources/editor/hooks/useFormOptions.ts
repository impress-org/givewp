import {__} from '@wordpress/i18n';
import {useSelect} from '@wordpress/data';
import type {Post} from '@wordpress/core-data/src/entity-types';
import type {Form} from '../types';

/**
 * @since 3.2.1
 */
export interface FormOption extends Form {
    label: string;
    value: number;
}

/**
 * @since 3.7.0 Increase the per_page attribute to 100 to accommodate for more forms.
 * @since 3.2.0 include isLegacyForm, isLegacyFormTemplate & link.
 * @since 3.0.0
 */
export default function useFormOptions(): {
    formOptions: FormOption[];
    isResolving: boolean;
} {
    const formOptions = [];

    const {forms, isResolving} = useSelect((select) => {
        const query = {per_page: 100};
        return {
            // @ts-ignore
            forms: select('core').getEntityRecords<Post[]>('postType', 'give_forms', query),
            // @ts-ignore
            isResolving: select('core/data').getIsResolving('core', 'getEntityRecords', [
                'postType',
                'give_forms',
                query,
            ]),
        };
    }, []);

    forms?.map(({title, id, formTemplate, isLegacyForm, link}) => {
        formOptions.push({
            label: __(title.rendered, 'give'),
            value: id,
            isLegacyForm,
            isLegacyTemplate: isLegacyForm && formTemplate === 'legacy',
            link: link,
        });
    });

    return {
        isResolving,
        formOptions,
    };
}
