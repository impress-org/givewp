export default function stripTags(dirtyString: string): string {
    const doc = new DOMParser().parseFromString(dirtyString, 'text/html');
    return (doc.body.textContent || '').trim();
}
