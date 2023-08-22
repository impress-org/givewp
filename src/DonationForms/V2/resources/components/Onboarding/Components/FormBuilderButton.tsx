import {__} from '@wordpress/i18n';
import {CubeIcon} from '@givewp/components/AdminUI/Icons';
import styles from '../style.module.scss';

export default function FormBuilderButton({showModal}) {
    return (
        <button
            className={styles.tryNewFormBuilderButton}
            onClick={showModal}
        >
            <CubeIcon /> {__('Try the new form builder', 'give')}
        </button>
    )
}
