import {useMemo} from 'react';
import {__, sprintf} from '@wordpress/i18n';
import {createInterpolateElement} from '@wordpress/element';
import {Interweave} from 'interweave';
import ListTableApi from '@givewp/components/ListTable/api';
import {hasStatus} from '@givewp/components/ListTable/adapters/hasStatus';
import {idsOf} from '@givewp/components/ListTable/adapters/idsOf';
import type {ListTableItem, ListTableParameters, ListTableRowAction} from '@givewp/components/ListTable/ListTablePage';
import type {CampaignEntity} from '@givewp/campaigns/admin/components/types';
import {UpgradeModalContent} from './Migration';
import AssignCampaignModal from './AssignCampaignModal';
import BulkEditDonationFormsDialog from './BulkEditDonationFormsDialog';

const API = new ListTableApi(window.GiveDonationForms);

const isTrashView = (parameters: ListTableParameters) => hasStatus(parameters, 'trash');

const outsideTrash = (data, parameters: ListTableParameters) => !isTrashView(parameters);

/* Actions that report no records reply with nothing useful; count the form as changed. */
const changed = (items: ListTableItem[]) => ({errors: [], successes: items.map((item) => item.id)});

/**
 * The Donation Forms actions. "Edit" is inline ("Restore" in the trash view); the rest are in the
 * row menu. Trash and Delete also run on a selection. The campaign's default form cannot be trashed
 * or deleted.
 *
 * @since TBD Declare the actions as data for the list engine; Trash, Delete and Edit author or status
 *            serve both the row menu and the selection bar; errors show as notices.
 */
export function useDonationFormsRowActions(entity?: CampaignEntity, isCampaignDetailsPage = false): ListTableRowAction[] {
    return useMemo<ListTableRowAction[]>(
        () => [
            {
                id: 'edit',
                label: __('Edit', 'give'),
                isPrimary: true,
                isVisible: outsideTrash,
                href: (item) => String(item.edit),
            },
            {
                id: 'restore',
                label: __('Restore', 'give'),
                isPrimary: true,
                isVisible: (data, parameters) => isTrashView(parameters),
                run: (items) => API.fetchWithArgs('/restore', {ids: idsOf(items)}, 'POST'),
            },
            {
                id: 'trash',
                label: __('Trash', 'give'),
                isDestructive: true,
                supportsBulk: true,
                isVisible: (data, parameters) => !isTrashView(parameters) && Boolean(data?.trash),
                isEligible: (item) => !item.isDefaultCampaignForm,
                confirm: {
                    title: __('Trash donation forms', 'give'),
                    body: () => <p>{__('Are you sure you want to trash the following donation forms?', 'give')}</p>,
                    confirmLabel: __('Trash', 'give'),
                },
                run: (items) => API.fetchWithArgs('/trash', {ids: idsOf(items)}, 'DELETE'),
            },
            {
                id: 'delete',
                label: (items) =>
                    items.some((item) => item.status_raw === 'trash')
                        ? __('Delete permanently', 'give')
                        : __('Delete', 'give'),
                isDestructive: true,
                supportsBulk: true,
                isVisible: (data, parameters) => isTrashView(parameters) || !data?.trash,
                isEligible: (item) => !item.isDefaultCampaignForm,
                confirm: {
                    title: __('Delete donation forms', 'give'),
                    body: () => <p>{__('Really delete the following donation forms?', 'give')}</p>,
                    confirmLabel: __('Delete', 'give'),
                },
                run: (items) => API.fetchWithArgs('/delete', {ids: idsOf(items)}, 'DELETE'),
            },
            {
                id: 'view',
                label: __('View', 'give'),
                isVisible: outsideTrash,
                href: (item) => String(item.permalink),
            },
            {
                id: 'duplicate',
                label: __('Duplicate', 'give'),
                isVisible: outsideTrash,
                run: (items) => API.fetchWithArgs('/duplicate', {ids: idsOf(items)}, 'POST'),
            },
            {
                id: 'upgrade',
                label: __('Upgrade', 'give'),
                isVisible: outsideTrash,
                isEligible: (item) => !item.v3form,
                confirm: {
                    title: __('Upgrade', 'give'),
                    body: () => <UpgradeModalContent />,
                    confirmLabel: __('Confirm', 'give'),
                },
                run: async ([item]) => {
                    await API.fetchWithArgs(`/migrate/${item.id}`, {}, 'POST');

                    return changed([item]);
                },
            },
            {
                id: 'copy-shortcode',
                label: __('Copy shortcode', 'give'),
                isVisible: outsideTrash,
                run: async ([item]) => {
                    await navigator.clipboard.writeText(`[give_form id="${item.id}"]`);

                    return changed([item]);
                },
                successMessage: ([item]) =>
                    /* translators: %d: donation form id */
                    sprintf(__('The shortcode for Donation Form #%d has been copied to your clipboard!', 'give'), item.id),
            },
            {
                id: 'make-default',
                label: __('Make as default', 'give'),
                isVisible: (data, parameters) => !isTrashView(parameters) && isCampaignDetailsPage && Boolean(entity),
                isEligible: (item) => !item.isDefaultCampaignForm,
                confirm: {
                    title: __('Make as default', 'give'),
                    body: ([item]) => (
                        <p>
                            {createInterpolateElement(
                                __(
                                    'This will set <title_link/> as the default form for this campaign. Do you want to proceed?',
                                    'give'
                                ),
                                {title_link: <Interweave content={String(item.title)} />}
                            )}
                        </p>
                    ),
                    confirmLabel: __('Yes proceed', 'give'),
                },
                run: async ([item]) => {
                    // The entity takes a partial record, as the old row action passed it.
                    await entity.edit({defaultFormId: Number(item.id)} as Parameters<CampaignEntity['edit']>[0]);
                    await entity.save();

                    return changed([item]);
                },
            },
            {
                id: 'assign-campaign',
                label: ([item]) => (item?.campaignId ? __('Change campaign', 'give') : __('Assign campaign', 'give')),
                isVisible: outsideTrash,
                isEligible: (item) => item.campaignType !== 'p2p' && (Boolean(item.campaignId) || !isCampaignDetailsPage),
                Dialog: ({items: [item], onClose, refresh}) => (
                    <AssignCampaignModal
                        isOpen
                        handleClose={onClose}
                        onChanged={async () => {
                            await refresh();
                        }}
                        formId={Number(item.id)}
                        formTitle={String(item.name ?? item.title)}
                        campaignId={Number(item.campaignId ?? 0)}
                        campaignTitle={String(item.campaignTitle ?? '')}
                        isDefaultCampaignForm={Boolean(item.isDefaultCampaignForm)}
                    />
                ),
            },
            {
                id: 'edit-author-status',
                label: __('Edit author or status', 'give'),
                supportsBulk: true,
                isVisible: outsideTrash,
                Dialog: (props) => (
                    <BulkEditDonationFormsDialog
                        {...props}
                        authors={window.GiveDonationForms.authors}
                        onSave={(ids, {author, status}) =>
                            API.fetchWithArgs('/edit', {ids: ids.join(','), author, status}, 'UPDATE')
                        }
                    />
                ),
            },
        ],
        [entity, isCampaignDetailsPage]
    );
}
