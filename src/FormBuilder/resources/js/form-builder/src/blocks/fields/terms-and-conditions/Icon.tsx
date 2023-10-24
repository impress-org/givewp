import {Icon} from '@wordpress/icons';
import {Path, SVG} from "@wordpress/components";

export default function BlockIcon() {
    return (
        <Icon
            icon={
                <SVG width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <Path
                        fillRule="evenodd"
                        clipRule="evenodd"
                        d="M16.8292 6.34164L17.4317 6.64288L18.0571 6.39271L18.5 6.21555V18.7845L18.0571 18.6073L17.4317 18.3571L16.8292 18.6584L15.3849 19.3805L12.9743 18.577L12.5 18.4189L12.0257 18.577L9.61509 19.3805L8.17082 18.6584L7.56834 18.3571L6.94291 18.6073L6.5 18.7845V6.21555L6.94291 6.39271L7.56834 6.64288L8.17082 6.34164L9.61509 5.6195L12.0257 6.42302L12.5 6.58114L12.9743 6.42302L15.3849 5.6195L16.8292 6.34164ZM20 4L18.5 4.6L17.5 5L15.5 4L12.5 5L9.5 4L7.5 5L6.5 4.6L5 4V5V5.61555V19.3844V20V21L6.5 20.4L7.5 20L9.5 21L12.5 20L15.5 21L17.5 20L18.5 20.4L20 21V20V19.3844V5.61555V5V4ZM16.5 10.25V8.75H8.5V10.25H16.5ZM16.5 13.25V11.75H8.5V13.25H16.5ZM8.5 16.25V14.75H16.5V16.25H8.5Z"
                        fill="currentColor"
                    />
                </SVG>
            }
        />
    );
}

