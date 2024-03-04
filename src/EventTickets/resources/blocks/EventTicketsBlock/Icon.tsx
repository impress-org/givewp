import {Icon} from '@wordpress/icons';
import {Path, SVG} from '@wordpress/components';

/**
 * @unreleased
 */
export default function BlockIcon() {
    return (
        <Icon
            icon={
                <SVG width="24" height="24" xmlns="http://www.w3.org/2000/svg">
                    <Path
                        d="M10 6a1 1 0 01.993.883l.007.116v1a1 1 0 01-1.993.117L9 7.999V7a1 1 0 011-1zM10 10.5a1 1 0 01.993.883l.007.116v1a1 1 0 01-1.993.117L9 12.499v-1a1 1 0 011-1zM10 15a1 1 0 01.993.883l.007.116v1a1 1 0 01-1.993.117L9 16.999v-1a1 1 0 011-1z"
                        fill="currentColor"
                    />
                    <Path
                        d="M4.753 3l-.441.006-.375.014c-.576.034-.925.116-1.299.306a3 3 0 00-1.311 1.311c-.19.374-.273.724-.306 1.298l-.015.374-.005.439L1 8.499a1 1 0 001 1 2.5 2.5 0 01.164 4.995L2 14.499a1 1 0 00-1 1v1.748l.006.44.015.375c.033.576.116.926.306 1.3a3 3 0 001.311 1.31l.178.085c.263.115.514.176.905.208l.283.018.334.01L5.2 21h14.067l.57-.01.307-.014a4.47 4.47 0 00.485-.052l.198-.042c.094-.024.182-.053.27-.087l.175-.077.09-.045a3 3 0 001.311-1.31l.085-.18c.051-.116.092-.23.124-.356l.041-.198a3.97 3.97 0 00.043-.35l.017-.282.01-.334.007-.863v-1.3a1 1 0 00-.883-.993L22 14.5a2.5 2.5 0 110-5 1 1 0 001-1l-.001-1.766-.01-.57-.013-.307c-.026-.424-.078-.691-.182-.953l-.076-.175a3 3 0 00-1.356-1.401c-.374-.191-.723-.273-1.298-.307l-.373-.014L19.25 3H4.754zm-.169 2.001H19.42l.295.006.333.017.16.018.12.022.09.028a1 1 0 01.472.453l.015.031.026.074.02.095.01.058.014.141.01.183.01.367L21 7.2v.411l-.032.008A4.502 4.502 0 0017.5 12l.005.212a4.503 4.503 0 003.26 4.117l.235.06-.004 1.117-.01.367-.011.183-.015.141-.018.109-.023.083-.028.065a1 1 0 01-.437.437l-.031.015-.073.025-.095.02a1.572 1.572 0 01-.058.01l-.142.014-.182.01-.368.011-.705.004-14.154-.002-.41-.008-.206-.01-.227-.02-.108-.018-.083-.023-.066-.028a1 1 0 01-.437-.437l-.016-.035-.028-.09-.023-.12-.017-.162-.017-.334-.006-.297L3 16.387l.032-.006A4.502 4.502 0 006.5 11.999l-.005-.211a4.503 4.503 0 00-3.26-4.117L3 7.611l.002-1.031.006-.295.017-.333.017-.16.023-.122.028-.09a1 1 0 01.453-.473l.036-.015.09-.028.12-.022.161-.018.334-.017.297-.006z"
                        fill="currentColor"
                    />
                </SVG>
            }
        />
    );
}
