import { ReactNode } from 'react';
import sharedStyles from '@givewp/components/AdminDetailsPage/AdminDetailsPage.module.scss';

interface AdminSectionProps {
    title: string;
    description: string;
    children: ReactNode;
}

interface AdminSectionFieldProps {
    subtitle?: string;
    children: ReactNode;
    error?: string;
}

export function AdminSectionField({ subtitle, children, error }: AdminSectionFieldProps) {
    return (
        <div className={sharedStyles.sectionField}>
            {subtitle && <div className={sharedStyles.sectionSubtitle}>{subtitle}</div>}
            {children}
            {error && <div className={sharedStyles.errorMsg}>{error}</div>}
        </div>
    );
}

export function AdminSectionsWrapper({ children }: { children: ReactNode }) {
    return (
        <div className={sharedStyles.sections}>
            {children}
        </div>
    );
}

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
