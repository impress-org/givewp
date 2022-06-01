import './style.scss';
import {FormTokenField} from "@wordpress/components";

export default function ({data, filter }) {
    const props = data.filter(i => i.filterValue === filter)[0];

    return(
        <>
            <FormTokenField
                name={props.name}
                onChange={props.onChange}
                value={props.value}
                filterValue={props.filterValue}
                label={props.label}
                help={props.help}
            />
            <p className="components-form-token-field__help">
                {props.help}
            </p>
        </>
    );
}


