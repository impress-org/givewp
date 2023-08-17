import React from 'react';
import {Button} from '@wordpress/components';
import FeedbackIcon from './FeedbackIcon';

const FeedbackButton = (props) => {
    return (
        <Button
            style={{
                padding: '0 15px',
                borderRadius: '10px',
            }}
            variant="primary"
            icon={<FeedbackIcon />}
            {...props}
        >
            {props.children}
        </Button>
    );
};

export default FeedbackButton;
