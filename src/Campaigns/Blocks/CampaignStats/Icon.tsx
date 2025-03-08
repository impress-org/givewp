import {Path, SVG} from '@wordpress/components';

export function StatsIcon() {
    return (
        <SVG width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <Path
                fillRule="evenodd"
                clipRule="evenodd"
                d="M3 2a1 1 0 0 1 1 1v14.8c0 .577 0 .949.024 1.232.022.272.06.372.085.422a1 1 0 0 0 .437.437c.05.025.15.063.422.085C5.25 20 5.623 20 6.2 20H21a1 1 0 1 1 0 2H6.162c-.528 0-.982 0-1.357-.03-.395-.033-.789-.104-1.167-.297a3 3 0 0 1-1.311-1.311c-.193-.378-.264-.772-.296-1.167A17.9 17.9 0 0 1 2 17.838V3a1 1 0 0 1 1-1z"
                fill="#000"
            />
            <Path
                fillRule="evenodd"
                clipRule="evenodd"
                d="M7 13.5a1 1 0 0 1 1 1v3a1 1 0 1 1-2 0v-3a1 1 0 0 1 1-1zM11.5 10.5a1 1 0 0 1 1 1v6a1 1 0 1 1-2 0v-6a1 1 0 0 1 1-1zM16 7.5a1 1 0 0 1 1 1v9a1 1 0 1 1-2 0v-9a1 1 0 0 1 1-1zM20.5 4.5a1 1 0 0 1 1 1v12a1 1 0 1 1-2 0v-12a1 1 0 0 1 1-1z"
                fill="#000"
            />
        </SVG>
    );
}
