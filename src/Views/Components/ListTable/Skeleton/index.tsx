import cx from 'classnames';
import './styles.scss';

interface SkeletonProps {
    /** Any CSS length. Fills the width by default. */
    inlineSize?: string;
    /** Any CSS length. One line of text by default. */
    blockSize?: string;
    className?: string;
}

/**
 * The one placeholder block for content that is still loading. It is hidden from assistive
 * technology: the component that uses it says "Loading" in text where that matters.
 *
 * @since TBD
 */
export default function Skeleton({inlineSize = '100%', blockSize = '1em', className}: SkeletonProps) {
    return <span className={cx('givewp-skeleton', className)} aria-hidden="true" style={{inlineSize, blockSize}} />;
}
