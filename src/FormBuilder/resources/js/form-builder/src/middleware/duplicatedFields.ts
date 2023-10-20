import {slugifyMeta} from "@givewp/form-builder/supports/field-settings/MetaKeyTextControl";
import {useFieldNameValidator} from "@givewp/form-builder/hooks";
import {getBlockNames, getFieldNameValidator} from "@givewp/form-builder/hooks/useFieldNameValidator";
import {useState} from "react";

export default (blocks) => {
    return blocks.map((section) => {
        return {
            ...section,
            innerBlocks: section.innerBlocks.map((block) => {

                // Only Text Field blocks, for now.
                if(block.name !== 'givewp/text') return block;

                const isNewField = typeof block.attributes.metaUUID === 'undefined';
                const isDuplicatedField = !isNewField && block.attributes.metaUUID !== block.clientId;

                if(isDuplicatedField) console.log('DUPLICATED')

                const fieldNames = getBlockNames(blocks);
                const validateFieldName = getFieldNameValidator(fieldNames);

                const slugifiedLabel = slugifyMeta(block.attributes.label);
                let slugifiedName = block.attributes.fieldName ?? slugifiedLabel;

                const [isUnique, suggestedName] = validateFieldName(slugifiedName, !isNewField);
                const fieldName = isUnique ? slugifiedName : suggestedName;

                return isDuplicatedField
                    ? {
                        ...block,
                        attributes: {
                            ...block.attributes,
                            metaUUID: block.clientId,
                            fieldName: fieldName,
                        }
                    } : block;
            })
        };
    });
}
