/**
 * Vendor dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
const {useInstanceId} = wp.compose;
const {BaseControl, ColorPalette} = wp.components;

const ColorControl = ({name, label, help, className, value, hideLabelFromVision, onChange, colors}) => {
    const instanceId = useInstanceId(ColorControl);
    const id = `give-color-control-${name}-${instanceId}`;
    return (
        <BaseControl label={label} hideLabelFromVision={hideLabelFromVision} id={id} help={help} className={className}>
            <ColorPalette value={value} colors={colors} onChange={(newValue) => onChange(newValue)} clearable={true} />
        </BaseControl>
    );
};

ColorControl.propTypes = {
    label: PropTypes.string,
    value: PropTypes.any.isRequired,
    onChange: PropTypes.func,
    name: PropTypes.string.isRequired,
    help: PropTypes.string,
    className: PropTypes.string,
    hideLabelFromVision: PropTypes.bool,
};

ColorControl.defaultProps = {
    onChange: null,
    options: null,
};

export default ColorControl;
