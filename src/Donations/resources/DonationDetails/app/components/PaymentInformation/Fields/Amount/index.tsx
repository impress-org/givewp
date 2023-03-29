import {useState} from 'react';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import {CurrencyField} from '../Field';
import {useFormContext, useWatch} from 'react-hook-form';
import {formatCurrency} from '../../../../utilities/formatter';
import {__} from '@wordpress/i18n';
import {CurrencyInputField} from '@givewp/components/AdminUI/FormElements';
import Button from '@givewp/components/AdminUI/Button';
import NoticeInformationIcon from '@givewp/components/AdminUI/Icons/NoticeInformationIcon';

const {currency} = window.GiveDonations.donationDetails.amount;

export type CurrencyAmountDialogProps = {
    defaultAmount: number;
    amountChanged: (amount: number) => void;
};

export default function AmountField() {
    const [isModalOpen, setIsModalOpen] = useState<boolean>(false);

    const {setValue} = useFormContext();
    const amount = useWatch({name: 'amount'});

    const handleAmountChange = (value) => {
        setValue('amount', value, {shouldDirty: true});
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

function AmountDialog({defaultAmount, amountChanged}: CurrencyAmountDialogProps) {
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
                variant={'primary'}
                size={'large'}
                disabled={!amount || Number(amount) === defaultAmount}
                onClick={() => amountChanged(Number(amount))}
            >
                {__('Set Donation Amount', 'give')}
            </Button>
            <span>
                <NoticeInformationIcon />
                {__('Changes made will not be billed to the donor', 'give')}
            </span>
        </div>
    );
}
