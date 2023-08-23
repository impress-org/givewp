import {__} from '@wordpress/i18n';
import {useSelect} from '@wordpress/data';
import type {Post} from '@wordpress/core-data/src/entity-types';
import type {Option} from '../types';

/**
 * @since 3.0.0
 */
export default function useFormOptions(): {formOptions: Option[] | []; isResolving: boolean} {
    const {forms, isResolving} = useSelect((select) => {
        return {
            forms: select('core')
                // @ts-ignore
                .getEntityRecords<Post[]>('postType', 'give_forms')
                ?.filter(({excerpt}) => excerpt.rendered === '<p>[]</p>\n'),
            // @ts-ignore
            isResolving: select('core/data').getIsResolving('core', 'getEntityRecords', ['postType', 'give_forms']),
        };
    }, []);

    const formOptions =
        forms && forms.length > 0
            ? forms.map(({id, title}) => {
                  return {
                      label: __(title.rendered, 'give'),
                      value: String(id),
                  };
              })
            : [];

    return {
        isResolving,
        formOptions,
    };
}
