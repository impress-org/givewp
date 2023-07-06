import cx from 'classnames';
import {__} from '@wordpress/i18n';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import ButtonGroup from '@givewp/components/AdminUI/ButtonGroup';
import Button from '@givewp/components/AdminUI/Button';
import {AlertTriangle, Award} from '@givewp/components/AdminUI/Icons';

import styles from './style.module.scss';

export default function MigrationSuccessDialog({handleClose, formId}) {
    return (
        <ModalDialog
            isOpen={true}
            icon={<Award />}
            showHeader={false}
            showCloseIcon={false}
            title={__('Great! First step completed', 'give')}
            handleClose={handleClose}
        >
            <div className={cx(styles.title, styles.center)}>
                {__('Great! First step completed', 'give')}
            </div>

            <div className={styles.center}>
                {__('You just made a copy of your v2 form to the v3 form. Test the v3 form out to make sure it works as expected.', 'give')}
            </div>

            <br /><br />

            <ButtonGroup align="space-between">
                <Button
                    size="large"
                    variant="secondary"
                    onClick={handleClose}
                >
                    {__('Back to forms', 'give')}
                </Button>

                <Button
                    size="large"
                    onClick={ () => console.log(formId) }
                >
                    {__('Test it out', 'give')}
                </Button>
            </ButtonGroup>

        </ModalDialog>
    )
}
