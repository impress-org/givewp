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
            <div className="give-filter-component">
                <div className="give-filter-component__options">
                    <span> {TextProps[0].value} </span>
                    <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 1L9 9" stroke="#1E1E1E" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9 1L1 9" stroke="#1E1E1E" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>


                </div>
            </div>
        </>
    );
}


