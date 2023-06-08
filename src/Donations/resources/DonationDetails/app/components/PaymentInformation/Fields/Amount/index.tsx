import {useState} from 'react';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import {CurrencyField} from '../Field';
import {useFormContext, useWatch} from 'react-hook-form';
import {formatCurrency} from '../../../../utilities/formatter';
import {__} from '@wordpress/i18n';
import {createInterpolateElement} from '@wordpress/element';

import {CurrencyInputField} from '@givewp/components/AdminUI/FormElements';
import Button from '@givewp/components/AdminUI/Button';
import WarningIcon from '@givewp/components/AdminUI/Icons/WarningIcon';
import styles from '../../style.module.scss';

const {currency} = window.GiveDonations.donationDetails.amount;

/**
 *
 * @unreleased
 */

export type CurrencyAmountDialogProps = {
    defaultAmount: number;
    amountChanged: (amount: number) => void;
};

export default function AmountField() {
    const [isModalOpen, setIsModalOpen] = useState<boolean>(false);

    const {setValue, register} = useFormContext();
    const amount = useWatch({name: 'amount'});

    const handleAmountChange = (value) => {
        setValue('amount', value, {shouldDirty: true});
        setIsModalOpen(false);
    };

    return (
        <>
            <input hidden {...register('amount')} />
            <CurrencyField label="Total Donation" editable onEdit={() => setIsModalOpen(true)}>
                {formatCurrency(Number(amount), currency)}
            </CurrencyField>
            <ModalDialog
                open={isModalOpen}
                onClose={() => setIsModalOpen(false)}
                handleClose={() => setIsModalOpen(false)}
                title={__('Total Donation', 'give')}
            >
                <AmountDialog defaultAmount={amount} amountChanged={handleAmountChange} />
            </ModalDialog>
        </>
    );
}

/**
 *
 * @unreleased
 */
function AmountDialog({defaultAmount, amountChanged}: CurrencyAmountDialogProps) {
    const [amount, setAmount] = useState<number>(defaultAmount);

    return (
        <div className={styles.currencyDialog}>
            <WarningMessage />
            <CurrencyInputField
                defaultValue={amount}
                currency={currency}
                handleCurrencyChange={(value) => setAmount(value)}
                label={__('Total Donation', 'give')}
                placeholder={__('Enter amount', 'give')}
            />
            <Button
                variant={'primary'}
                size={'large'}
                disabled={!amount || Number(amount) === defaultAmount}
                onClick={() => amountChanged(Number(amount))}
            >
                {__('Set Donation Amount', 'give')}
            </Button>
        </div>
    );
}

/**
 *
 * @unreleased
 */
function WarningMessage() {
    const message = createInterpolateElement(
        __(
            'Changing the amount is <strong>not recommended</strong> unless correcting an error. It does not charge the donor or change the amount on the gateway.',
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
