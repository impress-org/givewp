import {useState} from 'react';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import {CurrencyField} from '../Field';
import {useFormContext, useWatch} from 'react-hook-form';
import {formatCurrency} from '../../../../utilities/formatter';
import {__} from '@wordpress/i18n';
import {CurrencyInputField} from '@givewp/components/AdminUI/FormElements';
import Button from '@givewp/components/AdminUI/Button';

const currency = window.GiveDonationsDetails.donationDetails.amount.currency;

export default function Amount() {
    const [isModalOpen, setIsModalOpen] = useState<boolean>(false);

    const {setValue} = useFormContext();
    const amount = useWatch({name: 'amount'});

    const handleAmountChange = (value) => {
        setValue('amount', value);
        setIsModalOpen(false);
    };

    return (
        <>
            <CurrencyField label="Amount" editable onEdit={() => setIsModalOpen(true)}>
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

function AmountDialog({defaultAmount, amountChanged}) {
    const [amount, setAmount] = useState<number>(defaultAmount);

    return (
        <div>
            <CurrencyInputField
                defaultValue={amount}
                currency={currency}
                handleCurrencyChange={(value) => setAmount(value)}
                label={__('Total Donation', 'give')}
                placeholder={__('Enter amount', 'give')}
            />
            <Button
                disabled={!amount || Number(amount) === defaultAmount}
                onClick={() => amountChanged(Number(amount))}
            >
                Set Donation Amount
            </Button>
        </div>
    );
}
