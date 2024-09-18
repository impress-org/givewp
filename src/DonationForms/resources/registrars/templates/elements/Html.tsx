import type {HtmlProps} from '@givewp/forms/propTypes';
import {Interweave} from 'interweave';

/**
 * @unreleased updated to use interweave
 * @since 3.0.0
 */
export default function Html({html}: HtmlProps) {
    return <Interweave tagName="div" content={html} />;
}
