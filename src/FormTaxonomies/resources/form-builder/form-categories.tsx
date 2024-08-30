import {getAvailableFormCategories, getInitialFormCategories} from "./windowData";
import {buildTermsTree} from "./utils/terms";
import {CheckboxControl} from "@wordpress/components";
import {decodeEntities} from "@wordpress/html-entities";
import {useMemo} from "react";

/**
 * @since 3.16.0
 */
const FormCategorySetting = ({settings, setSettings}) => {
    const {
        formCategories = getInitialFormCategories(),
    } = settings;

    const categoryTree = useMemo(() => buildTermsTree(getAvailableFormCategories()), [])

    /**
     * @since 3.16.0
     */
    const onChange = (categoryId ) => {
        setSettings({formCategories: formCategories.includes( categoryId )
                ? formCategories.filter( ( id ) => id !== categoryId )
                : [ ...formCategories, categoryId ]
        })
    };

    return (
        <div style={{display: 'flex', flexDirection: 'column'}}>
            {renderTerms(categoryTree, formCategories, onChange)}
        </div>
    );
}

/**
 * @since 3.16.0
 */
const renderTerms = (availableTerms, selectedTerms, onChange) => {
    return availableTerms.map((term) => {
        return (
            <div
                key={term.id}
                className="editor-post-taxonomies__hierarchical-terms-choice"
            >
                <CheckboxControl
                    __nextHasNoMarginBottom
                    checked={ selectedTerms.indexOf( term.id ) !== -1 }
                    onChange={ () => {
                        const termId = parseInt( term.id, 10 );
                        onChange( termId );
                    } }
                    label={ decodeEntities( term.name ) }
                />
                { !! term.children.length && (
                    <div className="editor-post-taxonomies__hierarchical-terms-subchoices">
                        { renderTerms( term.children, selectedTerms, onChange ) }
                    </div>
                ) }
            </div>
        );
    } );
};

export default FormCategorySetting;
