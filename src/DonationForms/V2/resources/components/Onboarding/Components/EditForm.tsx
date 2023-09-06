import {useState} from 'react';
import {__} from '@wordpress/i18n';
import FormBuilderButtonPortal from './FormBuilderButtonPortal';
import Button from '@givewp/components/AdminUI/Button';
import {CompassIcon} from '@givewp/components/AdminUI/Icons';
import styles from '../style.module.scss';

export default function EditForm() {

    const [state, setState] = useState({
        show: false,
        upgrading: false
    });

    return (
        <>
            <FormBuilderButtonPortal
                showDialog={state.show}
                setShowDialog={setState}
                isUpgrading={state.upgrading}
                isEditing={true}
            />

            {!window.GiveDonationForms.isMigrated && (
                <div className={styles.migrationGuideBox}>
                    <div className={styles.migrationGuideTitle}>
                        <CompassIcon />
                        {__('Migration Guide', 'give')}
                    </div>

                    <div className={styles.migrationGuideContent}>
                        {__('Easily upgrade your form to support the new form builder', 'give')}
                    </div>

                    <Button
                        onClick={(e) => {
                            e.preventDefault();
                            setState({
                                upgrading: true,
                                show: true
                            });
                        }}
                        style={{width: '100%'}}
                    >
                        {__('Upgrade this form', 'give')}
                    </Button>
                </div>
            )}
        </>
    )
}
