import {useState, Fragment} from 'react';
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';
import {__} from '@wordpress/i18n';
import TextControl from '../text-control';
import Button from '../button';

import {loginWithAPI, verifyEmailWithAPI, resetPasswordWithAPI} from './utils';
import {getSubmittedEmail, isValidEmail} from './utils/validation';
import {getWindowData} from '../../utils';

import ReCAPTCHA from 'react-google-recaptcha';

import './style.scss';

const AuthModal = () => {
    const [recaptcha, setRecaptcha] = useState('');
    const [email, setEmail] = useState('');
    const [login, setLogin] = useState('');
    const [password, setPassword] = useState('');
    const [loginError, setLoginError] = useState(null);
    const [loggingIn, setLoggingIn] = useState(false);
    const [verifyingEmail, setVerifyingEmail] = useState(false);
    const [emailSent, setEmailSent] = useState(false);
    const [emailError, setEmailError] = useState(null);
    const [passwordResetEmail, setPasswordResetEmail] = useState('');
    const [passwordResetError, setPasswordResetError] = useState(null);
    const [showPasswordReset, setShowPasswordReset] = useState(false);
    const [passwordResetSent, setPasswordResetSent] = useState(false);
    const emailAccessEnabled = getWindowData('emailAccessEnabled');
    const loggedInWithoutDonor = getWindowData('loggedInWithoutDonor');
    const recaptchaKey = getWindowData('recaptchaKey');
    const loginEnabled = getWindowData('loginEnabled');

    const handleLogin = async (e) => {
        e.preventDefault();
        if (login && password) {
            setLoggingIn(true);
            // eslint-disable-next-line camelcase
            const {status, response, body_response} = await loginWithAPI({
                login,
                password,
            });

            if (status === 200) {
                window.location.reload();
            } else {
                setLoggingIn(false);
                setLoginError(body_response.message);
                if (response === 'unidentified_login') {
                    setLogin('');
                    setPassword('');
                } else {
                    setPassword('');
                }
            }
        }
    };

    const handleVerifyEmail = async (e) => {
        e.preventDefault();

        // Read the field value from the DOM so browser autofill is respected even
        // when it does not trigger React's onChange and the email state is stale.
        const submittedEmail = getSubmittedEmail(e.currentTarget, 'email', email);

        if (!isValidEmail(submittedEmail)) {
            setEmailError(__('Please enter a valid email address.', 'give'));
            return;
        }

        setEmail(submittedEmail);
        setEmailError(null);
        setVerifyingEmail(true);
        // eslint-disable-next-line camelcase
        const {status, body_response} = await verifyEmailWithAPI({
            email: submittedEmail,
            recaptcha,
        });

        setVerifyingEmail(false);

        if (status === 200) {
            setEmailSent(true);
        } else {
            setEmailError(body_response.message);
        }
    };

    const handlePasswordReset = async (e) => {
        e.preventDefault();

        const submittedEmail = getSubmittedEmail(e.currentTarget, 'reset-email', passwordResetEmail);

        if (!isValidEmail(submittedEmail)) {
            setPasswordResetError(__('Please enter a valid email address.', 'give'));
            return;
        }

        setPasswordResetEmail(submittedEmail);
        setPasswordResetError(null);
        // eslint-disable-next-line camelcase
        const {status, body_response} = await resetPasswordWithAPI(submittedEmail);

        if (status === 200) {
            setPasswordResetSent(true);
            setShowPasswordReset(false);
        } else {
            setPasswordResetError(body_response.message);
        }
    };

    return (
        <div className="give-donor-dashboard__auth-modal">
            <div className="give-donor-dashboard__auth-modal-frame">
                <div className="give-donor-dashboard__auth-modal-heading">
                    {__('Log in to see your donor dashboard', 'give')}
                </div>
                <div className="give-donor-dashboard__auth-modal-content">
                    {loggedInWithoutDonor && (
                        <div className="give-donor-dashboard__auth-modal-notice">
                            {__(
                                'The account you are currently logged into the site with does not have any donations associated with it. Donate now or contact the site administrator to associate this WordPress user with an existing donor.',
                                'give'
                            )}
                        </div>
                    )}
                    {emailAccessEnabled && (
                        <Fragment>
                            <div className="give-donor-dashboard__auth-modal-instruction">
                                {__(
                                    "For privacy, enter the email address used to make a donation below and we'll send you a link to access your donor dashboard.",
                                    'give'
                                )}
                            </div>
                            <form
                                className="give-donor-dashboard__auth-modal-form"
                                onSubmit={(e) => handleVerifyEmail(e)}
                            >
                                <TextControl
                                    icon="envelope"
                                    name="email"
                                    type="email"
                                    autoComplete="email"
                                    value={email}
                                    onChange={(value) => setEmail(value)}
                                />
                                {recaptchaKey && <ReCAPTCHA sitekey={recaptchaKey} onChange={setRecaptcha} />}
                                <div className="give-donor-dashboard__auth-modal-row">
                                    <Button type="submit">
                                        {emailSent === false ? __('Verify Email', 'give') : __('Email Sent', 'give')}
                                        {emailSent === false && (
                                            <FontAwesomeIcon
                                                className={
                                                    verifyingEmail ? 'give-donor-dashboard__auth-modal-spinner' : ''
                                                }
                                                icon={verifyingEmail ? 'spinner' : 'chevron-right'}
                                                fixedWidth
                                            />
                                        )}
                                    </Button>
                                    {emailError && (
                                        <div className="give-donor-dashboard__auth-modal-error">{emailError}</div>
                                    )}
                                </div>
                            </form>
                        </Fragment>
                    )}
                    {emailAccessEnabled && loginEnabled && (
                        <div className="give-donor-dashboard__auth-modal-seperator" />
                    )}
                    {loginEnabled && (
                        <>
                            {showPasswordReset && (
                                <>
                                    <div className="give-donor-dashboard__auth-modal-instruction">
                                        {__('Reset your password by entering your email address.', 'give')}
                                    </div>
                                    <form className="give-donor-dashboard__auth-modal-form" onSubmit={(e) => handlePasswordReset(e)}>
                                        <TextControl
                                            icon="envelope"
                                            name="reset-email"
                                            type="email"
                                            autoComplete="email"
                                            value={passwordResetEmail}
                                            onChange={(value) => setPasswordResetEmail(value)}
                                        />
                                        <div className="give-donor-dashboard__auth-modal-row">
                                            <Button type="submit">
                                                {__('Reset Password', 'give')}
                                                <FontAwesomeIcon
                                                    className={loggingIn ? 'give-donor-dashboard__auth-modal-spinner' : ''}
                                                    icon={loggingIn ? 'spinner' : 'chevron-right'}
                                                    fixedWidth
                                                />
                                            </Button>
                                            {passwordResetError && (
                                                <div className="give-donor-dashboard__auth-modal-error">
                                                    {passwordResetError}
                                                </div>
                                            )}
                                        </div>
                                    </form>
                                </>
                            )}
                            {!showPasswordReset && (
                                <>
                                    <div className="give-donor-dashboard__auth-modal-instruction">
                                        {emailAccessEnabled && (
                                            <Fragment>
                                                {__('Already have an account?', 'give')} <br />
                                            </Fragment>
                                        )}
                                        {__('Log in below to access your dashboard', 'give')}
                                    </div>
                                    <form className="give-donor-dashboard__auth-modal-form" onSubmit={(e) => handleLogin(e)}>
                                        <TextControl icon="user" value={login} onChange={(value) => setLogin(value)} />
                                        <TextControl
                                            icon="lock"
                                            type="password"
                                            value={password}
                                            onChange={(value) => setPassword(value)}
                                        />
                                        <div className="give-donor-dashboard__auth-modal-row">
                                            <Button type="submit">
                                                {__('Log in', 'give')}
                                                <FontAwesomeIcon
                                                    className={loggingIn ? 'give-donor-dashboard__auth-modal-spinner' : ''}
                                                    icon={loggingIn ? 'spinner' : 'chevron-right'}
                                                    fixedWidth
                                                />
                                            </Button>
                                            <Button type="button" onClick={() => setShowPasswordReset(true)}>{__('Forgot Password?', 'give')}</Button>
                                            {loginError && (
                                                <div className="give-donor-dashboard__auth-modal-error">{loginError}</div>
                                            )}
                                        </div>
                                    </form>
                                </>
                            )}
                            {passwordResetSent && (
                                <>
                                    <div className="give-donor-dashboard__auth-modal-instruction">
                                        {__('Instructions for resetting your password have been sent to you email.', 'give')}
                                    </div>
                                </>
                            )}
                        </>
                    )}
                </div>
            </div>
        </div>
    );
};

export default AuthModal;
