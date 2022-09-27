import CurrencyInput, {formatValue} from 'react-currency-input-field';

const {currency = 'USD'} = window.storageData?.currency ?? {};

const Currency = ({amount}) => {
    return formatValue({
        value: amount,
        intlConfig: {locale: window.navigator.language, currency},
    });

};

const CurrencyControl = (props) => {
    return (
        <div style={{position: 'relative'}}>
            <CurrencyInput
                {...props}
                style={{
                    ...props.style,
                    width: '150px',
                    padding: '6px 0.5rem',
                }}
                allowDecimals={true}
                allowNegativeValue={false}
                maxLength={9}
                intlConfig={{locale: window.navigator.language, currency}}
            />
        </div>
    );
};

export {
    currency,
    Currency,
    CurrencyControl,
};
