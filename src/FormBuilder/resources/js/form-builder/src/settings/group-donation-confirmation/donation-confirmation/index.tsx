import ClassicEditor from '@givewp/form-builder/components/ClassicEditor';
import {PanelRow} from '@wordpress/components';

const DonationConfirmation = ({id, content, onChange}) => {
    return (
        <PanelRow>
            <ClassicEditor id={id} content={content} setContent={(value) => onChange(value)} rows={10} />
        </PanelRow>
    );
};

export default DonationConfirmation;
