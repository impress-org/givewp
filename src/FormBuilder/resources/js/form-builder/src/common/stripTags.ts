export default function stripTags(text: string): string {
    const temporaryElement = document.createElement('div');
    temporaryElement.innerHTML = text;
    return (temporaryElement.textContent || temporaryElement.innerText || '').trim();
}
