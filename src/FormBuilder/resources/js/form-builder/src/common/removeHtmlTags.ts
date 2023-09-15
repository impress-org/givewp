export default function removeHtmlTags(text: string): string {
    const temporaryElement = document.createElement('div');
    temporaryElement.innerHTML = text;
    const cleanHtml = temporaryElement.textContent || temporaryElement.innerText || '';
    const didTextHaveHtmlTags = cleanHtml !== text;

    return didTextHaveHtmlTags ? removeHtmlTags(cleanHtml) : cleanHtml.trim();
}
