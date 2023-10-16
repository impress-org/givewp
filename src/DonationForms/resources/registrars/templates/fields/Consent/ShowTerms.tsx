import './styles.scss';

export default function ShowTerms({displayType, linkText, linkUrl, openTerms}) {
    const isLinkDisplay = displayType === 'showLinkTerms';

    return isLinkDisplay ? (
        <a href={linkUrl} target={'_blank'}>
            {linkText}
        </a>
    ) : (
        <button onClick={openTerms}>{linkText}</button>
    );
}
