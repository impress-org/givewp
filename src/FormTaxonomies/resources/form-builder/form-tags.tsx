import {__} from "@wordpress/i18n";
import {debounce} from "@wordpress/compose";
import apiFetch from '@wordpress/api-fetch';
import {FormTokenField} from "@wordpress/components";
import {getInitialFormTags} from "./windowData";
import {useState} from "react";

/**
 * @since 3.16.0
 */
const FormTagSetting = ({settings, setSettings}) => {
    const {formTags = getInitialFormTags()} = settings;
    const setFormTags = (tags) => setSettings({formTags: tags})
    const [searchResults, setSearchResults] = useState([])

    /**
     * @since 3.16.0
     */
    const searchTags = (search) => apiFetch({path: '/wp/v2/give_forms_tag?search=' + search}).then(setSearchResults)

    /**
     * @since 3.16.0
     */
    const resolveFormTags = ( tags ) => {
        const [newTag, isNewAndUnique] = validateNewAndUnique(tags, formTags);
        isNewAndUnique
            ? findOrCreateTag(newTag, (id) => setFormTags([...formTags, {id, value: newTag}]))
            : setFormTags(tags)
    }

    return <FormTokenField
        label={__( 'Add Form Tag', 'give')}
        value={formTags ?? []}
        onChange={resolveFormTags}
        onInputChange={debounce(searchTags, 500)}
        suggestions={searchResults?.map((tag) => tag.name)}
        disabled={!formTags}
    ></FormTokenField>
}

/**
 * @since 3.16.0
 */
const findOrCreateTag = (name, callback) => {
    apiFetch( {path: '/wp/v2/give_forms_tag', method: 'POST', data: { name }} )
        .then((response: any) => callback(response.id))
        .catch((error) => {
            if ( error.code !== 'term_exists' ) {
                throw error;
            }
            callback(error.data.term_id)
        })
}

/**
 * @since 3.16.0
 */
const validateNewAndUnique = (tags, previousTags): [string|null, boolean] => {
    // @note New terms are simple string inputs, as opposed to resolved objects ({id, value}).
    const newTag = tags.find( ( term ) => typeof term === "string" )
    const isUnique = !previousTags.some( ( tag ) => newTag === tag.value.toLowerCase() )
    return [
        newTag,
        (newTag && isUnique)
    ]
}

export default FormTagSetting;
