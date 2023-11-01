import {slugifyMeta} from "@givewp/form-builder/supports/field-settings/MetaKeyTextControl";
import {flattenBlocks, getFieldNameValidator} from "@givewp/form-builder/hooks/useFieldNameValidator";
import {FieldSettings} from "@givewp/form-builder/supports/field-settings/types";
import {getBlockSupport} from "@wordpress/blocks";
import {GiveWPSupports} from "@givewp/form-builder/supports/types";
import normalizeFieldSettings from "@givewp/form-builder/supports/field-settings/normalizeFieldSettings";

const hasLabelChanged = (slugifiedName, slugifiedLabel) => {
    return slugifiedName !== slugifiedLabel &&
        // Check for incremented field names.
        // Including the underscore is important, otherwise the check will fail while hitting backspace on the label.
        !slugifiedName.startsWith(slugifiedLabel + '_' );
}

const doesBlockSupportFieldNames = (block) => {
    // @ts-ignore
    const giveSupports = getBlockSupport(block.name, 'givewp') as GiveWPSupports;

    const fieldSettings: FieldSettings = normalizeFieldSettings(giveSupports?.fieldSettings);

    return fieldSettings?.metaKey;
}

const getBlockNamesWithoutBlock = (blocks, block) => {
    return blocks
        .flatMap(flattenBlocks)
        .filter(b => b.clientId !== block.clientId)
        .map((block) => block.attributes.fieldName)
        .filter((name) => name)
}

export default (blocks) => {
    return blocks.map((section) => {
        return {
            ...section,
            innerBlocks: section.innerBlocks.map((block) => {

                if(!doesBlockSupportFieldNames(block)) return block;

                const validateFieldName = getFieldNameValidator(
                    getBlockNamesWithoutBlock(blocks, block)
                );

                const slugifiedLabel = slugifyMeta(block.attributes.label);
                let slugifiedName = block.attributes.fieldName || slugifiedLabel;

                if(hasLabelChanged(slugifiedName, slugifiedLabel)) {
                    // The label has changed, so reset the field name.
                    slugifiedName = slugifiedLabel;
                }

                const [isUnique, suggestedName] = validateFieldName(slugifiedName, false);
                const fieldName = isUnique ? slugifiedName : suggestedName;

                const emailTag = block.attributes.storeAsDonorMeta
                    ? `meta_donor_${fieldName}`
                    : `meta_donation_${fieldName}`;

                return {
                    ...block,
                    attributes: {
                        ...block.attributes,
                        fieldName,
                        emailTag,
                    }
                }
            })
        };
    });
}
