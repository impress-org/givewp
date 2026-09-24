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
    onLinked: () => Promise<void>;
};

type LinkToCampaignModalProps = LinkToCampaignProps & {
    isOpen: boolean;
    handleClose: () => void;
};

/**
 * Row action that owns the modal state. It is a real component so the parent row-actions
 * function stays hook-free and the row count can change without breaking React's hook order.
 *
 * @since TBD
 */
export default function LinkToCampaignRowAction({formId, formTitle, onLinked}: LinkToCampaignProps) {
    const [isOpen, setOpen] = useState(false);

    return (
        <>
            <RowAction
                onClick={() => setOpen(true)}
                actionId={formId}
                displayText={__('Link to campaign', 'give')}
                hiddenText={formTitle}
            />
            {isOpen && (
                <LinkToCampaignModal
                    isOpen={isOpen}
                    handleClose={() => setOpen(false)}
                    formId={formId}
                    formTitle={formTitle}
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
 * Attach a standalone form to a campaign using the existing associate-forms route.
 *
 * @since TBD
 */
function LinkToCampaignModal({isOpen, handleClose, formId, formTitle, onLinked}: LinkToCampaignModalProps) {
    const [selected, setSelected] = useState<CampaignOption | null>(null);
    const [isSaving, setSaving] = useState(false);
    const [error, setError] = useState<string>('');
    const {loadOptions, mapOptionsForMenu} = useCampaignAsyncSelect(null);
    const campaignId = selected?.value ?? 0;

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
            title={__('Link to campaign', 'give')}
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
                    loadOptions={loadOptions}
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
                        {isSaving ? __('Linking…', 'give') : __('Link form', 'give')}
                    </button>
                    <button type="button" className="button button-secondary" onClick={handleClose}>
                        {__('Cancel', 'give')}
                    </button>
                </div>
            </>
        </ModalDialog>
    );
}
