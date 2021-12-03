import icon from '../../../../../../../../assets/dist/images/setup-page/givewp101@2x.min.png';
import template from '../../row-item.html';

export default () => {
    return template
        .replace(/{{\s*class\s*}}/gi, 'givewp101')
        .replace(/{{\s*icon\s*}}/gi, icon)
        .replace(/{{\s*title\s*}}/gi, 'GiveWP 101')
        .replace(
            /{{\s*description\s*}}/gi,
            'Start off on the right foot by learning the basics of the plugin and how to get the most out of it to further your online fundraising efforts.'
        )
        .replace(
            /{{\s*action\s*}}/gi,
            '<a href="#"><span class="screen-reader-text">Learn more about GiveWP</span><i class="fas fa-chevron-right"></i></a>'
        );
};
