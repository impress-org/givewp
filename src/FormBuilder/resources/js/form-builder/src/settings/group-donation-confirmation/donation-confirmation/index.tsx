import { PanelRow } from "@wordpress/components";
import { ClassicEditor } from "@givewp/form-builder-library";

/**
 * @since 3.3.0
 */
const DonationConfirmation = ({id, content, onChange}) => {
    return (
        <PanelRow>
            <ClassicEditor id={id} content={content} setContent={(value) => onChange(value)} rows={10} />
        </PanelRow>
    );
};

export default DonationConfirmation;
