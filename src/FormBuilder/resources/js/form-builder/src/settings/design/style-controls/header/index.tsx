import {useState} from 'react';
import {BaseControl, Button, DuotonePicker, PanelBody, RangeControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import EmptyIcon from '@givewp/form-builder/components/icons/empty';

export default function HeaderSettings({dispatch, publishSettings}) {
    const [duotone, setDuotone] = useState<string[] | null>(null);
    const [opacity, setOpacity] = useState<number>(0);

    const handleDuotone = (value: string[]) => setDuotone(value);
    const handleFilter = (value: number) => setOpacity(value);

    const setDuotoneBlankSlate = () => setDuotone(['#000', '#000']);

    return (
        <PanelBody className={'givewp-header-styles'} title={__('Header Image', 'give')}>
            <BaseControl id={'givewp-header-styles-duotone-control'} label={__('Filter', 'give')}>
                {duotone ? (
                    <DuotonePicker
                        clearable={false}
                        unsetable={false}
                        duotonePalette={[]}
                        colorPalette={[]}
                        value={duotone}
                        onChange={handleDuotone}
                    />
                ) : (
                    <Button
                        className={'givewp-header-styles__button'}
                        onClick={setDuotoneBlankSlate}
                        icon={<EmptyIcon />}
                    >
                        {__('Duotone', 'give')}
                    </Button>
                )}
            </BaseControl>

            <BaseControl id={'givewp-header-styles-range-control'} label={__('Opacity', 'give')}>
                <RangeControl value={opacity} onChange={handleFilter} min={0} max={100} />
            </BaseControl>
        </PanelBody>
    );
}
