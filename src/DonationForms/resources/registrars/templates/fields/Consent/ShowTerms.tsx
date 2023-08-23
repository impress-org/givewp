export default function ShowTerms({displayType, linkText, linkUrl, openTerms}) {
    const isLinkDisplay = displayType === 'showLinkTerms';

    return isLinkDisplay ? (
        <a href={linkUrl} target={'_blank'}>
            {linkText}
        </a>
    ) : (
        <button
            onClick={openTerms}
            style={{
                border: 0,
                color: 'inherit',
                display: 'inline',
                width: 'fit-content',
                padding: 0,
                background: 'none',
            }}
        >
            {linkText}
        </button>
    );
}
