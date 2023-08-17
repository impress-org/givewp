import {Button} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {SVGProps} from 'react';

const AddButton = (props) => {
    return (
        <Button {...props} variant={'secondary'} style={{width: '100%', justifyContent: 'center'}} icon={PlusIcon}>
            {__('Add another level', 'give')}
        </Button>
    );
};

const PlusIcon = (props: SVGProps<any>) => {
    return (
        <svg {...props} width="13" height="12" viewBox="0 0 13 12" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0.560547 6H12.8532" stroke="#5BA65E" strokeWidth="1.5" />
            <path d="M6.70703 0L6.70703 12" stroke="#5BA65E" strokeWidth="1.5" />
        </svg>
    );
};

export default AddButton;
