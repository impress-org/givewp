import {Icon} from "@wordpress/icons";

const defaults = {

    category: 'layout',

    icon: () => <Icon icon={
        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path
                d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
        </svg>
    } />,

    supports: {
        html: false, // Removes support for an HTML mode,
    },

    attributes: {
        title: {
            type: 'string',
            source: 'attribute',
            selector: 'h1',
            default: 'Section Title',
        },
        description: {
            type: 'string',
            source: 'attribute',
            selector: 'p',
            default: 'Section Description',
        },
    },

    save: function() {
        return null; // Save as attributes - not rendered HTML.
    }

}

export default defaults
