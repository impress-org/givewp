import './styles.scss';

type SpotLightProps = {
    title: string;
    description: string;
    children: any;
};

export default function SpotLight({title, description, children}: SpotLightProps) {
    return (
        <div className={'givewp-welcome-banner-spotlight-container'}>
            {children}
            <div className={'givewp-welcome-banner-spotlight-container__information'}>
                <h2>{title}</h2>
                <p>{description}</p>
            </div>
        </div>
    );
}
