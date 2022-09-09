import {ElementProps} from '@givewp/forms/propTypes';

interface ParagraphProps extends ElementProps {
    content: string;
}

export default function Paragraph({content}: ParagraphProps) {
    return <p>{content}</p>;
}
