import useClipboard from "react-use-clipboard";
import {__} from "@wordpress/i18n";
import {Button} from "@wordpress/components";
import {copy} from "@wordpress/icons";

const CopyToClipboardButton = ({text}) => {
    const [isCopied, setCopied] = useClipboard(text, {
        successDuration: 1000,
    });
    const label = isCopied
        ? __('Copied!', 'givewp')
        : __('Copy tag', 'givewp');
    return (
        <Button
            variant={'tertiary'}
            icon={copy}
            onClick={setCopied}
        >
            {label}
        </Button>
    )
}

export default CopyToClipboardButton;
