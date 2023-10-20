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

                const isNewField = block.attributes.metaUUID === block.clientId;

                const fieldNames = getBlockNames(blocks);
                const validateFieldName = getFieldNameValidator(fieldNames);

                let slugifiedName = block.attributes.fieldName ?? slugifyMeta(block.attributes.label);
                const [isUnique, suggestedName] = validateFieldName(slugifiedName, isNewField);
                const fieldName = isUnique ? slugifiedName : suggestedName;

                // console.log(
                //     block.attributes.label,
                //     slugifiedName,
                //     isUnique,
                //     suggestedName,
                //     fieldName
                // )

                const metaUUID = block.attributes.metaUUID !== block.clientId
                    ? block.clientId
                    : block.attributes.metaUUID

                const emailTag = block.attributes.storeAsDonorMeta
                    ? `meta_donor_${fieldName}`
                    : `meta_donation_${fieldName}`;

                return {
                    ...block,
                    attributes: {
                        ...block.attributes,
                        metaUUID,
                        fieldName,
                        emailTag,
                    }
                }
            })
        };
    });
}
