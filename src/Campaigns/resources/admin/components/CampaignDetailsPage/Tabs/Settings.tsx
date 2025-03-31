import {__} from '@wordpress/i18n';
import {useFormContext} from 'react-hook-form';
import {Upload} from '../../Inputs';
import styles from '../CampaignDetailsPage.module.scss';
import {getCampaignOptionsWindowData} from '@givewp/campaigns/utils';
import ColorControl from '@givewp/campaigns/admin/components/CampaignDetailsPage/Components/ColorControl';
import TextareaControl from '@givewp/campaigns/admin/components/CampaignDetailsPage/Components/TextareaControl';
import {CurrencyControl} from '@givewp/form-builder-library';
import type {CurrencyCode} from '@givewp/form-builder-library/build/CurrencyControl/CurrencyCode';
import {CampaignGoalInputAttributes} from '@givewp/campaigns/admin/constants/goalInputAttributes';
import CampaignNotice from '@givewp/campaigns/admin/components/CampaignDetailsPage/Components/Notices/CampaignNotice';
import {useCampaignNoticeHook} from '@givewp/campaigns/hooks';

const {currency, isRecurringEnabled} = getCampaignOptionsWindowData();

/**
 * @since 4.0.0
 */
export default function CampaignDetailsSettingsTab() {
    const [showTooltip, dismissTooltip] = useCampaignNoticeHook('givewp_campaign_settings_notice');

    const {
        register,
        watch,
        setValue,
        formState: {errors},
    } = useFormContext();

    const [goal, goalType, image, status, shortDescription] = watch([
        'goal',
        'goalType',
        'image',
        'status',
        'shortDescription',
    ]);

    const isDisabled = status === 'archived';

    const goalInputAttributes = new CampaignGoalInputAttributes(goalType, currency);

    return (
        <>
        <div className={styles.sections}>
                {/* Campaign Details */}
                <div className={styles.section}>
                    <div className={styles.leftColumn}>
                        <div className={styles.sectionTitle}>{__('Campaign Details', 'give')}</div>
                        <div className={styles.sectionDescription}>
                            {__('This includes the campaign title, description, and the cover of your campaign.', 'give')}
                        </div>
                    </div>

                    <div className={styles.rightColumn}>
                        <div className={styles.sectionField}>
                            <div className={styles.sectionSubtitle}>{__("What's the title of your campaign?", 'give')}</div>
                            <div className={styles.sectionFieldDescription}>
                                {__("Give your campaign a title that tells donors what it's about.", 'give')}
                            </div>
                            <input {...register('title')} disabled={isDisabled} />

                            {errors.title && <div className={styles.errorMsg}>{`${errors.title.message}`}</div>}
                        </div>

                        <div className={styles.sectionField}>
                            <div className={styles.sectionSubtitle}>{__("What's your campaign about?", 'give')}</div>
                            <div className={styles.sectionFieldDescription}>
                                {__('Let your donors know the story behind your campaign.', 'give')}
                            </div>

                            <TextareaControl
                                name={'shortDescription'}
                                disabled={isDisabled}
                                maxLength={120}
                                rows={3}
                                help={__('This will be displayed in your campaign block and campaign grid.', 'give')}
                            />

                            {errors.shortDescription && (
                                <div className={styles.errorMsg}>{`${errors.shortDescription.message}`}</div>
                            )}
                        </div>

                        <div className={styles.sectionField}>
                            <div className={styles.sectionSubtitle}>
                                {__('Add a cover image for your campaign.', 'give')}
                            </div>
                            <div className={styles.sectionFieldDescription}>
                                {__('Upload an image to represent and inspire your campaign.', 'give')}
                            </div>
                            <div className={styles.upload}>
                                <Upload
                                    disabled={isDisabled}
                                    id="givewp-campaigns-upload-cover-image"
                                    label={__('Cover', 'give')}
                                    actionLabel={__('Select to upload', 'give')}
                                    value={image}
                                    onChange={(coverImageUrl, coverImageAlt) => {
                                        setValue('image', coverImageUrl, {shouldDirty: true});
                                    }}
                                    reset={() => setValue('image', '', {shouldDirty: true})}
                                />
                                <p className={styles.sectionFieldHelpText}>{__('This will be displayed in your campaign block and campaign grid.', 'give')}</p>
                            </div>

                            {errors.title && <div className={styles.errorMsg}>{`${errors.title.message}`}</div>}
                        </div>
                    </div>
                </div>

                {/* Campaign Goal */}
                <div className={styles.section} id="campaign-goal">
                    <div className={styles.leftColumn}>
                        <div className={styles.sectionTitle}>{__('Campaign Goal', 'give')}</div>
                        <div className={styles.sectionDescription}>
                            {__('How would you like to set your goal?', 'give')}
                        </div>
                    </div>

                    <div className={styles.rightColumn}>
                        <div className={styles.sectionField}>
                            <div className={styles.sectionSubtitle}>
                                {__('Set the details of your campaign goal here.', 'give')}
                            </div>
                            <select {...register('goalType')} disabled={isDisabled}>
                                <option value="amount">{__('Amount raised', 'give')}</option>
                                <option value="donations">{__('Number of donations', 'give')}</option>
                                <option value="donors">{__('Number of donors', 'give')}</option>
                                {isRecurringEnabled && (
                                    <>
                                        <option value="amountFromSubscriptions">
                                            {__('Recurring amount raised', 'give')}
                                        </option>
                                        <option value="subscriptions">{__('Number of recurring donations', 'give')}</option>
                                        <option value="donorsFromSubscriptions">
                                            {__('Number of recurring donors', 'give')}
                                        </option>
                                    </>
                                )}
                            </select>

                            <div className={styles.sectionFieldDescription}>{goalInputAttributes.getHelp()}</div>

                            {errors.goalType && <div className={styles.errorMsg}>{`${errors.goalType.message}`}</div>}
                        </div>

                        <div className={styles.sectionField}>
                            <div className={styles.sectionSubtitle}>{goalInputAttributes.getLabel()}</div>
                            <div className={styles.sectionFieldDescription}>{goalInputAttributes.getDescription()}</div>

                        {goalInputAttributes.isCurrencyType() ? (
                            <div className={styles.sectionFieldCurrencyControl}>
                                <CurrencyControl
                                    name="goal"
                                    currency={currency as CurrencyCode}
                                    disabled={isDisabled}
                                    placeholder={goalInputAttributes.getPlaceholder()}
                                    value={goal}
                                    onValueChange={(value) => {
                                        setValue('goal', Number(value ?? 0), {shouldDirty: true});
                                    }}
                                />
                            </div>
                        ) : (
                            <input
                                type="number"
                                {...register('goal', {valueAsNumber: true})}
                                disabled={isDisabled}
                                placeholder={goalInputAttributes.getPlaceholder()}
                            />
                        )}

                            {errors.goal && <div className={styles.errorMsg}>{`${errors.goal.message}`}</div>}
                        </div>
                    </div>
                </div>

                {/* Campaign Theme */}
                <div className={styles.section}>
                    <div className={styles.leftColumn}>
                        <div className={styles.sectionTitle}>{__('Campaign Theme', 'give')}</div>
                        <div className={styles.sectionDescription}>
                            {__('Choose a preferred theme for your campaign.', 'give')}
                        </div>
                    </div>

                    <div className={styles.rightColumn}>
                        <div className={styles.sectionField}>
                            <div className={styles.sectionSubtitle}>
                                {__('Select your preferred primary color', 'give')}
                            </div>
                            <div className={styles.sectionFieldDescription}>
                                {__(
                                    'This will affect your main cta’s like your donate button, active and focus states of other UI elements.',
                                    'give'
                                )}
                            </div>

                            <ColorControl name="primaryColor" disabled={isDisabled} className={styles.colorControl} />
                        </div>
                        <div className={styles.sectionField}>
                            <div className={styles.sectionSubtitle}>
                                {__('Select your preferred secondary color', 'give')}
                            </div>
                            <div className={styles.sectionFieldDescription}>
                                {__('This will affect your goal progress indicator, badges, icons, etc', 'give')}
                            </div>

                            <ColorControl name="secondaryColor" disabled={isDisabled} className={styles.colorControl} />
                        </div>
                    </div>
                </div>
            </div>

            {showTooltip && (
                <CampaignNotice
                    title={__('Campaign Settings', 'give')}
                    description={__('You can make changes to your campaign page, campaign details, campaign goal, and campaign theme. Publish your campaign when you’re done with your changes.', 'give')}
                    linkHref="https://docs.givewp.com/campaign-settings"
                    linkText={__('Learn more about campaign and form settings', 'give')}
                    handleDismiss={dismissTooltip}
                    type={'campaignSettings'}
                />
            )}
        </>
    );
};
