import {TextareaControl} from '@wordpress/components';
import {BlockEditProps} from '@wordpress/blocks';

/**
 * @since 3.0.0
 */
const Label = ({label, helpText}: { label: string; helpText: string }) => (
    <>
        {label}
        <div className="components-base-control__help">
            <small style={{fontStyle: 'normal', color: '#757575'}}>{helpText}</small>
        </div>
    </>
);

/**
 * @since 3.0.0
 */
export default function Edit({attributes}: BlockEditProps<any>) {
    const {label, description} = attributes;

    return (
        <>
            <div>
                <TextareaControl
                    style={{backgroundColor: '#fff'}}
                    readOnly
                    label={<Label label={label} helpText={description} />}
                    value=""
                    onChange={() => null}
                />
            </div>
        </>
    );
}