import {useState} from 'react';
import {CurrencyField} from '../Field';
import {useFormContext, useWatch} from 'react-hook-form';
import {__} from '@wordpress/i18n';
import {createInterpolateElement} from '@wordpress/element';

import {formatCurrency} from '../../../../utilities/formatter';

import {CurrencyInputField} from '@givewp/components/AdminUI/FormElements';
import Button from '@givewp/components/AdminUI/Button';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';

import {CurrencyAmountDialogProps} from '../Amount';
import WarningIcon from '@givewp/components/AdminUI/Icons/WarningIcon';

import styles from '../../style.module.scss';

const {currency} = window.GiveDonations.donationDetails.amount;

/**
 *
 * @unreleased
 */
export default function FeeRecoveredField() {
    const [isModalOpen, setIsModalOpen] = useState<boolean>(false);

    const {setValue, register} = useFormContext();
    const feeAmountRecovered = useWatch({name: 'feeAmountRecovered'});

    const handleAmountChange = (value) => {
        setValue('feeAmountRecovered', value, {shouldDirty: true});
        setIsModalOpen(false);
    };

    return (
        <>
            <input hidden {...register('feeAmountRecovered')} />
            <CurrencyField label={__('Fee Recovered', 'give')} editable onEdit={() => setIsModalOpen(true)}>
                {formatCurrency(Number(feeAmountRecovered), currency)}
            </CurrencyField>
            <ModalDialog
                open={isModalOpen}
                onClose={() => setIsModalOpen(false)}
                handleClose={() => setIsModalOpen(false)}
                title={__('Fee Recovered', 'give')}
            >
                <FeeRecoveredDialog defaultAmount={feeAmountRecovered} amountChanged={handleAmountChange} />
            </ModalDialog>
        </>
    );
}

/**
 *
 * @unreleased
 */
function FeeRecoveredDialog({defaultAmount, amountChanged}: CurrencyAmountDialogProps) {
    const [amount, setAmount] = useState<number>(defaultAmount);

    return (
        <div className={styles.currencyDialog}>
            <WarningMessage />
            <CurrencyInputField
                defaultValue={amount}
                currency={currency}
                handleCurrencyChange={(value) => setAmount(value)}
                label={__('Fee Recovered', 'give')}
                placeholder={__('Enter amount', 'give')}
            />
            <Button
                variant={'primary'}
                size={'large'}
                disabled={!amount || Number(amount) === defaultAmount}
                onClick={() => amountChanged(Number(amount))}
            >
                {__('Set Fee Recovered', 'give')}
            </Button>
        </div>
    );
}

function WarningMessage() {
    const message = createInterpolateElement(
        __(
            'Changing the fee is <strong>not recommended</strong> unless correcting an error. It does not charge the donor or change the amount on the gateway.',
            'give'
        ),
        {
            strong: <strong className={styles.bold} />,
        }
    );
    return (
        <span className={styles.warning}>
            <div>
                <WarningIcon />
            </div>
            <div>{message}</div>
        </span>
    );
}
