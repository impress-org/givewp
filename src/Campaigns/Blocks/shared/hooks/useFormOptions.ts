import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import type { Post } from '@wordpress/core-data/src/entity-types';
import type { Form } from '../../CampaignForm/resources/types';
import useSWR from 'swr';
import { addQueryArgs } from '@wordpress/url';
import apiFetch from '@wordpress/api-fetch';

export interface FormOption extends Form {
    label: string;
    value: number;
    isLegacyTemplate: boolean;
}

/**
 * @since 4.3.0
 */
export default function useFormOptions(campaignId?: number): {
    formOptions: FormOption[];
    isResolving: boolean;
} {
    const { forms, isResolving } = useSelect((select) => {
        const query = { per_page: 100 };
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

    const { data, isLoading } = useSWR<{ items: { id: number }[] }>(
        campaignId
            ? addQueryArgs('/give-api/v2/admin/forms', {
                campaignId,
                status: 'publish',
            }) as string
            : null,
        (path) => apiFetch({ path })
    );

    const campaignFormIds = data?.items?.map((form) => form.id) ?? [];

    const filteredForms =
        campaignId && campaignFormIds.length > 0
            ? forms?.filter((form) => campaignFormIds.includes(form?.id))
            : forms;

    const formOptions: FormOption[] =
        filteredForms?.map(({ title, id, formTemplate, isLegacyForm, link, name }) => ({
            label: __(title?.rendered || name || 'Untitled Form', 'give'),
            value: id,
            isLegacyForm,
            isLegacyTemplate: isLegacyForm && formTemplate === 'legacy',
            link,
        })) ?? [];

    return {
        formOptions:
            isResolving || isLoading
                ? [{ label: __('Loading...', 'give'), value: 0 } as FormOption]
                : formOptions,
        isResolving: isResolving || isLoading,
    };
}
