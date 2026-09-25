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
import selectStyles from '@givewp/components/ListTable/CustomFilter/styles.module.scss';
import styles from './AssignCampaignModal.module.scss';

type AssignCampaignProps = {
    formId: number;
    formTitle: string;
    /** The campaign the form belongs to now, or 0 for a standalone form. */
    campaignId: number;
    campaignTitle: string;
    /** The campaign's default form cannot be moved or removed. */
    isDefaultCampaignForm: boolean;
    /** Called when the modal closes after a change, so the list can refresh. */
    onChanged: () => Promise<void>;
};

type AssignCampaignModalProps = AssignCampaignProps & {
    isOpen: boolean;
    handleClose: () => void;
};

type Step = 'pick' | 'confirmRemove' | 'moved' | 'removed';

/**
 * Row action that owns the modal state. It is a real component so the parent row-actions
 * function stays hook-free and the row count can change without breaking React's hook order.
 * A standalone form gets "Assign campaign"; a form that already has one gets "Change campaign".
 *
 * @since TBD
 */
export default function AssignCampaignRowAction(props: AssignCampaignProps) {
    const [isOpen, setOpen] = useState(false);

    return (
        <>
            <RowAction
                onClick={() => setOpen(true)}
                actionId={props.formId}
                displayText={props.campaignId ? __('Change campaign', 'give') : __('Assign campaign', 'give')}
                hiddenText={props.formTitle}
            />
            {isOpen && <AssignCampaignModal {...props} isOpen={isOpen} handleClose={() => setOpen(false)} />}
        </>
    );
}

/**
 * One modal for everything about a form's campaign: assign a standalone form, move a linked form
 * to another campaign, or remove it from its campaign. The picker only offers campaigns that are
 * not archived and never the current one. The server refuses to move or remove a campaign's
 * default form and explains why.
 *
 * @since TBD
 */
function AssignCampaignModal({
    isOpen,
    handleClose,
    formId,
    formTitle,
    campaignId: currentCampaignId,
    campaignTitle: currentCampaignTitle,
    isDefaultCampaignForm,
    onChanged,
}: AssignCampaignModalProps) {
    const hasCampaign = currentCampaignId > 0;
    const [step, setStep] = useState<Step>('pick');
    const [selected, setSelected] = useState<CampaignOption | null>(null);
    const [isSaving, setSaving] = useState(false);
    const [error, setError] = useState<string>('');
    const {loadOptions, mapOptionsForMenu} = useCampaignAsyncSelect(null, ['active', 'draft']);
    const campaignId = selected?.value ?? 0;
    const title = hasCampaign ? __('Change campaign', 'give') : __('Assign campaign', 'give');
    const campaignUrl = `edit.php?post_type=give_forms&page=give-campaigns&id=${campaignId}&tab=overview&action=edit`;

    /*
     * The list refresh re-renders the rows and unmounts this modal, so it waits until the
     * modal closes. Otherwise the success view would vanish the moment it appeared.
     */
    const close = async () => {
        handleClose();

        if (step === 'moved' || step === 'removed') {
            await onChanged();
        }
    };

    const loadOtherCampaigns = async (search: string) => {
        const result = await loadOptions(search);

        return {...result, options: result.options.filter((option) => option.value !== currentCampaignId)};
    };

    const request = async (path: string, data: object, nextStep: Step) => {
        setSaving(true);
        setError('');

        try {
            await apiFetch({path, method: 'POST', data});
            setStep(nextStep);
        } catch (e) {
            setError(e?.message ?? __('Something went wrong. Please try again.', 'give'));
        } finally {
            setSaving(false);
        }
    };

    const assign = () =>
        request('/givewp/v3/associate-forms-with-campaign', {campaignId, formIDs: [formId]}, 'moved');

    const remove = () => request('/givewp/v3/detach-forms-from-campaign', {formIDs: [formId]}, 'removed');

    const strong = (content: string) => (
        <strong>
            <Interweave content={content} />
        </strong>
    );

    return (
        <ModalDialog isOpen={isOpen} showHeader={true} handleClose={close} title={title} wrapperClassName={styles.modal}>
            {(step === 'moved' || step === 'removed') && (
                <div className={styles.success} role="status">
                    <CheckCircle />
                    <p>
                        {step === 'moved'
                            ? createInterpolateElement(
                                  /* translators: 1: form title, 2: campaign title */
                                  sprintf(__('<form>%1$s</form> now belongs to <campaign>%2$s</campaign>.', 'give'), formTitle, selected?.label ?? ''),
                                  {form: strong(formTitle), campaign: strong(selected?.label ?? '')}
                              )
                            : createInterpolateElement(
                                  /* translators: 1: form title, 2: campaign title */
                                  sprintf(__('<form>%1$s</form> is no longer part of <campaign>%2$s</campaign>.', 'give'), formTitle, currentCampaignTitle),
                                  {form: strong(formTitle), campaign: strong(currentCampaignTitle)}
                              )}
                    </p>
                    <div className={styles.actions}>
                        {step === 'moved' && (
                            <a className="button button-primary" href={campaignUrl}>
                                {__('Go to campaign', 'give')}
                            </a>
                        )}
                        <button type="button" className="button button-secondary" onClick={close}>
                            {__('Done', 'give')}
                        </button>
                    </div>
                </div>
            )}

            {step === 'confirmRemove' && (
                <>
                    <p>
                        {createInterpolateElement(
                            /* translators: 1: form title, 2: campaign title */
                            sprintf(
                                __('Remove <form>%1$s</form> from <campaign>%2$s</campaign>? The form keeps working on its own, and its past donations stay with the campaign.', 'give'),
                                formTitle,
                                currentCampaignTitle
                            ),
                            {form: strong(formTitle), campaign: strong(currentCampaignTitle)}
                        )}
                    </p>
                    {error && <p className={styles.error}>{error}</p>}
                    <div className={styles.actions}>
                        <button type="button" className={`button ${styles.danger}`} disabled={isSaving} onClick={remove}>
                            {isSaving ? __('Removing…', 'give') : __('Remove from campaign', 'give')}
                        </button>
                        <button type="button" className="button button-secondary" onClick={() => setStep('pick')}>
                            {__('Back', 'give')}
                        </button>
                    </div>
                </>
            )}

            {step === 'pick' && (
                <>
                    <dl className={styles.summary}>
                        <dt>{__('Form', 'give')}</dt>
                        <dd>
                            <Interweave content={formTitle} />
                        </dd>
                        <dt>{__('Current campaign', 'give')}</dt>
                        <dd>{hasCampaign ? <Interweave content={currentCampaignTitle} /> : __('No campaign', 'give')}</dd>
                    </dl>
                    <label htmlFor={`givewp-assign-campaign-${formId}`}>
                        {hasCampaign ? __('Move to', 'give') : __('Campaign', 'give')}
                    </label>
                    <AsyncPaginate
                        inputId={`givewp-assign-campaign-${formId}`}
                        placeholder={__('Search for a campaign…', 'give')}
                        loadOptions={loadOtherCampaigns}
                        mapOptionsForMenu={mapOptionsForMenu}
                        onChange={(option: CampaignOption | null) => setSelected(option)}
                        value={selected}
                        isSearchable
                        isClearable
                        debounceTimeout={600}
                        className={`${selectStyles.searchableSelect} ${styles.select}`}
                        classNamePrefix="searchableSelect"
                    />
                    {error && <p className={styles.error}>{error}</p>}
                    <div className={styles.actions}>
                        <button
                            type="button"
                            className="button button-primary"
                            disabled={!campaignId || isSaving}
                            onClick={assign}
                        >
                            {isSaving
                                ? hasCampaign
                                    ? __('Moving…', 'give')
                                    : __('Assigning…', 'give')
                                : hasCampaign
                                ? __('Move form', 'give')
                                : __('Assign campaign', 'give')}
                        </button>
                        <button type="button" className="button button-secondary" onClick={close}>
                            {__('Cancel', 'give')}
                        </button>
                        {hasCampaign && !isDefaultCampaignForm && (
                            <button type="button" className={styles.removeLink} onClick={() => setStep('confirmRemove')}>
                                {__('Remove from campaign', 'give')}
                            </button>
                        )}
                    </div>
                </>
            )}
        </ModalDialog>
    );
}
