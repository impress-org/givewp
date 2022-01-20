const feeRecoverySettings = document.querySelector('[name="give-fee-recovery-settings"]')?.value;
export const IS_FEE_RECOVERY_ACTIVE =
    'give_fee_recovery_object' in window && feeRecoverySettings && JSON.parse(feeRecoverySettings)?.fee_recovery;

export const IS_RECURRING_ACTIVE = 'Give_Recurring_Vars' in window;

export const IS_DONATION_SUMMARY_ACTIVE =
    window.classicTemplateOptions.payment_information.donation_summary_enabled === 'enabled';

export const IS_CURRENCY_SWITCHING_ACTIVE = window?.give_cs_json_obj?.length > 20;

export const IS_STRIPE_ACTIVE = 'give_stripe_vars' in window;
