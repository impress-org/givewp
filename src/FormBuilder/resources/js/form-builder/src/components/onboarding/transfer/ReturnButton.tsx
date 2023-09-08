import {useState, useEffect, CSSProperties} from 'react';
import {__} from '@wordpress/i18n';
import {ArrowUpLeft, ExitIcon} from '@givewp/components/AdminUI/Icons'

const containerStyles = {
    zIndex: 99999999,
    position: 'fixed',
    left: '10%',
    bottom: 30,
    display: 'flex',
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: 'var(--givewp-grey-900)',
    color: 'var(--givewp-shades-white)',
    borderRadius: 2,
    boxShadow: '0 2px 6px 0 rgba(0, 0, 0, 0.3)',
    fontSize: '0.875rem',
    cursor: 'pointer'
} as CSSProperties;

const redirectContainerStyles = {
    display: 'flex',
    alignItems: 'center',
    gap: 5,
    padding: '10px 15px 8px 15px',
    borderRight: '1px solid var(--givewp-grey-700)',

} as CSSProperties;

const closeIconStyles = {
    fill: 'var(--givewp-shades-white)',
    width: '18px',
    height: '18px',
} as CSSProperties;

export default function ReturnButton() {
    const [hidden, setHidden] = useState(false);

    useEffect(() => {
        setHidden(!sessionStorage.getItem('givewp-show-return-btn'));
        sessionStorage.removeItem('givewp-show-return-btn')
    }, []);

    if (hidden) {
        return null;
    }

    return (
        <div style={containerStyles}>
            <div
                style={redirectContainerStyles}
                onClick={() => history.back()}
            >
                <span><ArrowUpLeft /></span>
                <span style={{marginTop: '-5px'}}>{__('Return to editing form', 'give')}</span>
            </div>
            <div
                onClick={() => setHidden(true)}
                style={{padding: '10px 15px 8px 15px'}}
            >
                <ExitIcon style={closeIconStyles} />
            </div>
        </div>
    )

}
