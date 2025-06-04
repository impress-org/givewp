import { ReactNode } from 'react';
import sharedStyles from '@givewp/components/AdminDetailsPage/AdminDetailsPage.module.scss';

/**
 * @unreleased
 */
interface AdminSectionProps {
    title: string;
    description: string;
    children: ReactNode;
}

/**
 * @unreleased
 */
interface AdminSectionFieldProps {
    subtitle?: string;
    children: ReactNode;
    error?: string;
}

/**
 * @unreleased
 */
export function AdminSectionField({ subtitle, children, error }: AdminSectionFieldProps) {
    return (
        <div className={sharedStyles.sectionField}>
            {subtitle && <div className={sharedStyles.sectionSubtitle}>{subtitle}</div>}
            {children}
            {error && <div className={sharedStyles.errorMsg}>{error}</div>}
        </div>
    );
}

/**
 * @unreleased
 */
export function AdminSectionsWrapper({ children }: { children: ReactNode }) {
    return (
        <div className={sharedStyles.sections}>
            {children}
        </div>
    );
}

/**
 * @unreleased
 */
export default function AdminSection({ title, description, children }: AdminSectionProps) {
    return (
        <div className={sharedStyles.section}>
            <div className={sharedStyles.leftColumn}>
                <div className={sharedStyles.sectionTitle}>{title}</div>
                <div className={sharedStyles.sectionDescription}>
                    {description}
                </div>
            </div>

            <div className={sharedStyles.rightColumn}>
                {children}
            </div>
        </div>
    );
}
