import {ClassicEditor} from '@givewp/form-builder-library';
import {Controller, useFormContext} from 'react-hook-form';

type Props = {
    name: string;
    rows?: number;
};

/**
 * @unreleased
 */
export default ({name, rows = 10, ...rest}: Props) => {

    const {control} = useFormContext();

    return (
        <Controller
            name={name}
            control={control}
            render={({field}) => (
                <ClassicEditor
                    id={name}
                    content={field.value}
                    setContent={(value) => field.onChange(value)}
                    rows={rows}
                    {...rest}
                />
            )}
        />
    );
}
