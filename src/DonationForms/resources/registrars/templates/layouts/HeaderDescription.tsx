import type {HeaderDescriptionProps} from '@givewp/forms/propTypes';
import {Interweave} from 'interweave';

/**
 * @since 3.16.2 Replace <p></p> tag with Interweave to be able to render the content generated through the ClassicEditor component
 * @since 3.0.0
 */
export default function HeaderDescription({text}: HeaderDescriptionProps) {
    return <Interweave content={text} />;
}
