import useClipboard from 'react-use-clipboard';
import {__} from '@wordpress/i18n';
import {Button} from '@wordpress/components';
import {copy, Icon} from '@wordpress/icons';

type CopyClipboardButtonProps = {text: string};

const CopyToClipboardButton = ({text}: CopyClipboardButtonProps) => {
    const [isCopied, setCopied] = useClipboard(text, {
        successDuration: 1000,
    });

    const label = isCopied ? __('Copied!', 'givewp') : __('Copy tag', 'givewp');

    const CopyIcon = ({size}) => {
        return <Icon icon={copy} size={size} />;
    };

    return (
        <Button
            style={{
                display: 'flex',
                alignItems: 'center',
                gap: '0.25rem',
                height: 'fit-content',
                paddingBlock: '0.5rem',
                fontSize: '12px',
            }}
            variant={'tertiary'}
            onClick={setCopied}
        >
            <CopyIcon size={15} />
            {label}
        </Button>
    );
};

export default CopyToClipboardButton;
