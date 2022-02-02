import React, {useRef, useState} from 'react';

import GreenButton from '@givewp/promotions/shared/GreenButton';

import styles from './Modal.module.scss';

const Modal = () => {
    const [isOpen, setIsOpen] = useState(true);
    const firstNameInput = useRef();
    const emailInput = useRef();

    if (!isOpen) {
        return null;
    }

    const handleSubscribe = () => {
        setIsOpen(false);
    };

    return (
        <div className={styles.giveModalContainer} onClick={(event) => console.log(event)}>
            <div className={styles.modal}>
                <h2>🎉 Congratulations!</h2>
                <p>You've just updated to version 2.19 of GiveWP</p>
                <img
                    src="../wp-content/plugins/give/assets/dist/images/email-reports-icon.png"
                    alt="Email Reports Icon"
                    className={styles.icon}
                />
                <p>
                    As a special thanks, we’d like to offer you a <span>FREE</span> premium add-on, called Email
                    Reports!
                </p>
                <form onSubmit={handleSubscribe} className={styles.form}>
                    <div className={styles.fields}>
                        <label>
                            <span>First Name</span>
                            <input type="text" name="firstName" required ref={firstNameInput} />
                        </label>
                        <label>
                            <span>Email Address</span>
                            <input type="email" name="email" required ref={emailInput} />
                        </label>
                    </div>
                    <GreenButton as="input" text="👉 Get my Free Add-on" shadow />
                    <em className={styles.disclosure}>
                        * By submitting this form, you agree to be subscribed to our GiveWP Newsletter (you can
                        unsubscribe at any time. The free Email Reports add-on is only the open source installable zip
                        file. It does not include a license or access to priority support.
                    </em>
                </form>
                <a onClick={() => setIsOpen(false)} href="#">
                    No thanks!
                </a>
            </div>
        </div>
    );
};

export default Modal;
