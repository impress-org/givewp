import {useState} from 'react';
import {__} from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import RowAction from '@givewp/components/ListTable/RowAction';
import {AsyncPaginate} from 'react-select-async-paginate';
import {useCampaignAsyncSelect} from '@givewp/components/ListTable/CustomFilter/useAsyncCampaigns';
import {CampaignOption} from '@givewp/components/ListTable/CustomFilter/utils';
import {Interweave} from 'interweave';
import styles from './LinkToCampaignModal.module.scss';

type LinkToCampaignProps = {
    formId: number;
    formTitle: string;
    /** The campaign the form belongs to now, or 0 for a standalone form. */
    campaignId: number;
    onLinked: () => Promise<void>;
};

type LinkToCampaignModalProps = LinkToCampaignProps & {
    isOpen: boolean;
    handleClose: () => void;
};

/**
 * Row action that owns the modal state. It is a real component so the parent row-actions
 * function stays hook-free and the row count can change without breaking React's hook order.
 * A standalone form gets "Link to campaign"; a form that already has one gets "Move to campaign".
 *
 * @since TBD
 */
export default function LinkToCampaignRowAction({formId, formTitle, campaignId, onLinked}: LinkToCampaignProps) {
    const [isOpen, setOpen] = useState(false);

    return (
        <>
            <RowAction
                onClick={() => setOpen(true)}
                actionId={formId}
                displayText={campaignId ? __('Move to campaign', 'give') : __('Link to campaign', 'give')}
                hiddenText={formTitle}
            />
            {isOpen && (
                <LinkToCampaignModal
                    isOpen={isOpen}
                    handleClose={() => setOpen(false)}
                    formId={formId}
                    formTitle={formTitle}
                    campaignId={campaignId}
                    onLinked={async () => {
                        setOpen(false);
                        await onLinked();
                    }}
                />
            )}
        </>
    );
}

/**
 * Attach a form to a campaign using the associate-forms route, which also moves a form that
 * already belongs to one. A move only offers campaigns that are not archived, and never the
 * form's current campaign. The server refuses to move a campaign's default form and explains why.
 *
 * @since TBD
 */
function LinkToCampaignModal({isOpen, handleClose, formId, formTitle, campaignId: currentCampaignId, onLinked}: LinkToCampaignModalProps) {
    const isMove = currentCampaignId > 0;
    const [selected, setSelected] = useState<CampaignOption | null>(null);
    const [isSaving, setSaving] = useState(false);
    const [error, setError] = useState<string>('');
    const {loadOptions, mapOptionsForMenu} = useCampaignAsyncSelect(
        null,
        isMove ? ['active', 'draft'] : ['active', 'draft', 'archived']
    );
    const campaignId = selected?.value ?? 0;
    const saveLabel = isMove ? __('Move form', 'give') : __('Link form', 'give');
    const savingLabel = isMove ? __('Moving…', 'give') : __('Linking…', 'give');

    const loadOtherCampaigns = async (search: string) => {
        const result = await loadOptions(search);

        return {...result, options: result.options.filter((option) => option.value !== currentCampaignId)};
    };

    const handleSave = async () => {
        setSaving(true);
        setError('');

        try {
            await apiFetch({
                path: '/givewp/v3/associate-forms-with-campaign',
                method: 'POST',
                data: {campaignId, formIDs: [formId]},
            });
            await onLinked();
        } catch (e) {
            setError(e?.message ?? __('Something went wrong. Please try again.', 'give'));
        } finally {
            setSaving(false);
        }
    };

    return (
        <ModalDialog
            isOpen={isOpen}
            showHeader={true}
            handleClose={handleClose}
            title={isMove ? __('Move to campaign', 'give') : __('Link to campaign', 'give')}
            wrapperClassName={styles.linkModal}
        >
            <>
                <p>
                    <Interweave content={formTitle} />
                </p>
                <label htmlFor={`givewp-link-campaign-${formId}`}>{__('Campaign', 'give')}</label>
                <AsyncPaginate
                    inputId={`givewp-link-campaign-${formId}`}
                    placeholder={__('Search for a campaign…', 'give')}
                    loadOptions={loadOtherCampaigns}
                    mapOptionsForMenu={mapOptionsForMenu}
                    onChange={(option: CampaignOption | null) => setSelected(option)}
                    value={selected}
                    isSearchable
                    isClearable
                    debounceTimeout={600}
                    className={styles.select}
                    classNamePrefix="searchableSelect"
                />
                {error && <p className={styles.error}>{error}</p>}
                <div className={styles.actions}>
                    <button
                        type="button"
                        className="button button-primary"
                        disabled={!campaignId || isSaving}
                        onClick={handleSave}
                    >
                        {isSaving ? savingLabel : saveLabel}
                    </button>
                    <button type="button" className="button button-secondary" onClick={handleClose}>
                        {__('Cancel', 'give')}
                    </button>
                </div>
            </>
        </ModalDialog>
    );
}
