import React, {useEffect, useState} from "react";
import {__} from '@wordpress/i18n';
import Container from './Container'
import FeedbackButton from "./FeedbackButton";

import {
    Container as PopupContainer,
    Header as PopupHeader,
    Content as PopupContent,
} from './popup'

const feedbackUrl = 'https://docs.givewp.com/nextgenfeedback';

const HIDE_FEEDBACK = 'givewpNextGenHideFeedback'

const Feedback = () => {

    const [ hidden, setHidden ] = useState(true)
    const closeCallback = () => {
        setHidden(true)
        localStorage.setItem(HIDE_FEEDBACK, 'true')
    }

    useEffect(() => {
        setHidden(!! localStorage.getItem(HIDE_FEEDBACK))
    })

    return (
        <Container>
            {!hidden && (
                <PopupContainer>
                    <PopupHeader title={__('Have feedback?', 'give')} closeCallback={closeCallback} />
                    <PopupContent>
                        {__('Let us know what you think about the form builder to help improve the product experience.', 'give')}
                        {' '}
                        <a href={feedbackUrl} target="_blank" rel="noopener noreferrer" onClick={closeCallback} style={{color: 'var(--givewp-primary-600)'}}>
                            {__('Click here', 'give')}
                        </a>
                    </PopupContent>
                </PopupContainer>
            )}
            <FeedbackButton href={feedbackUrl} onClick={closeCallback}>
                {__('Feedback', 'give')}
            </FeedbackButton>
        </Container>
    )
}

export default Feedback;
