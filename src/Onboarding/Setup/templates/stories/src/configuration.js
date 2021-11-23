import configuration from '../../../../../../../../assets/dist/images/setup-page/configuration@2x.min.png';
import template from '../../row-item.html';

export default () => {
    return template
        .replace(/{{\s*class\s*}}/gi, 'configuration')
        .replace(/{{\s*icon\s*}}/gi, configuration)
        .replace(/{{\s*icon_alt\s*}}/gi, 'configuration')
        .replace(/{{\s*title\s*}}/gi, 'First-time configuration')
        .replace(
            /{{\s*description\s*}}/gi,
            'Every fundraising campaign begins with a donation form. Click here to create your first donation form in minutes. Once created you can use it anywhere on your website.'
        )
        .replace(
            /{{\s*action\s*}}/gi,
            '<a href="#"><span class="screen-reader-text">Configure GiveWP</span><i class="fas fa-chevron-right"></i></a>'
        );
};
