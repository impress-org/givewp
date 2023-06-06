import {BlockEditProps} from '@wordpress/blocks';
import {RichText} from '@wordpress/block-editor';
import {__} from '@wordpress/i18n';

export default function Edit({attributes, setAttributes}: BlockEditProps<any>) {
    const {content} = attributes;

    return (
        <div>
            <RichText
                tagName="p"
                value={content}
                allowedFormats={['core/bold', 'core/italic', 'core/link']}
                onChange={(value) => setAttributes({content: value})}
                placeholder={__('Enter some text', 'custom-block-editor')}
            />
        </div>
    );
}