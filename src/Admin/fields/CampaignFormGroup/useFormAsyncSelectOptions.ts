import { useAsyncSelectOptions } from "@givewp/admin/hooks/useAsyncSelectOption";
import apiFetch from "@wordpress/api-fetch";
import { useEffect, useState } from "react";

/**
 * Without a campaign, only standalone forms are listed, since a form in a campaign would put the campaign back on save.
 *
 * @since TBD List standalone forms when there is no campaign.
 * @since TBD List published forms only.
 * @since 4.11.0
 */
export default function useFormAsyncSelectOptions(formId: number, campaignId: number, queryParams?: {}) {
    const [selectedForm, setSelectedForm] = useState<any>(null);

    useEffect(() => {
        const fetchForm = async () => {
            if (!formId) {
                return;
            }

            const form = await apiFetch<any>({
                path: '/give-api/v2/admin/forms?search=' + formId + '&return=model',
            });

            if (!form?.items.length) {
                setSelectedForm(null);
                return;
            }

            setSelectedForm(form.items[0]);
        };

        fetchForm();
    }, [formId]);

    return useAsyncSelectOptions({
        recordId: formId || null,
        selectedOptionRecord: selectedForm,
        endpoint: '/give-api/v2/admin/forms',
        recordsFormatter: (records) => records.items,
        optionFormatter: (record) => {
            return {
                value: record.id,
                label: record.title,
            };
        },
        queryParams: {
            sortColumn: 'title',
            sortDirection: 'asc',
            return: 'model',
            status: 'publish',
            ...(campaignId ? { campaignId } : { campaign: 'none' }),
            ...queryParams,
        },
    });
}
