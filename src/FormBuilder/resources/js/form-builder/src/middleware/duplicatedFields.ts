import {slugifyMeta} from "@givewp/form-builder/supports/field-settings/MetaKeyTextControl";
import {getBlockNames, getFieldNameValidator} from "@givewp/form-builder/hooks/useFieldNameValidator";
import type {BlockInstance} from '@wordpress/blocks';

/**
 * The Duplicated Fields middleware ensure that each block has a unique metaUUID,
 * which is derived form the client ID, and that the field name is unique from the original.
 * @unreleased
 */
export default (blocks: BlockInstance[]) => {
    return blocks.map((section) => {
        return {
            ...section,
            innerBlocks: section.innerBlocks.map((block) => {

                const isNewField = typeof block.attributes.metaUUID === 'undefined';
                const isDuplicatedField = !isNewField && block.attributes.metaUUID !== block.clientId;
                if(!isDuplicatedField) return block;

                // If the duplicated block has a field name it needs to be updated for uniqueness.
                // This is important to do here while we know which block is the duplicate.
                let fieldName = block.attributes.fieldName;
                if(fieldName) {
                    const fieldNames = getBlockNames(blocks);
                    const validateFieldName = getFieldNameValidator(fieldNames);

                    const slugifiedLabel = slugifyMeta(block.attributes.label);
                    let slugifiedName = block.attributes.fieldName ?? slugifiedLabel;

                    const [isUnique, suggestedName] = validateFieldName(slugifiedName, false);
                    fieldName = isUnique ? slugifiedName : suggestedName;
                }

                return {
                    ...block,
                    attributes: {
                        ...block.attributes,
                        metaUUID: block.clientId,
                        fieldName: fieldName,
                    },
                };
            })
        };
    });
}
