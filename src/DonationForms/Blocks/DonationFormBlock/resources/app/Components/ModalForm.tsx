import {useState} from '@wordpress/element';
import IframeResizer from 'iframe-resizer-react';

import '../../editor/styles/index.scss';
import FormModal from '../../common/FormModal';
import useFormWidth from '../../editor/hooks/useFormWidth';

type ModalFormProps = {
    dataSrc: string;
    embedId: string;
    openFormButton: string;
    openByDefault?: boolean;
    isFormRedirect: boolean;
    formViewUrl: string;
};

/**
 * @unreleased
 * @since 3.4.0
 * @since 3.2.0 include types. update BEM classnames.
 * @since 3.0.0
 */
export default function ModalForm({
    dataSrc,
    embedId,
    openFormButton,
    openByDefault,
    isFormRedirect,
    formViewUrl,
}: ModalFormProps) {
    const [dataSrcUrl, setDataSrcUrl] = useState(dataSrc);
    const {formWidth, getFormWidth} = useFormWidth();

    const resetDataSrcUrl = () => {
        if (isFormRedirect) {
            setDataSrcUrl(formViewUrl);
        }
    };

    return (
        <FormModal onChange={resetDataSrcUrl} openFormButton={openFormButton}>
            <IframeResizer
                id={embedId}
                src={dataSrcUrl}
                checkOrigin={false}
                onLoad={getFormWidth}
                style={{
                    width: `${formWidth}px`,
                    border: 'none',
                }}
            />
        </FormModal>
    );
}
