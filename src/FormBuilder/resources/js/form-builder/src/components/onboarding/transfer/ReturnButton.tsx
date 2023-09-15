import {useEffect, useState} from 'react';
import {__} from '@wordpress/i18n';
import {ArrowUpLeft} from '@givewp/components/AdminUI/Icons'
import {Snackbar} from "@wordpress/components";

export default function ReturnButton() {
    const [hidden, setHidden] = useState(false);

    useEffect(() => {
        setHidden(!sessionStorage.getItem('givewp-show-return-btn'));
        sessionStorage.removeItem('givewp-show-return-btn');
    }, []);

    if (hidden) {
        return null;
    }

    return (
        <div className="components-snackbar-list components-editor-notices__snackbar givewp-return-btn">
            <Snackbar
                icon={<ArrowUpLeft />}
                explicitDismiss={true}
                onDismiss={() => setHidden(true)}
            >
                <span onClick={() => history.back()}>{__('Return to editing form', 'give')}</span>
            </Snackbar>
        </div>
    )
}
