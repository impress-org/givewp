import {BlockEditProps} from '@wordpress/blocks';
import {RichText} from '@wordpress/block-editor';
import {__} from '@wordpress/i18n';

export default function Edit({attributes, setAttributes}: BlockEditProps<any>) {
    const {content} = attributes;

    return (
        <>
            <RichText
                tagName="p"
                value={content}
                allowedFormats={['core/bold', 'core/italic', 'core/link']}
                onChange={(content) => setAttributes({content})}
                placeholder={__('Enter some text', 'give')}
            />
        </>
    );
}
