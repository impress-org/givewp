import CheckCircleIcon from "./icons/check-circle"

const DesignCard = ({title, description}) => {
    return <div style={{display: 'flex', flexDirection: 'column', gap: 'var(--givewp-spacing-1)', padding: 'var(--givewp-spacing-4) var(--givewp-spacing-5)'}}>
        {/*<img src="#" alt="" style={{height: '12.5rem', width: '17.25rem'}} />*/}
        <strong>{title}</strong>
        <p>{description}</p>
        <CheckCircleIcon />
    </div>
}

export default DesignCard;
