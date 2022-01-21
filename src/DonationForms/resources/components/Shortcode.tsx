import {__} from '@wordpress/i18n';
import useCopyText, {CopyTextStatus} from '../hooks/useCopyText';

interface ShortcodeProps {
    code: string;
}

function buttonTextFromStatus(status: CopyTextStatus) {
    switch (status) {
        case CopyTextStatus.Copied:
            return __('Copied!', 'give');
        case CopyTextStatus.Idle:
            return __('Copy Shortcode', 'give');
        case CopyTextStatus.Error:
            return __('Unable to copy', 'give');
    }
}

export default function Shortcode({code}: ShortcodeProps) {
    const copyShortcode = useCopyText(code);

    if (copyShortcode.isSupported) {
        return (
            <button
                type="button"
                disabled={copyShortcode.status !== CopyTextStatus.Idle}
                onClick={copyShortcode.handleCopyText}
            >
                {buttonTextFromStatus(copyShortcode.status)}
            </button>
        );
    }

    return <>{code}</>;
}
