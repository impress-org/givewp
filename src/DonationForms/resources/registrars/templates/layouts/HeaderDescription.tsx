import type {HeaderDescriptionProps} from '@givewp/forms/propTypes';
import {Interweave} from 'interweave';

/**
 * @since 3.0.0
 */
export default function HeaderDescription({text}: HeaderDescriptionProps) {
    return <Interweave content={text} />;
}
