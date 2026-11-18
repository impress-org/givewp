import Balloon from '@givewp/components/ListTable/Balloon';

import styles from './Notices.module.scss';

interface NoticeProps {
    title: string;
    description: string;
    handleDismiss?: () => void;
    type: 'campaignSettings' | 'campaignForm';
    linkText?: string;
    linkHref?: string;
}

/**
 * @since TBD Render through the shared Balloon in the stock WordPress look.
 */
export default ({title, description, type, linkHref, linkText, handleDismiss}: NoticeProps) => (
    <Balloon
        className={`${styles.tooltip} ${styles[type]}`}
        title={title}
        link={linkText ? {href: linkHref, text: linkText} : undefined}
        onDismiss={handleDismiss}
    >
        {description}
    </Balloon>
);
