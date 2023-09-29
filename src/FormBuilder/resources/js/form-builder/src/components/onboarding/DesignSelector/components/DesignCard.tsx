import cx from 'classnames';
import CheckCircleIcon from "../icons/check-circle"
import {setFormSettings, useFormState, useFormStateDispatch} from "@givewp/form-builder/stores/form-state";

const DesignCard = ({design, title, description, image, alt}) => {

    const {settings: {designId}} = useFormState();
    const dispatch = useFormStateDispatch();

    return <div
        className={cx('givewp-design-selector--card', {selected: designId === design})}
        onClick={() => dispatch(setFormSettings({designId: design}))}
    >
        <img src={image} alt={alt} />
        <strong>{title}</strong>
        <p>{description}</p>
        {designId === design && <CheckCircleIcon />}
    </div>
}

export default DesignCard;
