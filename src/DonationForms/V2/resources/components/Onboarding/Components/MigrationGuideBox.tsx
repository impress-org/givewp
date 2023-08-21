import {useState} from 'react';
import {createPortal} from 'react-dom';
import {__} from '@wordpress/i18n';
import {FeatureNoticeDialog} from '../Dialogs';
import TryNewFormBuilderButton from './TryNewFormBuilderButton';
import Button from '@givewp/components/AdminUI/Button';
import styles from '../style.module.scss';


export default function MigrationGuideBox() {

    const [showDialog, setShowDialog] = useState(false);

    return (
        <>
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

            {createPortal(<TryNewFormBuilderButton showModal={() => setShowDialog(true)} />, document.querySelector('.wp-heading-inline'))}
        </>
    )
}
