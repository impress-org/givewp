import {__} from '@wordpress/i18n';
import './styles.scss';
import LockedFields, {LockIcon} from './LockedFields';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';

export default function AdditionalFields() {
    const {
        formFieldManagerData: {isInstalled},
    } = getFormBuilderWindowData();

    if (isInstalled) {
        return;
    }

    return (
        <>
            <div className="block-editor-inserter__panel-header">
                <h2 className="block-editor-inserter__panel-title additional-fields-header">
                    {__('Additional Fields', 'give')}
                    <LockIcon />
                </h2>
            </div>

            <div className="block-editor-inserter__panel-content">
                <a
                    className={'additional-fields-upgrade-link'}
                    href="https://docs.givewp.com/vb-upgrade-recurring"
                    target="_blank"
                >
                    {__('Upgrade to unlock additional fields', 'give')}
                </a>
                <LockedFields />
            </div>
        </>
    );
}
