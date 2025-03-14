import {addFilter} from "@wordpress/hooks";
import withTaxonomySettingsRoute from "./taxonomy-settings";
import './style.scss';

addFilter('givewp_form_builder_settings_additional_routes', 'givewp/form-taxonomies', withTaxonomySettingsRoute);
