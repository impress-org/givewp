import React from 'react';
import FeedbackIcon from './FeedbackIcon';

const FeedbackButton = (props) => {
    return (
        <button
            className={'button button-primary'}
            style={{
                display: 'flex',
                alignItems: 'center',
                gap: '2px',
                padding: '2px 10px',
                borderRadius: '10px',
                fontSize: '14px',
            }}
            {...props}
        >
            <FeedbackIcon /> {props.children}
        </button>
    );
};

export default FeedbackButton;
