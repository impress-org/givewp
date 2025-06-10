import {useEffect} from 'react';
import {createPortal} from 'react-dom';
import {FeatureNoticeDialog} from '../Dialogs';
import styles from '../style.module.scss';
import FormBuilderButton from './FormBuilderButton';
import {__} from '@wordpress/i18n';

const portalContainer = document.createElement('div');

export default function FormBuilderButtonPortal({isUpgrading = false, isEditing = false, showDialog, setShowDialog}) {
    useEffect(() => {
        const target = document.querySelector('.wp-header-end');
        target.parentNode.insertBefore(portalContainer, target);
    }, [portalContainer]);

    const ButtonPortal = () =>
        createPortal(
            <div className={styles.actionsContainer}>
                <div className={styles.tryNewFormBuilderBtnContainer}>
                    <FormBuilderButton
                        onClick={() =>
                            setShowDialog({
                                show: true,
                                upgrading: false,
                            })
                        }
                    />
                </div>
                {window.GiveDonationForms.campaignUrl && (
                    <a href={window.GiveDonationForms.campaignUrl}>{__('Manage Campaign', 'give')}</a>
                )}
            </div>,
            portalContainer
        );

    return (
        <>
            <ButtonPortal />

            {showDialog && (
                <FeatureNoticeDialog
                    isUpgrading={isUpgrading}
                    isEditing={isEditing}
                    handleClose={() => setShowDialog(false)}
                />
            )}
        </>
    );
}
