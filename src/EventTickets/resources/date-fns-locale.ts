import * as locales from 'date-fns/locale';

const browserLanguage = navigator.language;
const localizedCode = browserLanguage.replace('-', '');
const genericCode = browserLanguage.split('-')[0];
const locale = locales[localizedCode] ?? locales[genericCode] ?? locales.enUS;

export default locale;
