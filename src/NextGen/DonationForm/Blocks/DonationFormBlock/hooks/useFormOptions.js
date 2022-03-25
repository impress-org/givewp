import {__} from '@wordpress/i18n';
import {useSelect} from '@wordpress/data';

/**
 * @since 1.0.0
 * @returns {{formOptions: (*|[{label: string, value: number}])}}
 */
export default function useFormOptions() {
    const forms = useSelect((select) => {
        return select('core').getEntityRecords('postType', 'give_forms');
    }, []);

    const formOptions = forms
        ? forms.map(({id, title, formTemplate}) => {
              return {
                  label: __(title.rendered, 'give'),
                  value: id,
              };
          })
        : [
              {
                  label: __('loading forms...', 'give'),
                  value: 0,
              },
          ];

    return {
        formOptions,
    };
}
