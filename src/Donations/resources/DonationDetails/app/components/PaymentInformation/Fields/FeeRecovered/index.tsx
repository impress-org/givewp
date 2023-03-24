import {useState} from 'react';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import {CurrencyField} from '../Field';
import {useFormContext, useWatch} from 'react-hook-form';
import {formatCurrency} from '../../../../utilities/formatter';
import {__} from '@wordpress/i18n';
import {CurrencyInputField} from '@givewp/components/AdminUI/FormElements';
import Button from '@givewp/components/AdminUI/Button';
import NoticeInformationIcon from '@givewp/components/AdminUI/Icons/NoticeInformationIcon';
import {CurrencyAmountDialogProps} from '../types';

const {currency} = window.GiveDonations.donationDetails.amount;

export default function FeeRecoveredField() {
    const [isModalOpen, setIsModalOpen] = useState<boolean>(false);

    const {setValue} = useFormContext();
    const feeAmountRecovered = useWatch({name: 'feeAmountRecovered'});

    const handleAmountChange = (value) => {
        setValue('feeAmountRecovered', value, {shouldDirty: true});
        setIsModalOpen(false);
    };

    return (
        <>
            <CurrencyField label="feeRecovered" editable onEdit={() => setIsModalOpen(true)}>
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

function FeeRecoveredDialog({defaultAmount, amountChanged}: CurrencyAmountDialogProps) {
    const [amount, setAmount] = useState<number>(defaultAmount);

    return (
        <div>
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
            <span>
                <NoticeInformationIcon />
                {__('Changes made will not be billed to the donor', 'give')}
            </span>
        </div>
    );
}
