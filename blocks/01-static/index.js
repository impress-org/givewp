/**
 * Block dependencies
 */
import icon from './icon';
import './style.scss';
import './editor.scss';

/**
 * Internal block libraries
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

/**
 * Register block
 */
export default registerBlockType(
    'jsforwp/static',
    {
        title: __( 'Example - Static Block' ),
        category: 'common',
        icon: icon,
        keywords: [
            __( 'Banner' ),
            __( 'CTA' ),
            __( 'Shout Out' ),
        ],
        edit: props => {
          return (
            <div className={ props.className }>
              <h2>{ __( 'Static Call to Action' ) }</h2>
              <p>{ __( 'This is really important!' ) }</p>
              {
                !! props.focus && (
                  <p className="sorry warning">✋ Sorry! You cannot edit this block ✋</p>
                )
              }
            </div>
          );
        },
        save: props => {
          return (
            <div>
              <h2>{ __( 'Call to Action' ) }</h2>
              <p>{ __( 'This is really important!' ) }</p>
            </div>
          );
        },
    },
);
