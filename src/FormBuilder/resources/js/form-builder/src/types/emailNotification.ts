export type EmailNotification = {
    id: string;
    title: string;
    statusOptions: StatusOption[];
    defaultValues: {
        notification: string;
        email_subject: string;
        email_header: string;
        email_message: string;
        email_content_type: 'text/html'|'text/plain';
    };
    supportsRecipients: boolean;
}

type StatusOption = {
    label: string;
    value: string;
}
