import {slugifyMeta} from "@givewp/form-builder/supports/field-settings/MetaKeyTextControl";
import {getBlockNames, getFieldNameValidator} from "@givewp/form-builder/hooks/useFieldNameValidator";

export default (blocks) => {
    return blocks.map((section) => {
        return {
            ...section,
            innerBlocks: section.innerBlocks.map((block) => {

                // Only Text Field blocks, for now.
                if(block.name !== 'givewp/text') return block;

                const isNewField = typeof block.attributes.metaUUID === 'undefined';
                const isDuplicatedField = !isNewField && block.attributes.metaUUID !== block.clientId;
                if(!isDuplicatedField) return block;

                const fieldNames = getBlockNames(blocks);
                const validateFieldName = getFieldNameValidator(fieldNames);

                const slugifiedLabel = slugifyMeta(block.attributes.label);
                let slugifiedName = block.attributes.fieldName ?? slugifiedLabel;

                const [isUnique, suggestedName] = validateFieldName(slugifiedName, false);
                const fieldName = isUnique ? slugifiedName : suggestedName;

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
