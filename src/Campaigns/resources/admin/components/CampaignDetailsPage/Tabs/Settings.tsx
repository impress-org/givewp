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
import AdminSection, { AdminSectionField, AdminSectionsWrapper } from '@givewp/components/AdminDetailsPage/AdminSection';

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
        <AdminSectionsWrapper>
            <AdminSection
                title={__('Campaign Details', 'give')}
                description={__('This includes the campaign title, description, and the cover of your campaign.', 'give')}
            >
                <AdminSectionField
                    subtitle={__("What's the title of your campaign?", 'give')}
                    description={__("Give your campaign a title that tells donors what it's about.", 'give')}
                    error={errors.title?.message as string}
                >
                    <input {...register('title')} disabled={isDisabled} />
                </AdminSectionField>

                <AdminSectionField
                    subtitle={__("What's your campaign about?", 'give')}
                    description={__('Let your donors know the story behind your campaign.', 'give')}
                    error={errors.shortDescription?.message as string}
                >
                    <TextareaControl
                        name={'shortDescription'}
                        disabled={isDisabled}
                        maxLength={120}
                        rows={3}
                        help={__('This will be displayed in your campaign block and campaign grid.', 'give')}
                    />
                </AdminSectionField>

                <AdminSectionField
                    subtitle={__('Add a cover image for your campaign.', 'give')}
                    description={__('Upload an image to represent and inspire your campaign.', 'give')}
                    error={errors.image?.message as string}
                >
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
                </AdminSectionField>
            </AdminSection>

            {/* Campaign Goal */}
            <AdminSection
            title={__('Campaign Goal', 'give')}
            description={__('How would you like to set your goal?', 'give')}
            >
                <AdminSectionField
                    subtitle={__('Set the details of your campaign goal here.', 'give')}
                    description={__('How would you like to set your goal?', 'give')}
                    error={errors.goalType?.message as string}
                >
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
                </AdminSectionField>

                <AdminSectionField
                    subtitle={goalInputAttributes.getLabel()}
                    description={goalInputAttributes.getDescription()}
                    error={errors.goal?.message as string}
                >

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

                </AdminSectionField>
            </AdminSection>

            {/* Campaign Theme */}
            <AdminSection
            title={__('Campaign Theme', 'give')}
            description={__('Choose a preferred theme for your campaign.', 'give')}
            >
                <AdminSectionField
                    subtitle={__('Select your preferred primary color', 'give')}
                    description={__('This will affect your main cta’s like your donate button, active and focus states of other UI elements.', 'give')}
                    error={errors.primaryColor?.message as string}
                >
                    <ColorControl name="primaryColor" disabled={isDisabled} className={styles.colorControl} />
                </AdminSectionField>

                <AdminSectionField
                    subtitle={__('Select your preferred secondary color', 'give')}
                    description={__('This will affect your goal progress indicator, badges, icons, etc', 'give')}
                    error={errors.secondaryColor?.message as string}
                >
                    <ColorControl name="secondaryColor" disabled={isDisabled} className={styles.colorControl} />
                </AdminSectionField>
            </AdminSection>

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
        </AdminSectionsWrapper>
    );
};
