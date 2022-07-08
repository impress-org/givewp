import {ElementProps} from '../index';

interface HtmlProps extends ElementProps {
    html: string;
}

export default function Html({html}: HtmlProps) {
    return <div dangerouslySetInnerHTML={{__html: html}} />;
}
