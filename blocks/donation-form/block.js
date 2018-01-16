/**
 * Block dependencies
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const BlockControls = wp.blocks.BlockControls;
const el = wp.element.createElement;

/**
 * Internal block libraries
 */
let giveForms = [];
let giveFormsOptions = {};
if ( CF_FORMS.forms.length ) {
	cfFormsOptions = CF_FORMS.forms;
}

if ( Object.keys( cfFormsOptions ).length ) {
	Object.keys( cfFormsOptions ).forEach( form => {
		cfForms.push( form.id );
	} );
}

/**
 * Register Caldera Forms block
 *
 * @param  {string}   name     Block name.
 * @param  {Object}   settings Block settings.
 * @return {?WPBlock}          The block, if it has been successfully
 *                             registered; otherwise `undefined`.
 */
registerBlockType( 'give/donation-form', {
	title: __( 'Donation Form' ),
	icon: 'feedback',
	category: 'common',
	attributes: {
		formId: {
			formId: 'string',
		}
	},
	edit( { attributes, setAttributes, className, focus, id } ) {
		const assetsAppended = {
			css: [],
			js: [],
		};

		/**
		 * Append CSS or JavaScript as needed if not already done
		 *
		 * @since 1.5.8
		 *
		 * @param {String} type
		 * @param {String} url
		 * @param {String} identifier
		 */
		function appendCSSorJS( type, url, identifier ) {

			switch ( type ) {
				case  'css' :
					if ( - 1 < assetsAppended.css.indexOf( identifier ) ) {
						const fileref = document.createElement( 'link' );
						fileref.rel = 'stylesheet';
						fileref.type = 'text/css';
						fileref.href = url;
						fileref.id = identifier;
						document.getElementsByTagName( 'head' )[ 0 ].appendChild( fileref );
						assetsAppended.css.push( identifier );
					}

					break;
				case 'js' :

					if ( - 1 < assetsAppended.js.indexOf( identifier ) ) {
						const fileref = document.createElement( 'script' );
						fileref.type = 'text/javascript';
						fileref.src = url;
						fileref.id = identifier;
						document.getElementsByTagName( 'body' )[ 0 ].appendChild( fileref );
						assetsAppended.js.push( identifier );
					}
			}

		}

		/**
		 * Get a form preview and put where it goes.
		 *
		 * NOTE: This is a super-hack, must replace
		 *
		 * @since 1.5.8
		 *
		 * @param {String} formId
		 */
		function previewForm( formId ) {

			if ( false === formId || 'false' === formId || - 1 < cfForms.indexOf( formId ) ) {
				return;
			}

			let url = CF_FORMS.previewApi.replace( '-formId-', formId );
			let el = document.getElementById( 'caldera-forms-preview-' + id );
			wp.apiRequest( {
				url: url,
				method: 'GET',
				params: {
					preview: true
				},
				cache: true

			} ).done( (response => {

				if ( null !== el ) {
					el.innerHTML = '';
					el.innerHTML = response.html;
					Object.keys( response.css ).forEach( key => {
						appendCSSorJS( 'css', response.css[ key ], key );
					} );
					Object.keys( response.js ).forEach( key => {
						appendCSSorJS( 'js', response.js[ key ], key );
					} );
				}

			}) )
				.fail( function( response ) {
					if ( null !== el ) {
						el.innerHTML = __( 'Form Not Found' );
					}
				} );
		}

		let previewEl = el(
			'div',
			{
				id: 'caldera-forms-preview-' + id
			},
			[
				el(
					'span',
					{
						className: 'spinner is-active'
					}
				)
			]
		);
		let formId = attributes.formId;
		if ( formId ) {
			previewForm( formId );
		}
		let formPreview = attributes.formPreview;
		setAttributes( { formPreview: 'Load' } );

		const updateFormId = event => {
			formId = event.target.value;
			setAttributes( { formId: formId } );

			previewForm( formId );

			event.preventDefault();

		};

		let formOptions = [
			el(
				'option',
				{},
				__( '-- Choose --' )
			)
		];

		if ( CF_FORMS.forms.length ) {
			CF_FORMS.forms.forEach( form => {
				formOptions.push(
					el(
						'option',
						{
							value: form.formId
						},
						form.name
					)
				);
			} );
		}

		const selectId = 'caldera-forms-form-selector-';
		let select = el(
			'select',
			{
				value: formId,
				id: selectId,
				onChange: updateFormId,

			},
			formOptions,
		);

		const formChooser = el(
			'div',
			{},
			[
				el( 'label', {
					for: selectId
				}, __( 'Form' ) ),
				select
			]
		);

		const focusControls = el(
			BlockControls, {
				key: 'controls'
			},
			formChooser
		);

		return (
			<div className={className}>
				{previewEl}

				{focus && focusControls}
			</div>
		);
	},
	save: function( { attributes, className } ) {
		return null;
	},
} );
