<?php

namespace Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk;

/**
 * Class ErrorCodes
 *
 * Source of errors
 *  - https://developer.paypal.com/docs/api/orders/v2/#definition-processor_response
 *
 * @since 3.2.0
 */
class ProcessorResponseError
{
    /**
     * This function decode the error code from PayPal.
     * @since 3.2.0
     * @param \stdClass $processorResponse
     */
    public static function getError(\stdClass $processorResponse): string
    {
        $errors = [];
        $self = new self();

        $avsCode = $processorResponse->avs_code ?? '';
        $cvvCode = $processorResponse->cvv_code ?? '';
        $responseCode = $processorResponse->response_code ?? '';
        $paymentAdviceCode = $processorResponse->payment_advice_code ?? '';

        $errorCode = [
            'avsCode' => $self->avsCode(),
            'cvvCode' => $self->cvvCode(),
            'responseCode' => $self->responseCode(),
            'paymentAdviceCode' => $self->paymentAdviceCode(),
        ];

        if (
            property_exists($processorResponse, 'avs_code')
            && array_key_exists($avsCode, $errorCode['avsCode'])
        ) {
            $errors[] = $errorCode['avsCode'][$avsCode];
        }

        if (
            property_exists($processorResponse, 'cvv_code')
            && array_key_exists($cvvCode, $errorCode['cvvCode'])
        ) {
            $errors[] = $errorCode['cvvCode'][$avsCode];
        }

        if (
            property_exists($processorResponse, 'response_code')
            && array_key_exists($responseCode, $errorCode['responseCode'])
        ) {
            $errors[] = $errorCode['responseCode'][$responseCode];
        }

        if (
            property_exists($processorResponse, 'payment_advice_code')
            && array_key_exists($paymentAdviceCode, $errorCode['paymentAdviceCode'])
        ) {
            $errors[] = $errorCode['paymentAdviceCode'][$paymentAdviceCode];
        }

        return implode(' ', $errors);
    }

    /**
     * @since 3.2.0
     */
    private function avsCode(): array
    {
        return [
            '0' => esc_html__('For Maestro, all address information matches.', 'give'),
            '1' => esc_html__('For Maestro, none of the address information matches.', 'give'),
            '2' => esc_html__('For Maestro, part of the address information matches.', 'give'),
            '3' => esc_html__(
                'For Maestro, the merchant did not provide AVS information. It was not processed.',
                'give'
            ),
            '4' => esc_html__(
                'For Maestro, the address was not checked or the acquirer had no response. The service is not available.',
                'give'
            ),
            'A' => esc_html__(
                'For Visa, Mastercard, or Discover transactions, the address matches but the zip code does not match. For American Express transactions, the card holder address is correct.',
                'give'
            ),
            'B' => esc_html__(
                'For Visa, Mastercard, or Discover transactions, the address matches. International A.',
                'give'
            ),
            'C' => esc_html__(
                'For Visa, Mastercard, or Discover transactions, no values match. International N.',
                'give'
            ),
            'D' => esc_html__(
                'For Visa, Mastercard, or Discover transactions, the address and postal code match. International X.',
                'give'
            ),
            'E' => esc_html__(
                'For Visa, Mastercard, or Discover transactions, not allowed for Internet or phone transactions. For American Express card holder, the name is incorrect but the address and postal code match.',
                'give'
            ),
            'F' => esc_html__(
                'For Visa, Mastercard, or Discover transactions, the address and postal code match. UK-specific X. For American Express card holder, the name is incorrect but the address matches.',
                'give'
            ),
            'G' => esc_html__(
                'For Visa, Mastercard, or Discover transactions, global is unavailable. Nothing matches.',
                'give'
            ),
            'I' => esc_html__(
                'For Visa, Mastercard, or Discover transactions, international is unavailable. Not applicable.',
                'give'
            ),
            'M' => esc_html__(
                'For Visa, Mastercard, or Discover transactions, the address and postal code match. For American Express card holder, the name, address, and postal code match.',
                'give'
            ),
            'N' => esc_html__(
                'For Visa, Mastercard, or Discover transactions, nothing matches. For American Express card holder, the address and postal code are both incorrect.',
                'give'
            ),
            'P' => esc_html__(
                'For Visa, Mastercard, or Discover transactions, postal international Z. Postal code only.',
                'give'
            ),
            'R' => esc_html__(
                'For Visa, Mastercard, or Discover transactions, re-try the request. For American Express, the system is unavailable.',
                'give'
            ),
            'S' => esc_html__(
                'For Visa, Mastercard, Discover, or American Express, the service is not supported.',
                'give'
            ),
            'U' => esc_html__(
                'For Visa, Mastercard, or Discover transactions, the service is unavailable. For American Express, information is not available. For Maestro, the address is not checked or the acquirer had no response. The service is not available.',
                'give'
            ),
            'W' => esc_html__(
                'For Visa, Mastercard, or Discover transactions, whole ZIP code. For American Express, the card holder name, address, and postal code are all incorrect.',
                'give'
            ),
            'X' => esc_html__(
                'For Visa, Mastercard, or Discover transactions, exact match of the address and the nine-digit ZIP code. For American Express, the card holder name, address, and postal code are all incorrect.',
                'give'
            ),
            'Y' => esc_html__(
                'For Visa, Mastercard, or Discover transactions, the address and five-digit ZIP code match. For American Express, the card holder address and postal code are both correct.',
                'give'
            ),
            'Z' => esc_html__(
                'For Visa, Mastercard, or Discover transactions, the five - digit ZIP code matches but no address . for American Express, only the card holder postal code is correct.',
                'give'
            ),
            'Null' => esc_html__('For Maestro, no AVS response was obtained .', 'give')
        ];
    }

    private function cvvCode(): array
    {
        return [
            '0' => esc_html__('For Maestro, the CVV2 matched.', 'give'),
            '1' => esc_html__('For Maestro, the CVV2 did not match.', 'give'),
            '2' => esc_html__('For Maestro, the merchant has not implemented CVV2 code handling.', 'give'),
            '3' => esc_html__('For Maestro, the merchant has indicated that CVV2 is not present on card.', 'give'),
            '4' => esc_html__('For Maestro, the service is not available.', 'give'),
            'E' => esc_html__(
                'For Visa, Mastercard, Discover, or American Express, error - unrecognized or unknown response.',
                'give'
            ),
            'I' => esc_html__('For Visa, Mastercard, Discover, or American Express, invalid or null.', 'give'),
            'M' => esc_html__('For Visa, Mastercard, Discover, or American Express, the CVV2/CSC matches.', 'give'),
            'N' => esc_html__(
                'For Visa, Mastercard, Discover, or American Express, the CVV2/CSC does not match.',
                'give'
            ),
            'P' => esc_html__('For Visa, Mastercard, Discover, or American Express, it was not processed.', 'give'),
            'S' => esc_html__(
                'For Visa, Mastercard, Discover, or American Express, the service is not supported.',
                'give'
            ),
            'U' => esc_html__(
                'For Visa, Mastercard, Discover, or American Express, unknown - the issuer is not certified.',
                'give'
            ),
            'X' => esc_html__(
                'For Visa, Mastercard, Discover, or American Express, no response. For Maestro, the service is not available.',
                'give'
            ),
        ];
    }

    /**
     * @since 3.2.0
     */
    private function responseCode(): array
    {
        return [
            '100' => esc_html__('PARTIAL_AUTHORIZATION.', 'give'),
            '130' => esc_html__('INVALID_DATA_FORMAT.', 'give'),
            '1310' => esc_html__('INVALID_AMOUNT.', 'give'),
            '1312' => esc_html__('INVALID_TRANSACTION_CARD_ISSUER_ACQUIRER.', 'give'),
            '1317' => esc_html__('INVALID_CAPTURE_DATE.', 'give'),
            '1320' => esc_html__('INVALID_CURRENCY_CODE.', 'give'),
            '1330' => esc_html__('INVALID_ACCOUNT.', 'give'),
            '1335' => esc_html__('INVALID_ACCOUNT_RECURRING.', 'give'),
            '1340' => esc_html__('INVALID_TERMINAL.', 'give'),
            '1350' => esc_html__('INVALID_MERCHANT.', 'give'),
            '1352' => esc_html__('RESTRICTED_OR_INACTIVE_ACCOUNT.', 'give'),
            '1360' => esc_html__('BAD_PROCESSING_CODE.', 'give'),
            '1370' => esc_html__('INVALID_MCC.', 'give'),
            '1380' => esc_html__('INVALID_EXPIRATION.', 'give'),
            '1382' => esc_html__('INVALID_CARD_VERIFICATION_VALUE.', 'give'),
            '1384' => esc_html__('INVALID_LIFE_CYCLE_OF_TRANSACTION.', 'give'),
            '1390' => esc_html__('INVALID_ORDER.', 'give'),
            '1393' => esc_html__('TRANSACTION_CANNOT_BE_COMPLETED.', 'give'),
            '5100' => esc_html__('GENERIC_DECLINE.', 'give'),
            '5110' => esc_html__('CVV2_FAILURE.', 'give'),
            '5120' => esc_html__('INSUFFICIENT_FUNDS.', 'give'),
            '5130' => esc_html__('INVALID_PIN.', 'give'),
            '5135' => esc_html__('DECLINED_PIN_TRY_EXCEEDED.', 'give'),
            '5140' => esc_html__('CARD_CLOSED.', 'give'),
            '5150' => esc_html__(
                'PICKUP_CARD_SPECIAL_CONDITIONS. try using another card. Do not retry the same card.',
                'give'
            ),
            '5160' => esc_html__('UNAUTHORIZED_USER.', 'give'),
            '5170' => esc_html__('AVS_FAILURE.', 'give'),
            '5180' => esc_html__(
                'INVALID_OR_RESTRICTED_CARD. try using another card. Do not retry the same card.',
                'give'
            ),
            '5190' => esc_html__('SOFT_AVS.', 'give'),
            '5200' => esc_html__('DUPLICATE_TRANSACTION.', 'give'),
            '5210' => esc_html__('INVALID_TRANSACTION.', 'give'),
            '5400' => esc_html__('EXPIRED_CARD.', 'give'),
            '5500' => esc_html__('INCORRECT_PIN_REENTER.', 'give'),
            '5650' => esc_html__('DECLINED_SCA_REQUIRED.', 'give'),
            '5700' => esc_html__('TRANSACTION_NOT_PERMITTED. Outside of scope of accepted business.', 'give'),
            '5710' => esc_html__('TX_ATTEMPTS_EXCEED_LIMIT.', 'give'),
            '5800' => esc_html__('REVERSAL_REJECTED.', 'give'),
            '5900' => esc_html__('INVALID_ISSUE.', 'give'),
            '5910' => esc_html__('ISSUER_NOT_AVAILABLE_NOT_RETRIABLE.', 'give'),
            '5920' => esc_html__('ISSUER_NOT_AVAILABLE_RETRIABLE.', 'give'),
            '5930' => esc_html__('CARD_NOT_ACTIVATED.', 'give'),
            '5950' => esc_html__(
                'DECLINED_DUE_TO_UPDATED_ACCOUNT. External decline as an updated card has been issued.',
                'give'
            ),
            '6300' => esc_html__('ACCOUNT_NOT_ON_FILE.', 'give'),
            '7600' => esc_html__('APPROVED_NON_CAPTURE.', 'give'),
            '7700' => esc_html__('ERROR_3DS.', 'give'),
            '7710' => esc_html__('AUTHENTICATION_FAILED.', 'give'),
            '7800' => esc_html__('BIN_ERROR.', 'give'),
            '7900' => esc_html__('PIN_ERROR.', 'give'),
            '8000' => esc_html__('PROCESSOR_SYSTEM_ERROR.', 'give'),
            '8010' => esc_html__('HOST_KEY_ERROR.', 'give'),
            '8020' => esc_html__('CONFIGURATION_ERROR.', 'give'),
            '8030' => esc_html__('UNSUPPORTED_OPERATION.', 'give'),
            '8100' => esc_html__('FATAL_COMMUNICATION_ERROR.', 'give'),
            '8110' => esc_html__('RETRIABLE_COMMUNICATION_ERROR.', 'give'),
            '8220' => esc_html__('SYSTEM_UNAVAILABLE.', 'give'),
            '9100' => esc_html__('DECLINED_PLEASE_RETRY. Retry.', 'give'),
            '9500' => esc_html__('SUSPECTED_FRAUD. try using another card. Do not retry the same card.', 'give'),
            '9510' => esc_html__('SECURITY_VIOLATION.', 'give'),
            '9520' => esc_html__('LOST_OR_STOLEN. try using another card. Do not retry the same card.', 'give'),
            '9530' => esc_html__(
                'HOLD_CALL_CENTER. The merchant must call the number on the back of the card. POS scenario.',
                'give'
            ),
            '9540' => esc_html__('REFUSED_CARD.', 'give'),
            '9600' => esc_html__('UNRECOGNIZED_RESPONSE_CODE.', 'give'),
            '0000' => esc_html__('APPROVED.', 'give'),
            '00N7' => esc_html__('CVV2_FAILURE_POSSIBLE_RETRY_WITH_CVV.', 'give'),
            '0100' => esc_html__('REFERRAL.', 'give'),
            '0390' => esc_html__('ACCOUNT_NOT_FOUND.', 'give'),
            '0500' => esc_html__('DO_NOT_HONOR.', 'give'),
            '0580' => esc_html__('UNAUTHORIZED_TRANSACTION.', 'give'),
            '0800' => esc_html__('BAD_RESPONSE_REVERSAL_REQUIRED.', 'give'),
            '0880' => esc_html__('CRYPTOGRAPHIC_FAILURE.', 'give'),
            '0890' => esc_html__('UNACCEPTABLE_PIN.', 'give'),
            '0960' => esc_html__('SYSTEM_MALFUNCTION.', 'give'),
            '0R00' => esc_html__('CANCELLED_PAYMENT.', 'give'),
            '10BR' => esc_html__('ISSUER_REJECTED.', 'give'),
            'PCNR' => esc_html__('CONTINGENCIES_NOT_RESOLVED.', 'give'),
            'PCVV' => esc_html__('CVV_FAILURE.', 'give'),
            'PP06' => esc_html__('ACCOUNT_CLOSED. A previously open account is now closed.', 'give'),
            'PPRN' => esc_html__('REATTEMPT_NOT_PERMITTED.', 'give'),
            'PPAD' => esc_html__('BILLING_ADDRESS.', 'give'),
            'PPAB' => esc_html__('ACCOUNT_BLOCKED_BY_ISSUER.', 'give'),
            'PPAE' => esc_html__('AMEX_DISABLED.', 'give'),
            'PPAG' => esc_html__('ADULT_GAMING_UNSUPPORTED.', 'give'),
            'PPAI' => esc_html__('AMOUNT_INCOMPATIBLE.', 'give'),
            'PPAR' => esc_html__('AUTH_RESULT.', 'give'),
            'PPAU' => esc_html__('MCC_CODE.', 'give'),
            'PPAV' => esc_html__('ARC_AVS.', 'give'),
            'PPAX' => esc_html__('AMOUNT_EXCEEDED.', 'give'),
            'PPBG' => esc_html__('BAD_GAMING.', 'give'),
            'PPC2' => esc_html__('ARC_CVV.', 'give'),
            'PPCE' => esc_html__('CE_REGISTRATION_INCOMPLETE.', 'give'),
            'PPCO' => esc_html__('COUNTRY.', 'give'),
            'PPCR' => esc_html__('CREDIT_ERROR.', 'give'),
            'PPCT' => esc_html__('CARD_TYPE_UNSUPPORTED.', 'give'),
            'PPCU' => esc_html__('CURRENCY_USED_INVALID.', 'give'),
            'PPD3' => esc_html__('SECURE_ERROR_3DS.', 'give'),
            'PPDC' => esc_html__('DCC_UNSUPPORTED.', 'give'),
            'PPDI' => esc_html__('DINERS_REJECT.', 'give'),
            'PPDV' => esc_html__('AUTH_MESSAGE.', 'give'),
            'PPDT' => esc_html__('DECLINE_THRESHOLD_BREACH.', 'give'),
            'PPEF' => esc_html__('EXPIRED_FUNDING_INSTRUMENT.', 'give'),
            'PPEL' => esc_html__('EXCEEDS_FREQUENCY_LIMIT.', 'give'),
            'PPER' => esc_html__('INTERNAL_SYSTEM_ERROR.', 'give'),
            'PPEX' => esc_html__('EXPIRY_DATE.', 'give'),
            'PPFE' => esc_html__('FUNDING_SOURCE_ALREADY_EXISTS.', 'give'),
            'PPFI' => esc_html__('INVALID_FUNDING_INSTRUMENT.', 'give'),
            'PPFR' => esc_html__('RESTRICTED_FUNDING_INSTRUMENT.', 'give'),
            'PPFV' => esc_html__('FIELD_VALIDATION_FAILED.', 'give'),
            'PPGR' => esc_html__('GAMING_REFUND_ERROR.', 'give'),
            'PPH1' => esc_html__('H1_ERROR.', 'give'),
            'PPIF' => esc_html__('IDEMPOTENCY_FAILURE.', 'give'),
            'PPII' => esc_html__('INVALID_INPUT_FAILURE.', 'give'),
            'PPIM' => esc_html__('ID_MISMATCH.', 'give'),
            'PPIT' => esc_html__('INVALID_TRACE_ID.', 'give'),
            'PPLR' => esc_html__('LATE_REVERSAL.', 'give'),
            'PPLS' => esc_html__('LARGE_STATUS_CODE.', 'give'),
            'PPMB' => esc_html__('MISSING_BUSINESS_RULE_OR_DATA.', 'give'),
            'PPMC' => esc_html__('BLOCKED_Mastercard.', 'give'),
            'PPMD' => esc_html__('PPMD.', 'give'),
            'PPNC' => esc_html__('NOT_SUPPORTED_NRC.', 'give'),
            'PPNL' => esc_html__('EXCEEDS_NETWORK_FREQUENCY_LIMIT.', 'give'),
            'PPNM' => esc_html__('NO_MID_FOUND.', 'give'),
            'PPNT' => esc_html__('NETWORK_ERROR.', 'give'),
            'PPPH' => esc_html__('NO_PHONE_FOR_DCC_TRANSACTION.', 'give'),
            'PPPI' => esc_html__('INVALID_PRODUCT.', 'give'),
            'PPPM' => esc_html__('INVALID_PAYMENT_METHOD.', 'give'),
            'PPQC' => esc_html__('QUASI_CASH_UNSUPPORTED.', 'give'),
            'PPRE' => esc_html__('UNSUPPORT_REFUND_ON_PENDING_BC.', 'give'),
            'PPRF' => esc_html__('INVALID_PARENT_TRANSACTION_STATUS.', 'give'),
            'PPRR' => esc_html__('MERCHANT_NOT_REGISTERED.', 'give'),
            'PPS0' => esc_html__('BANKAUTH_ROW_MISMATCH.', 'give'),
            'PPS1' => esc_html__('BANKAUTH_ROW_SETTLED.', 'give'),
            'PPS2' => esc_html__('BANKAUTH_ROW_VOIDED.', 'give'),
            'PPS3' => esc_html__('BANKAUTH_EXPIRED.', 'give'),
            'PPS4' => esc_html__('CURRENCY_MISMATCH.', 'give'),
            'PPS5' => esc_html__('CREDITCARD_MISMATCH.', 'give'),
            'PPS6' => esc_html__('AMOUNT_MISMATCH.', 'give'),
            'PPSC' => esc_html__('ARC_SCORE.', 'give'),
            'PPSD' => esc_html__('STATUS_DESCRIPTION.', 'give'),
            'PPSE' => esc_html__('AMEX_DENIED.', 'give'),
            'PPTE' => esc_html__('VERIFICATION_TOKEN_EXPIRED.', 'give'),
            'PPTF' => esc_html__('INVALID_TRACE_REFERENCE.', 'give'),
            'PPTI' => esc_html__('INVALID_TRANSACTION_ID.', 'give'),
            'PPTR' => esc_html__('VERIFICATION_TOKEN_REVOKED.', 'give'),
            'PPTT' => esc_html__('TRANSACTION_TYPE_UNSUPPORTED.', 'give'),
            'PPTV' => esc_html__('INVALID_VERIFICATION_TOKEN.', 'give'),
            'PPUA' => esc_html__('USER_NOT_AUTHORIZED.', 'give'),
            'PPUC' => esc_html__('CURRENCY_CODE_UNSUPPORTED.', 'give'),
            'PPUE' => esc_html__('UNSUPPORT_ENTITY.', 'give'),
            'PPUI' => esc_html__('UNSUPPORT_INSTALLMENT.', 'give'),
            'PPUP' => esc_html__('UNSUPPORT_POS_FLAG.', 'give'),
            'PPUR' => esc_html__('UNSUPPORTED_REVERSAL.', 'give'),
            'PPVC' => esc_html__('VALIDATE_CURRENCY.', 'give'),
            'PPVE' => esc_html__('VALIDATION_ERROR.', 'give'),
            'PPVT' => esc_html__('VIRTUAL_TERMINAL_UNSUPPORTE.D', 'give')
        ];
    }

    /**
     * @since 3.2.0
     */
    private function paymentAdviceCode(): array
    {
        return [
            '21' => esc_html__(
                'For Mastercard, the card holder has been unsuccessful at canceling recurring payment through merchant. Stop recurring payment requests. For Visa, all recurring payments were canceled for the card number requested. Stop recurring payment requests.',
                'give'
            ),
            '01' => esc_html__(
                'For Mastercard, expired card account upgrade or portfolio sale conversion. Obtain new account information before next billing cycle.',
                'give'
            ),
            '02' => esc_html__(
                'For Mastercard, over credit limit or insufficient funds. Retry the transaction 72 hours later. For Visa, the card holder wants to stop only one specific payment in the recurring payment relationship. The merchant must NOT resubmit the same transaction. The merchant can continue the billing process in the subsequent billing period.',
                'give'
            ),
            '03' => esc_html__(
                'For Mastercard, account closed as fraudulent. Obtain another type of payment from customer due to account being closed or fraud. Possible reason: Account closed as fraudulent. For Visa, the card holder wants to stop all recurring payment transactions for a specific merchant. Stop recurring payment requests.',
                'give'
            )
        ];
    }
}
