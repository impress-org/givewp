import './style.scss';
import {TextControl} from "@wordpress/components";

export default function ({TextControls, SelectControl, }) {
    let TextProps = TextControls.filter(i => i.filterValue === SelectControl.props.value);

    return(
        <>
            {SelectControl}
            <TextControl
                name={TextProps[0].name}
                onChange={TextProps[0].onChange}
                value={TextProps[0].value}
                filterValue={TextProps[0].filterValue}
                label={TextProps[0].label}
            />
            <div className="filter__selected">hello</div>
        </>
    );
}


