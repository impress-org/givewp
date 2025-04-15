import {Path, SVG} from '@wordpress/components';

export default function ModalCloseIcon() {
    return (
        <SVG
            className="givewp-donation-form-modal__close__icon"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            width="24"
            height="24"
            aria-hidden="true"
            focusable="false"
        >
            <Path
                stroke="black"
                strokeWidth="2"
                d="M13 11.8l6.1-6.3-1-1-6.1 6.2-6.1-6.2-1 1 6.1 6.3-6.5 6.7 1 1 6.5-6.6 6.5 6.6 1-1z"
            ></Path>
        </SVG>
    );
}
