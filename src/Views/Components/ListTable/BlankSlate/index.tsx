import styles from './BlankSlate.module.scss';

interface BlankSlateProps {
    imagePath: string;
    imageAlt: string;
    description: string;
    helpText: string;
}

const BlankSlate = ({imagePath, description, imageAlt, helpText}: BlankSlateProps) => {
    return (
        <div className={styles.container}>
            <img src={} alt={imageAlt} />
            <h3>{description}</h3>
        </div>
    );
};
export default BlankSlate;
