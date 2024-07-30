import {getAvailableFormCategories, getInitialFormCategories} from "./windowData";
import {buildTermsTree} from "./utils/terms";
import {CheckboxControl} from "@wordpress/components";
import {decodeEntities} from "@wordpress/html-entities";


const FormCategorySetting = ({settings, setSettings}) => {
    const {
        formCategories = getInitialFormCategories(),
    } = settings;

    const categoryTree = buildTermsTree(getAvailableFormCategories());

    const onChange = (categoryId ) => {
        setSettings({formCategories: formCategories.includes( categoryId )
                ? formCategories.filter( ( id ) => id !== categoryId )
                : [ ...formCategories, categoryId ]
        })
    };

    const renderTerms = (renderedTerms) => {
        return renderedTerms.map((term) => {
            return (
                <div
                    key={term.id}
                    className="editor-post-taxonomies__hierarchical-terms-choice"
                >
                    <CheckboxControl
                        __nextHasNoMarginBottom
                        checked={ formCategories.indexOf( term.id ) !== -1 }
                        onChange={ () => {
                            const termId = parseInt( term.id, 10 );
                            onChange( termId );
                        } }
                        label={ decodeEntities( term.name ) }
                    />
                    { !! term.children.length && (
                        <div className="editor-post-taxonomies__hierarchical-terms-subchoices">
                            { renderTerms( term.children ) }
                        </div>
                    ) }
                </div>
            );
        } );
    };

    return (
        <div style={{display: 'flex', flexDirection: 'column'}}>
            {renderTerms(categoryTree)}
        </div>
    )
}

export default FormCategorySetting;
