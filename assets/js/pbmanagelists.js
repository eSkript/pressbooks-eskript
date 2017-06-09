/**
 * Tinymce Plugin for the Lists Module
 *
 * Enhances the Tinymce editor with the lists toolbar
 */


tinymce.PluginManager.add( 'pbmanagelists', function( editor ) {
    var toolbarActive = false;
    var defaultActive = true;

    /**
     * Checks if a HTMLElement is in the list or not
     * @param HTMLElement node the Element to be checked
     * @return boolean
     */

    function isToggled( node ) {
        var dom = editor.dom;

        if(dom.hasClass(node, "in-list")){
            return(true);
        }else if(dom.hasClass(node, "not-in-list")){
            return(false);
        }else{
            if(defaultActive){
                dom.addClass(node, "in-list");
                return(true);
            }else{
                dom.addClass(node, "not-in-list");
                return(false);
            }
        }
    }

    /**
     * Changes the in list status
     * @param HTMLElement node the Element to be changed
     */

    function toggleList( node ){
        var dom = editor.dom;

        if(dom.hasClass(node, "in-list")){
            dom.addClass(node, "not-in-list");
            dom.removeClass(node, "in-list");
        }else if(dom.hasClass(node, "not-in-list")){
            dom.addClass(node, "in-list");
            dom.removeClass(node, "not-in-list");
        }else{
            if(defaultActive){
                dom.addClass(node, "in-list");
            }else{
                dom.addClass(node, "not-in-list");
            }
        }
        addToolbar(node);
    }

    /**
     * Prompts window with the reference shortcode
     * @param HTMLElement node the Element to show the shortcode of
     */

    function getShortcode( node ){
        var dom = editor.dom;

        if(!dom.getAttrib(node, "ID")){
            var randId = 'y'; // Must start with non-number
            var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
            for(var i=0; i < 12; i++) {
                randId += possible.charAt(Math.floor(Math.random() * possible.length));
            }
            dom.setAttrib(node, "ID", randId);
        }

        window.prompt("Copy to clipboard: Ctrl+C, Enter", '[ref id="'+dom.getAttrib(node, "ID")+'"/]');
    }

    /**
     * Edite the caption of a HTMLElement shows prompt
     * @param HTMLElement node the element edit the caption
     */

    function editCaption( node ){
        var dom = editor.dom;
        if(node.nodeName == "TABLE"){
            var caption = node.getElementsByTagName('caption');
            if(caption.length > 0){
                caption = caption[0];
            }else{
                caption = dom.create( 'caption', {}, "" );
                if(node.firstChild) node.insertBefore(caption,node.firstChild);
                else node.appendChild(caption);
            }
            var ctext = window.prompt("Caption", caption.textContent);
            if(ctext != null){
                if(ctext == ""){
                    node.removeChild(caption);
                }else{
                    caption.textContent = ctext;
                }
            }
        }
    }

    /**
     * Adds the list toolbar to an element
     * @param HTMLElement node the element the toolbar should be added
     */

    function addToolbar( node ) {
        var rectangle, toolbarHtml, toolbar, left, inList,
            dom = editor.dom;

        removeToolbar();

        // Don't add to placeholders
        if ( ! node || !isElement(node) ) {
            return;
        }

        dom.setAttrib( node, 'data-wp-listselect', 1 );
        rectangle = dom.getRect( node );

        inList = isToggled(node) ? "pblists-icon-in-list" : "pblists-icon-not-in-list";

        toolbarHtml = '<div class="dashicons list '+inList+'" data-mce-bogus="1"></div>' +
            '<div class="dashicons dashicons-editor-code ref" data-mce-bogus="1"></div>';

        if(node.nodeName == "TABLE"){
            toolbarHtml += '<div class="dashicons dashicons-edit caption" data-mce-bogus="1"></div>';
        }

        toolbar = dom.create( 'div', {
            'id': 'wp-list-toolbar',
            'class': 'wp-list-toolbar-'+node.nodeName,
            'data-mce-bogus': '1',
            'contenteditable': false
        }, toolbarHtml );

        if ( editor.rtl ) {
            left = rectangle.x + rectangle.w - 82;
        } else {
            left = rectangle.x;
        }

        editor.getBody().appendChild( toolbar );
        dom.setStyles( toolbar, {
            top: rectangle.y+rectangle.h,
            left: left
        });

        toolbarActive = true;
    }

    /**
     * Removes the list toolbar
     */

    function removeToolbar() {
        var toolbar = editor.dom.get( 'wp-list-toolbar' );

        if ( toolbar ) {
            editor.dom.remove( toolbar );
        }

        editor.dom.setAttrib( editor.dom.select( '[data-wp-listselect]' ), 'data-wp-listselect', null );

        toolbarActive = false;
    }

    /**
     * Checks if Element can have the toolbar
     * @param HTMLElement node the element that should be checked
     * @return HTMLElement|false the passed element, a parent element meeting the criteria or false
     */

    function isElement(node){
        var elements = ["TABLE", "H1", "H2", "H3", "H4", "H5", "H6", "IMG"];
        if(elements.indexOf(node.nodeName) > -1){
            return(node);
        }else{
            // search for parent node
            while(node.parentNode){
                node = node.parentNode;
                if(elements.indexOf(node.nodeName) > -1){
                    return(node);
                }
            }
        }
        return false;
    }

    editor.on( 'BeforeExecCommand', function( event ) {
        var node, p, DL, align,
            cmd = event.command,
            dom = editor.dom;

            removeToolbar();
    });

    editor.on( 'keydown', function( event ) {
        var node, wrap, P, spacer,
            selection = editor.selection,
            keyCode = event.keyCode,
            dom = editor.dom;

        // Key presses will replace the image so we need to remove the toolbar
        if ( toolbarActive ) {
            if ( event.ctrlKey || event.metaKey || event.altKey ||
                ( keyCode < 48 && keyCode > 90 ) || keyCode > 186 ) {
                return;
            }

            removeToolbar();
        }
    });

    editor.on( 'mousedown', function( event ) {

        var node = event.target, dom = editor.dom, element;

        element = isElement(node);

        if ( !element && !dom.getParent( node, '#wp-list-toolbar' )) {
            removeToolbar();
        }
    });

    editor.on( 'mouseup', function( event ) {
        var node = event.target,
            dom = editor.dom,
            element;

        // Don't trigger on right-click
        if ( event.button && event.button > 1 ) {
            return;
        }
        element = isElement(node);

        if ( node.nodeName === 'DIV' && dom.getParent( node, '#wp-list-toolbar' ) ) {
            element = dom.select( '[data-wp-listselect]' )[0];

            if ( element ) {
                editor.selection.select( element );

                // Handle Actions
                if ( dom.hasClass( node, 'list' ) ) {
                    toggleList(element);
                } else if ( dom.hasClass( node, 'ref' ) ) {
                    getShortcode(element);
                } else if ( dom.hasClass( node, 'caption' ) ){
                    editCaption(element);
                }
            }
        } else if ( element && ! dom.getAttrib( element, 'data-wp-listselect' ) ) {
            addToolbar( element );
        } else if ( !element ) {
            removeToolbar();
        }
    });

    editor.on( 'cut', function() {
        removeToolbar();
    });

    editor.on( 'PostProcess', function( event ) {
        if ( event.get ) {
            // Remove elements just used for the editor
            event.content = event.content.replace( / data-wp-listselect="1"/g, '' );
        }
    });
    /*editor.onBeforeGetContent.add(function(ed, o) {
        // Output the element name
        console.debug("onBeforeGetContent");
    });*/
});