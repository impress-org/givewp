import cx from 'classnames';
import CheckCircleIcon from "../icons/check-circle"
import {setFormSettings, useFormState, useFormStateDispatch} from "@givewp/form-builder/stores/form-state";

const DesignCard = ({title, description, image, alt, selected, onSelected}) => {
    return <div
        className={cx('givewp-design-selector--card', {selected: selected})}
        onClick={onSelected}
    >
        <img src={image} alt={alt} />
        <strong>{title}</strong>
        <p>{description}</p>
        {selected && <CheckCircleIcon />}
    </div>
}

export default DesignCard;
