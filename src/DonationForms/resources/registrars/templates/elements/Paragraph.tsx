import {Markup} from 'interweave';
import type {ParagraphProps} from '@givewp/forms/propTypes';

export default function Paragraph({content}: ParagraphProps) {
    return <Markup content={content} tagName={'p'} />;
}
