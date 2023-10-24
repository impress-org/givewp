import {__} from '@wordpress/i18n';
import {ReactNode} from 'react';
import {BlockEditProps} from '@wordpress/blocks';

const LineItem = ({label}: {label: string | ReactNode}) => {
    return (
        <div style={{display: 'flex', justifyContent: 'space-between'}}>
            <div>{label}</div>
            <div style={{height: '20px', width: '120px', backgroundColor: 'var(--givewp-gray-30)'}}></div>
        </div>
    );
};

export default function Edit(props: BlockEditProps<any>) {
    return (
        <>
            <div
                style={{
                    padding: '30px 20px',
                    display: 'flex',
                    fontSize: '16px',
                    gap: '16px',
                    flexDirection: 'column',
                    border: '1px dashed var(--givewp-gray-100)',
                    borderRadius: '5px',
                    backgroundColor: 'var(--givewp-gray-10)',
                }}
            >
                <LineItem label={__('Payment Amount', 'give')} />
                <LineItem label={__('Giving Frequency', 'give')} />
                <LineItem label={<strong>{__('Donation Total', 'give')}</strong>} />
            </div>
        </>
    );
}
