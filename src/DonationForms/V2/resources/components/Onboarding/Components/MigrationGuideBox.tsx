import {useEffect, useState} from 'react';
import {createPortal} from 'react-dom';
import {__} from '@wordpress/i18n';
import {FeatureNoticeDialog} from '../Dialogs';
import TryNewFormBuilderButton from './TryNewFormBuilderButton';
import Button from '@givewp/components/AdminUI/Button';
import styles from '../style.module.scss';

const portalContainer = document.createElement('div');

export default function MigrationGuideBox() {

    const [showDialog, setShowDialog] = useState(false);

    useEffect(() => {
        const container = document.querySelector('.wp-heading-inline');
        container.parentNode.insertBefore(portalContainer, container.nextSibling);
    }, [portalContainer]);

    const HeaderButton = () => createPortal(
        <TryNewFormBuilderButton showModal={() => setShowDialog(true)} />,
        portalContainer
    );

    return (
        <>
            <HeaderButton />

            <div className={styles.migrationGuideBox}>
                <div className={styles.migrationGuideTitle}>
                    {__('Migration Guide', 'give')}
                </div>

                <div className={styles.migrationGuideContent}>
                    {__('Easily upgrade your form to support the new form builder', 'give')}
                </div>

                <Button
                    onClick={(e) => {
                        e.preventDefault();
                        setShowDialog(true);
                    }}
                    style={{width: '100%'}}
                >
                    {__('Upgrade this form', 'give')}
                </Button>
            </div>

            {showDialog && (
                <FeatureNoticeDialog handleClose={() => setShowDialog(false)} />
            )}
        </>
    )
}
