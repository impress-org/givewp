import icon from '../../../../../../../../assets/dist/images/setup-page/addons@2x.min.png';
import template from '../../row-item.html';

export default () => {
	return template
		.replace( /{{\s*class\s*}}/gi, 'addons' )
		.replace( /{{\s*icon\s*}}/gi, icon )
		.replace( /{{\s*title\s*}}/gi, 'GiveWP Add-ons' )
		.replace( /{{\s*description\s*}}/gi,
			'Make your fundraising even more effective with powerful features like Recurring Donations, ask donor\'s to cover processing fees, multiple currencies, eCard dedications, and much more. View our growing library of 35+ add-ons and extend your fundraising now.'
		)
		.replace( /{{\s*action\s*}}/gi, '<a href="#"><span class="screen-reader-text">View Add-ons for GiveWP</span><i class="fas fa-chevron-right"></i></a>' );
};
