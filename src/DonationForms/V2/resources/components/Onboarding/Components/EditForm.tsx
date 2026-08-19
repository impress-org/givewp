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

    const {isMigrated, migratedFormUrl} = window.GiveDonationForms;

    return (
        <>
            <FormBuilderButtonPortal
                showDialog={state.show}
                setShowDialog={setState}
                isUpgrading={state.upgrading}
                isEditing={true}
            />

            {isMigrated && migratedFormUrl ? (
                <div className={styles.migrationGuideBox}>
                    <div className={styles.migrationGuideTitle}>
                        <CompassIcon />
                        {__('Upgrade in Progress', 'give')}
                    </div>

                    <div className={styles.migrationGuideContent}>
                        {__('This form has been upgraded to the Visual Form Builder. Complete the transfer to finalize the upgrade.', 'give')}
                    </div>

                    <Button
                        onClick={(e) => {
                            e.preventDefault();
                            window.location.href = migratedFormUrl;
                        }}
                        style={{width: '100%'}}
                    >
                        {__('Continue editing upgraded form', 'give')}
                    </Button>
                </div>
            ) : !isMigrated && (
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
