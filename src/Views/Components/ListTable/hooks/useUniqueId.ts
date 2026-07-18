import _uniqueId from 'lodash/uniqueId';
import { useState } from '@wordpress/element';

export const useUniqueId = (prefix) => {
    const [uniqueId] = useState(_uniqueId(prefix));
    return uniqueId;
}
