/**
 * @since 1.0.1 Removed postboxes toggle
 * @since 1.0.0
 */

jQuery.noConflict();
jQuery(document).ready(function($){

    /**
     * Localized var from WP.
     *
     * @name cmb2OptTabs
     * @property {string} key
     * @property {string} posttype
     * @property {string} defaulttab
     */

    var slug        = cmb2OptTabs.key,
        posttype    = cmb2OptTabs.posttype,
        cls         = {
            toggle:     'opt-content',
            tab:        'opt-tab',
            defaulttab: 'opt-tab-' + cmb2OptTabs.defaulttab,
            hidden:     'opt-hidden',
            wpactive:   'nav-tab-active'
        },
        activetab   = '',
        page        = slug,
        $containers = $('.' + cls.toggle);

    // move metaboxes to correct tabs
    moveMetaboxes();

    // when page loads, make tab active
    tabSwitch();

    // show and hide tab content on tab clicks
    $( '.' + cls.tab ).on( 'click', function(e) {
        e.preventDefault();
        tabSwitch( $(this) );
    });

    /**
     * TAB SWITCH
     * Changes the tab content when tab is clicked.
     *
     * @param $tab
     *
     * @since 1.0.0
     */
    function tabSwitch( $tab ) {

        var id, content, tab;

        // if $tab was not set, get the tab from the query string
        if ( typeof($tab) === 'undefined' ) {
            tab = getQ('tab').length ? getQ('tab') : cls.defaulttab;
            $tab = $( '#' + tab );
        }

        // get id and content location from the chosen tab
        id = $tab.attr('id');
        content = $tab.data('optcontent');

        // hide all tabs and show current tab
        $( '.' + cls.toggle ).hide();
        $( content ).show();

        // remove and set the Wordpress active tab class on the tab navigation
        $( '.' + cls.tab ).removeClass( cls.wpactive );
        $( '#' + id ).addClass( cls.wpactive ).blur();

        // set the current active tab
        activetab = id;

        // change the browser URL to reflect the current tab; this allows Wordpress to change to this tab on save
        if ( history.pushState ) {

            var stateObject = { dummy: true},
                postvar = posttype ? 'post_type=' + posttype + '&' : '';

            var url = window.location.protocol
                + "//"
                + window.location.host
                + window.location.pathname
                + '?'
                + postvar
                + 'page=' + page
                + '&tab=' + id;

            history.pushState( stateObject, $(document).find('title').text(), url );
        }
    }

    /**
     * MOVE METABOXES
     * Moves metaboxes to their proper tabs. Each tab container has a data attribute "boxes" with a comma-delimited
     * string containing the metaboxes which belong within.
     *
     * @since 1.0.0
     */
    function moveMetaboxes() {
        $containers.each( function() {
            var $this = $(this),
                $sortable = $this.find('.meta-box-sortables'),
                boxes;
            boxes = $this.data( 'boxes' ).split( ',' );
            $.each( boxes, function(i,v) {
                $( '#' + v ).appendTo( $sortable ).removeClass( 'hide-if-js ' + cls.hidden );
            });
        });
    }

    /**
     * GETQ
     * Gets the query string.
     *
     * @param name
     * @returns {string}
     *
     * @since 1.0.0
     */
    function getQ( name ) {
        var regex, results;
        name = name.replace(/[\[]/,"\\[").replace(/[\]]/,"\\]");
        regex = new RegExp( "[\\?&]" + name + "=([^&#]*)" );
        results = regex.exec( location.search );
        return results === null ?
            "" : decodeURIComponent( results[1].replace( /\+/g," ") );
    }
});