import {__} from '@wordpress/i18n';

import {useContext} from 'react';
import {ModalContext} from '@givewp/components/AdminUI/FormPage';

import ActionContainer from './ActionContainer';

import styles from './style.module.scss';
import {useWatch} from 'react-hook-form';
import ExternalIcon from '@givewp/components/AdminUI/Icons/ExternalIcon';
import {PaymentInformation} from '../types';
import PaypalIcon from '@givewp/components/AdminUI/Icons/PaypalIcon';
import {TextInputField} from '@givewp/components/AdminUI/FormElements';

function Legend({title}) {
    return (
        <div className={styles.legend}>
            <legend>
                <h2>{title}</h2>
            </legend>
            <div className={styles.paymentType}>
                <div>Subscriber</div>
                <StatusSelect />
            </div>
        </div>
    );
}

// Todo: Replace with StatusSelectComponent - ReactSelect - will move to AdminUI components
function StatusSelect() {
    return (
        <select>
            <option>Completed</option>
        </select>
    );
}

// Todo: Replace with SearchableSelectComponent - ReactSelect - will move to AdminUI components
function FormSelect() {
    return (
        <select>
            <option>Form</option>
        </select>
    );
}

export default function PaymentInformation({register}: PaymentInformation) {
    const confirmActionDialog = useContext(ModalContext);

    const totalDonation = useWatch({
        name: 'totalDonation',
    });

    const feeAmount = useWatch({
        name: 'feeAmount',
    });

    return (
        <fieldset className={styles.paymentInformation}>
            <Legend title={__('Payment Information', 'give')} />
            <div className={styles.actions}>
                <ActionContainer
                    label={__('Total Donation', 'give')}
                    value={totalDonation}
                    type={'amount'}
                    showEditDialog={(event) =>
                        confirmActionDialog(
                            __(' Edit total donation', 'give'),
                            <TextInputField
                                {...register('totalDonation')}
                                label={__('Total Donations', 'give')}
                                asCurrencyField
                            />,
                            null,
                            __('Save Changes', 'give'),
                            __('Changes made will not be billed to the donor', 'give')
                        )
                    }
                />
                <ActionContainer
                    label={__('Fee amount', 'give')}
                    value={feeAmount}
                    type={'amount'}
                    showEditDialog={(event) =>
                        confirmActionDialog(
                            __(' Edit fee amount', 'give'),
                            <TextInputField
                                {...register('feeAmount')}
                                label={__('Fee Amount', 'give')}
                                asCurrencyField
                            />,
                            null,
                            __('Save Changes', 'give'),
                            __('Changes made will not be billed to the donor', 'give')
                        )
                    }
                />
                <ActionContainer label={__('Donation form', 'give')} value={<FormSelect />} type={'text'} />
                <ActionContainer
                    label={__('Donation date', 'give')}
                    value={'September 6, 2022'}
                    type={'text'}
                    showEditDialog={null}
                />
                <ActionContainer
                    label={__('Donation time', 'give')}
                    value={'10:00 am'}
                    type={'text'}
                    showEditDialog={null}
                />
                <ActionContainer
                    label={__('Payment method', 'give')}
                    value={
                        <>
                            <PaypalIcon />
                            {__('Paypal', 'give')}
                        </>
                    }
                    type={'text'}
                />

                <a href={'/'}>
                    <ExternalIcon />

                    {__('View Payment gateway', 'give')}
                </a>
            </div>
        </fieldset>
    );
}
