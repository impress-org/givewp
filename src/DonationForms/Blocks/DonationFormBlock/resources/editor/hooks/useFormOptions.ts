import {__} from '@wordpress/i18n';
import {useSelect} from '@wordpress/data';
import type {Post} from '@wordpress/core-data/src/entity-types';
import type {Option} from '../types';

/**
 * @since 0.5.0 filter v3 forms by rendered excerpt.
 * @since 0.1.0
 */
export default function useFormOptions(): {formOptions: Option[] | []; isResolving: boolean} {
    const {forms, isResolving} = useSelect((select) => {
        return {
            forms: select('core')
                .getEntityRecords<Post[]>('postType', 'give_forms')
                ?.filter(({excerpt}) => excerpt.rendered === '<p>[]</p>\n'),
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
