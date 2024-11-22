<?php

namespace Give\FeatureFlags\OptionBasedFormEditor;

use Give\DonationForms\V2\Repositories\DonationFormsRepository;
use Give\FeatureFlags\FeatureFlags;
use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * @since 3.18.0
 */
class OptionBasedFormEditor implements FeatureFlags
{
    /**
     * @since 3.18.0
     */
    public static function isEnabled(): bool
    {
        $option = give_get_option('option_based_form_editor', '');

        if (empty($option)) {
            $option = self::existOptionBasedFormsOnDb();
            give_update_option('option_based_form_editor', $option ? 'enabled' : 'disabled');

            return $option;
        }

        return give_is_setting_enabled($option);
    }

    /**
     * @since 3.18.0
     */
    public static function helperText(): string
    {

        return sprintf(
            '<div class="give-settings-section-group-helper">
                            <img src="%1$s" />
                            <div class="give-settings-section-group-helper__popout">
                                <img src="%2$s" />
                                <h5>%3$s</h5>
                                <p>%4$s</p>
                            </div>
                        </div>',
            esc_url(GIVE_PLUGIN_URL . 'assets/dist/images/admin/help-circle.svg'),
            esc_url(GIVE_PLUGIN_URL . 'assets/dist/images/admin/give-settings-gateways-v2.jpg'),
            __('Only for Option-Based Form Editor', 'give'),
            __('Uses the traditional settings options for creating and customizing a donation form.',
                'give')
        );
    }

    /**
     * @since 3.18.0
     */
    public static function existOptionBasedFormsOnDb(): bool
    {
        return (bool)give(DonationFormsRepository::class)->prepareQuery()
            ->whereNotExists(function (
                QueryBuilder $builder
            ) {
                $builder
                    ->select(['meta_value', 'formBuilderSettings'])
                    ->from(DB::raw(DB::prefix('give_formmeta')))
                    ->where('meta_key', 'formBuilderSettings')
                    ->whereRaw('AND form_id = ID');
            })->count();
    }
}
