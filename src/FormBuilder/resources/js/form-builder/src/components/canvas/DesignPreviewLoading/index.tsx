import {Spinner} from '@wordpress/components';

import './index.scss';

export default function DesignPreviewLoading() {
    return (
        <div className="givewp__component--DesignPreviewLoading">
            <Spinner />
        </div>
    );
}

