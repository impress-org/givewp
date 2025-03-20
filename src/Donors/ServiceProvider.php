<?php

namespace Give\Donors;

use Give\DonationForms\Models\DonationForm;
use Give\Donors\Actions\CreateUserFromDonor;
use Give\Donors\Actions\SendDonorUserRegistrationNotification;
use Give\Donors\Actions\UpdateAdminDonorDetails;
use Give\Donors\CustomFields\Controllers\DonorDetailsController;
use Give\Donors\Exceptions\FailedDonorUserCreationException;
use Give\Donors\ListTable\DonorsListTable;
use Give\Donors\Migrations\AddPhoneColumn;
use Give\Donors\Models\Donor;
use Give\Donors\Repositories\DonorRepositoryProxy;
use Give\Framework\Migrations\MigrationsRegister;
use Give\Helpers\Hooks;
use Give\Log\Log;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;
use Give_Donor as LegacyDonor;

/**
 * @since 2.19.6
 */
class ServiceProvider implements ServiceProviderInterface
{

    /**
     * @inheritDoc
     */
    public function register()
    {
        give()->singleton('donors', DonorRepositoryProxy::class);
        give()->singleton(DonorsListTable::class, function () {
            $listTable = new DonorsListTable();
            Hooks::doAction('givewp_donors_list_table', $listTable);

            return $listTable;
        });
    }

    /**
     * @inheritDoc
     *
     * @since 3.7.0 Register "AddPhoneColumn" migration and add the "give_admin_donor_details_updating" action
     */
    public function boot()
    {
        $userId = get_current_user_id();
        $showLegacy = get_user_meta($userId, '_give_donors_archive_show_legacy', true);
        // only register new admin page if user hasn't chosen to use the old one
        if (empty($showLegacy)) {
            Hooks::addAction('admin_menu', DonorsAdminPage::class, 'registerMenuItem', 30);

            if (DonorsAdminPage::isShowing()) {
                Hooks::addAction('admin_enqueue_scripts', DonorsAdminPage::class, 'loadScripts');
            }
        } elseif (DonorsAdminPage::isShowing()) {
            Hooks::addAction('admin_head', DonorsAdminPage::class, 'renderReactSwitch');
        }

        $this->addCustomFieldsToDonorDetails();
        $this->enforceDonorsAsUsers();

        give(MigrationsRegister::class)->addMigrations([
            AddPhoneColumn::class,
        ]);

        Hooks::addAction('give_admin_donor_details_updating', UpdateAdminDonorDetails::class, '__invoke', 10, 2);
    }

    /**
     * @since 3.0.0
     */
    private function addCustomFieldsToDonorDetails()
    {
        add_action('give_donor_after_tables', static function (LegacyDonor $legacyDonor) {
            /** @var Donor $donor */
            $donor = Donor::find($legacyDonor->id);

            echo (new DonorDetailsController())->show($donor);
        });
    }

    /**
     * Hook into the donor creation process to ensure that donors are also users.
     * @since 3.2.0
     */
    protected function enforceDonorsAsUsers()
    {
        add_action('givewp_donate_controller_donor_created', function (Donor $donor, $formId) {
            if (!$donor->userId) {
                try {
                    give(CreateUserFromDonor::class)->__invoke($donor);

                    if (DonationForm::find($formId)->settings->registrationNotification) {
                        give(SendDonorUserRegistrationNotification::class)->__invoke($donor);
                    }
                } catch (FailedDonorUserCreationException $e) {
                    Log::error($e->getLogMessage(), [
                        'donor' => $donor,
                        'previous' => $e->getPrevious(),
                    ]);
                }
            }
        }, 10, 2);
    }
}
