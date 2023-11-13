import {__} from '@wordpress/i18n';
import {useSelect} from '@wordpress/data';
import type {Post} from '@wordpress/core-data/src/entity-types';
import type {Form, Option} from '../types';

type FormOption = Form & Option;

/**
 * unreleased include isLegacyForm, isLegacyFormTemplate & link.
 * @since 3.0.0
 */
export default function useFormOptions(): {
    formOptions: FormOption[];
    isResolving: boolean;
} {
    const formOptions = [];

    const {forms, isResolving} = useSelect((select) => {
        return {
            // @ts-ignore
            forms: select('core').getEntityRecords<Post[]>('postType', 'give_forms'),
            // @ts-ignore
            isResolving: select('core/data').getIsResolving('core', 'getEntityRecords', ['postType', 'give_forms']),
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
