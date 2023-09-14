import {Button, Dropdown, ExternalLink, TextControl} from '@wordpress/components';
import {close, Icon} from '@wordpress/icons';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';

import {getWindowData} from '@givewp/form-builder/common';
import {cleanForSlug, safeDecodeURIComponent} from '@wordpress/url';
import {useCallback, useState} from '@wordpress/element';

const {
    formPage: {isEnabled, permalink, rewriteSlug},
} = getWindowData();

/**
 * @since 3.0.0
 * @see https://github.com/WordPress/gutenberg/blob/a8c5605f5dd077a601aefce6f58409f54d7d4447/packages/editor/src/components/post-slug/index.js
 */
const PageSlugControl = () => {
    const {
        settings: {pageSlug},
    } = useFormState();
    const dispatch = useFormStateDispatch();
  const [editedSlug, setEditedSlug] = useState(safeDecodeURIComponent(pageSlug));

  const updateSlug = useCallback(() => {
      const cleanEditedSlug = cleanForSlug(editedSlug);
      setEditedSlug(cleanEditedSlug);

      if (cleanEditedSlug !== pageSlug) {
          dispatch(setFormSettings({pageSlug: cleanEditedSlug}));
      }
  }, [pageSlug, editedSlug, dispatch]);

  return (
      !!isEnabled && (
          <Dropdown
              className="my-container-class-name"
              contentClassName="givewp-sidebar-dropdown-content"
              popoverProps={{placement: 'bottom-start'}}
              focusOnMount={'container'}
              renderToggle={({isOpen, onToggle}) => (
                  <TextControl
                      label={'URL'}
                      value={'/donations/' + editedSlug}
                      onChange={() => null}
                      onClick={onToggle}
                      aria-expanded={isOpen}
                  />
              )}
              renderContent={({onClose}) => (
                  <div style={{minWidth: 'calc(var(--givewp-sidebar-width) - 48px)'}}>
                      <div style={{display: 'flex', justifyContent: 'space-between', alignItems: 'center'}}>
                          <strong style={{fontSize: '14px'}}>{'URL'}</strong>
                          <Button onClick={onClose}>
                              <Icon icon={close} size={14}></Icon>
                          </Button>
                      </div>
                      <TextControl
                          label={'Permalink'}
                          value={editedSlug}
                          autoComplete="off"
                          spellCheck="false"
                          onChange={(newPageSlug) => {
                              setEditedSlug(newPageSlug);
                          }}
                          help={'The last part of the URL.'}
                          onBlur={() => updateSlug()}
                      />
                      <div>
                          <strong style={{fontSize: '14px'}}>View Page</strong>
                      </div>
                      <ExternalLink href={permalink} style={{fontSize: '14px'}}>
                          {sprintf('%s/%s', rewriteSlug, editedSlug)}
                      </ExternalLink>
                  </div>
              )}
          />
      )
  );
};

export default PageSlugControl;

export {isEnabled as isFormPageEnabled, PageSlugControl};
