import {useState} from 'react';
import {__} from '@wordpress/i18n';
import FormBuilderButtonPortal from './FormBuilderButtonPortal';
import Button from '@givewp/components/AdminUI/Button';
import styles from '../style.module.scss';

export default function EditForm() {

    const [showDialog, setShowDialog] = useState(false);

    return (
        <>
            <FormBuilderButtonPortal
                showDialog={showDialog}
                setShowDialog={setShowDialog}
            />

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
        </>
    )
}
