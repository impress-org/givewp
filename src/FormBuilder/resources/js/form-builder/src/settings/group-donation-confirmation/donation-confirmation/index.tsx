import TextEditor from '@givewp/form-builder/components/settings/TextEditor';

const DonationConfirmation = ({content, onChange}) => {
    return (
        <div className={'givewp-form-settings__section__body__extra-gap'}>
            <TextEditor onChange={onChange} content={content} richText={true} />
        </div>
    );
};

export default DonationConfirmation;
