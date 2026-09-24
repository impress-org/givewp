import {useState} from 'react';
import {__, sprintf} from '@wordpress/i18n';
import {createInterpolateElement} from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import RowAction from '@givewp/components/ListTable/RowAction';
import {CheckCircle} from '@givewp/components/AdminUI/Icons';
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
    campaignTitle: string;
    /** Called when the modal closes after a successful link or move, so the list can refresh. */
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
export default function LinkToCampaignRowAction({formId, formTitle, campaignId, campaignTitle, onLinked}: LinkToCampaignProps) {
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
                    campaignTitle={campaignTitle}
                    onLinked={onLinked}
                />
            )}
        </>
    );
}

/**
 * Attach a form to a campaign using the associate-forms route, which also moves a form that
 * already belongs to one. The modal shows the form and its current campaign, offers the
 * destination, and on success confirms the new home with a link to that campaign. A move only
 * offers campaigns that are not archived, and never the form's current campaign. The server
 * refuses to move a campaign's default form and explains why.
 *
 * @since TBD
 */
function LinkToCampaignModal({
    isOpen,
    handleClose,
    formId,
    formTitle,
    campaignId: currentCampaignId,
    campaignTitle: currentCampaignTitle,
    onLinked,
}: LinkToCampaignModalProps) {
    const isMove = currentCampaignId > 0;
    const [selected, setSelected] = useState<CampaignOption | null>(null);
    const [isSaving, setSaving] = useState(false);
    const [isDone, setDone] = useState(false);
    const [error, setError] = useState<string>('');
    const {loadOptions, mapOptionsForMenu} = useCampaignAsyncSelect(
        null,
        isMove ? ['active', 'draft'] : ['active', 'draft', 'archived']
    );
    const campaignId = selected?.value ?? 0;
    const title = isMove ? __('Move to campaign', 'give') : __('Link to campaign', 'give');
    const saveLabel = isMove ? __('Move form', 'give') : __('Link form', 'give');
    const savingLabel = isMove ? __('Moving…', 'give') : __('Linking…', 'give');
    const campaignUrl = `edit.php?post_type=give_forms&page=give-campaigns&id=${campaignId}&tab=overview&action=edit`;

    /*
     * The list refresh re-renders the rows and unmounts this modal, so it waits until the
     * modal closes. Otherwise the success view would vanish the moment it appeared.
     */
    const close = async () => {
        handleClose();

        if (isDone) {
            await onLinked();
        }
    };

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
            setDone(true);
        } catch (e) {
            setError(e?.message ?? __('Something went wrong. Please try again.', 'give'));
        } finally {
            setSaving(false);
        }
    };

    return (
        <ModalDialog isOpen={isOpen} showHeader={true} handleClose={close} title={title} wrapperClassName={styles.linkModal}>
            {isDone ? (
                <div className={styles.success} role="status">
                    <CheckCircle />
                    <p>
                        {createInterpolateElement(
                            sprintf(
                                /* translators: 1: form title, 2: campaign title */
                                __('<form>%1$s</form> now belongs to <campaign>%2$s</campaign>.', 'give'),
                                formTitle,
                                selected?.label ?? ''
                            ),
                            {
                                form: <strong><Interweave content={formTitle} /></strong>,
                                campaign: <strong><Interweave content={selected?.label ?? ''} /></strong>,
                            }
                        )}
                    </p>
                    <div className={styles.actions}>
                        <a className="button button-primary" href={campaignUrl}>
                            {__('Go to campaign', 'give')}
                        </a>
                        <button type="button" className="button button-secondary" onClick={close}>
                            {__('Done', 'give')}
                        </button>
                    </div>
                </div>
            ) : (
                <>
                    <dl className={styles.summary}>
                        <dt>{__('Form', 'give')}</dt>
                        <dd>
                            <Interweave content={formTitle} />
                        </dd>
                        <dt>{__('Current campaign', 'give')}</dt>
                        <dd>{isMove ? <Interweave content={currentCampaignTitle} /> : __('No campaign', 'give')}</dd>
                    </dl>
                    <label htmlFor={`givewp-link-campaign-${formId}`}>
                        {isMove ? __('Move to', 'give') : __('Campaign', 'give')}
                    </label>
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
            )}
        </ModalDialog>
    );
}
