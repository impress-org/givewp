import './style.scss';
import {TextControl} from "@wordpress/components";

export default function ({TextControls, filter }) {
    const TextProps = TextControls.filter(i => i.filterValue === filter)[0];

    return(
        <>
            <TextControl
                name={TextProps.name}
                onChange={TextProps.onChange}
                value={TextProps.value}
                filterValue={TextProps.filterValue}
                label={TextProps.label}
                help={TextProps.help}
            />
        </>
    );
}


