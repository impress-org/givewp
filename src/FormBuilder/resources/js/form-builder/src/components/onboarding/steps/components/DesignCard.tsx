import CheckCircleIcon from "./icons/check-circle"

const DesignCard = ({title, description, image}) => {
    return <div style={{display: 'flex', flexDirection: 'column', gap: 'var(--givewp-spacing-1)', padding: 'var(--givewp-spacing-4) var(--givewp-spacing-5)'}}>
        <img src={image} alt="" style={{height: '12.5rem', width: '17.25rem', marginBottom: 'var(--givewp-spacing-4)'}} />
        <strong>{title}</strong>
        <p>{description}</p>
        <CheckCircleIcon />
    </div>
}

export default DesignCard;
