/**
 * @since 4.10.0
 */
export function formatTimestamp(timestamp: string | null | undefined, useComma: boolean = false): string {
    // Handle null, undefined, or empty string
    if (!timestamp) {
        return '—';
    }

    const date = new Date(timestamp);

    // Check if the date is valid
    if (isNaN(date.getTime())) {
        return '—';
    }

    const day = date.getDate();
    const ordinal = (day: number): string => {
        if (day > 3 && day < 21) return 'th';
        switch (day % 10) {
            case 1:
                return 'st';
            case 2:
                return 'nd';
            case 3:
                return 'rd';
            default:
                return 'th';
        }
    };

    const dayWithOrdinal = `${day}${ordinal(day)}`;
    const month = date.toLocaleString('en-US', {month: 'long'});
    const year = date.getFullYear();
    const time = date.toLocaleString('en-US', {hour: 'numeric', minute: '2-digit', hour12: true}).toLowerCase();
    const separator = useComma ? ', ' : ' • ';

    return `${dayWithOrdinal} ${month} ${year}${separator}${time}`;
}

