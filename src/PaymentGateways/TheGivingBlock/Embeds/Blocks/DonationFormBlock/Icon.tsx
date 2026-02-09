/**
 * TGB Icon Component - Original TGB logo at small scale
 *
 * @unreleased
 */
const TgbIcon = ({ size = '24px', ...rest }: { size?: string | number; [key: string]: unknown }) => (
    <svg width="24" height="24" viewBox="0 0 140.5 161.49" xmlns="http://www.w3.org/2000/svg" {...rest}>
        <g id="Layer_1-2">
            <g id="Symbol_cube">
                <g id="Logo_colors">
                    <polygon fill="#261b4f" points="69.82 0 0 40.31 70.26 80.87 140.08 40.56 69.82 0" />
                    <polygon fill="#fcd328" points="69.82 81.13 70.25 161.49 140.5 120.85 140.08 40.56 69.82 81.13" />
                    <polygon fill="#cee9e5" points="0 120.93 70.26 161.49 70.26 80.87 0 40.31 0 120.93" />
                </g>
            </g>
        </g>
    </svg>
);

export default TgbIcon;
