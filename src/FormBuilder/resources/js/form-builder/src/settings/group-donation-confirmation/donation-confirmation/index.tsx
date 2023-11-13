import ClassicEditor from '@givewp/form-builder/components/ClassicEditor';

const DonationConfirmation = ({id, content, onChange}) => {
    return (
        <div className={'givewp-form-settings__section__body__extra-gap'}>
            <ClassicEditor id={id} content={content} setContent={(value) => onChange(value)} rows={10} />
        </div>
    );
};

export default DonationConfirmation;
