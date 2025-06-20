/**
 * External Dependencies
 */
import { ReactNode } from 'react';

/**
 * Internal Dependencies
 */
import sharedStyles from '@givewp/components/AdminDetailsPage/AdminDetailsPage.module.scss';
import ErrorBoundary from './ErrorBoundary';

/**
 * @since 4.4.0
 */
interface AdminSectionProps {
    title: string;
    description: string;
    children: ReactNode;
}

/**
 * @since 4.4.0
 */
interface AdminSectionFieldProps {
    subtitle?: string;
    children: ReactNode;
    error?: string;
}

/**
 * @since 4.4.0
 */
export function AdminSectionField({ subtitle, children, error }: AdminSectionFieldProps) {
    return (
        <ErrorBoundary>
            <div className={sharedStyles.sectionField}>
                {subtitle && <h3 className={sharedStyles.sectionSubtitle}>{subtitle}</h3>}
                {children}
                {error && <div className={sharedStyles.errorMsg}>{error}</div>}
            </div>
        </ErrorBoundary>
    );
}

/**
 * @since 4.4.0
 */
export function AdminSectionsWrapper({ children }: { children: ReactNode }) {
    return (
        <div className={sharedStyles.sections}>
            {children}
        </div>
    );
}

/**
 * @since 4.4.0
 */
export default function AdminSection({ title, description, children }: AdminSectionProps) {
    return (
        <ErrorBoundary>
            <div className={sharedStyles.section}>
                <div className={sharedStyles.leftColumn}>
                    <h2 className={sharedStyles.sectionTitle}>{title}</h2>
                    <div className={sharedStyles.sectionDescription}>
                        {description}
                    </div>
                </div>

                <div className={sharedStyles.rightColumn}>
                    {children}
                </div>
            </div>
        </ErrorBoundary>
    );
}
