import {__, sprintf} from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import type {ListTableRowAction} from '@givewp/components/ListTable/ListTablePage';

/**
 * Campaign row actions: "Edit" inline, "Duplicate" in the row menu.
 *
 * @since TBD Declare the row actions as data for the list engine.
 */
export const campaignsRowActions: ListTableRowAction[] = [
    {
        id: 'edit',
        label: __('Edit', 'give'),
        isPrimary: true,
        href: (item) => `edit.php?post_type=give_forms&page=give-campaigns&id=${item.id}`,
    },
    {
        id: 'duplicate',
        label: __('Duplicate', 'give'),
        confirm: {
            title: __('Duplicate campaign', 'give'),
            body: ([item]) => (
                <p>
                    {/* translators: %s: campaign title */}
                    {sprintf(
                        __('Are you sure you want to duplicate the following campaign: %s?', 'give'),
                        item.titleRaw
                    )}
                </p>
            ),
            confirmLabel: __('Duplicate', 'give'),
        },
        run: async ([item]) => {
            await apiFetch({path: `/givewp/v3/campaigns/${item.id}/duplicate`, method: 'POST'});

            return {errors: [], successes: [item.id]};
        },
    },
];
