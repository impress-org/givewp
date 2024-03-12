import React, {useEffect, useState} from 'react';
import {__} from '@wordpress/i18n';
import Container from './Container';
import FeedbackButton from './FeedbackButton';

import {Container as PopupContainer, Content as PopupContent, Header as PopupHeader} from './popup';

const feedbackUrl = 'https://feedback.givewp.com/events-beta-feedback';

const HIDE_FEEDBACK = 'givewpEventTicketsBetaFeedback';

const Feedback = () => {

    const [hidden, setHidden] = useState(false);
    const closeCallback = () => {
        setHidden(true);
        localStorage.setItem(HIDE_FEEDBACK, 'true');
    };

    useEffect(() => {
        setHidden(!!localStorage.getItem(HIDE_FEEDBACK));
    }, []);

    // @ts-ignore
    return (
        <Container>
            {!hidden && (
                <PopupContainer>
                    <PopupHeader title={__('Event Beta Feedback', 'give')} closeCallback={closeCallback} />
                    <PopupContent>
                        <div>
                            <span style={{
                                fontSize: '14px',
                                lineHeight: '21px',
                                color: 'var(--givewp-grey-700)',
                            }}>
                                {__(
                                    'This is an early access to our event feature. Let us know how we can improve this feature to make your experience better. You can also choose to opt-out of accessing beta features in Settings.',
                                    'give'
                                )}
                            </span>
                        </div>
                        <div>
                            <a
                                target="_blank"
                                href={feedbackUrl}
                                rel="noopener noreferrer"
                                onClick={closeCallback}
                                style={{color: 'var(--givewp-green-500)'}}
                            >
                                {__('Provide Feedback', 'give')}
                            </a>
                        </div>
                    </PopupContent>
                </PopupContainer>
            )}
            <FeedbackButton onClick={() => setHidden(!hidden)}>{__('Feedback', 'give')}</FeedbackButton>
        </Container>
    );
};

export default Feedback;
