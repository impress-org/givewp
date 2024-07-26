import {__} from "@wordpress/i18n";
import {debounce} from "@wordpress/compose";
import apiFetch from '@wordpress/api-fetch';
import {FormTokenField} from "@wordpress/components";
import {getInitialFormTags} from "./windowData";

/**
 * @note Not sure why it won't let me use a state hook here.
 *       As a workaround, I'm overloading the form settings.
 *       They will be parsed out by the server during publish.
 */
const FormTagSetting = ({settings, setSettings}) => {
    const {
        formTags = getInitialFormTags(),
        formTagsSearchResults: searchResults = [],
    } = settings;

    const setFormTags = (tags) => setSettings({formTags: tags})
    const setSearchResults = (results) => setSettings({formTagsSearchResults: results})

    const searchTags = (search) => apiFetch({path: '/wp/v2/give_forms_tag?search=' + search}).then(setSearchResults)
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
