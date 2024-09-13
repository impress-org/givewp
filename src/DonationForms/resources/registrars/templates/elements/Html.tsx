import type {HtmlProps} from '@givewp/forms/propTypes';
import {Interweave} from 'interweave';

export default function Html({html}: HtmlProps) {
    return <Interweave tagName="div" content={html} />;

}
