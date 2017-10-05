var eskript_inList_defaultActive = true;

function eskript_isToggled( node , editor ) {
	var dom = editor.dom;

	if(dom.hasClass(node, "in-list")){
		return(true);
	}else if(dom.hasClass(node, "not-in-list")){
		return(false);
	}else{
		if(eskript_inList_defaultActive){
			dom.addClass(node, "in-list");
			return(true);
		}else{
			dom.addClass(node, "not-in-list");
			return(false);
		}
	}
};
function eskript_isElement(node){
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
};
function eskript_updateToolbar(node , editor) {
	alert('eskript_updateToolbar');
	inList = eskript_isToggled(node, editor) ? "eskript-toggleinlist" : "eskript-togglenotinlist";
	var button=editor.buttons['toggleinlist'];
	button.icon = inList;
};

function eskript_handleElement(node, dom , editor) {
	element = eskript_isElement(node);

	if ( node.nodeName === 'DIV' && dom.getParent( node, '#wp-list-toolbar' ) ) {
		element = dom.select( '[data-wp-listselect]' )[0];

		if ( element ) {
			editor.selection.select( element );

			// Handle Actions
			if ( dom.hasClass( node, 'list' ) ) {
				eskript_toggleList(element, editor);
			} else if ( dom.hasClass( node, 'ref' ) ) {
				eskript_getShortcode(element, editor);
			}
		}
	} else if ( element && ! dom.getAttrib( element, 'data-wp-listselect' ) ) {
		eskript_updateToolbar(element, editor);
	} else if ( !element ) {
		// no handled element, do nothing
	}
}

function eskript_toggleList( node , editor ){
	var dom = editor.dom;

	if(dom.hasClass(node, "in-list")){
		dom.addClass(node, "not-in-list");
		dom.removeClass(node, "in-list");
	}else if(dom.hasClass(node, "not-in-list")){
		dom.addClass(node, "in-list");
		dom.removeClass(node, "not-in-list");
	}else{
		if(eskript_inList_defaultActive){
			dom.addClass(node, "in-list");
		}else{
			dom.addClass(node, "not-in-list");
		}
	}
	
};

function eskript_getShortcode( node , editor ){
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
};

(function() {
    tinymce.create('tinymce.plugins.pbeskript', {
        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} editor Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */

		 
        init : function(editor, url) {
			toolbar: 'undo redo | pbeskript',	
				
			editor.on( 'keydown', function( event ) {
				var node, wrap, P, spacer,
					selection = editor.selection,
					keyCode = editor.keyCode,
					dom = editor.dom;

				// Key presses will replace the image so we need to remove the toolbar
				if ( event.ctrlKey || event.metaKey || event.altKey ||
					( keyCode < 48 && keyCode > 90 ) || keyCode > 186 ) {
					return;
				}
			});
				
			editor.addButton('toggleinlist', {
                title : 'Toggle In-List',
				icon: 'eskript-toggleinlist',
				cmd: 'toggleinlist'
            });
 
            editor.addButton('code', {
                title : 'Code',
				icon: 'eskript-code',
				cmd: 'code'
            });
			
			editor.addCommand("toggleinlist", function() {

				var node = editor.selection, dom = editor.dom;
				eskript_handleElement(node, dom, editor);
            });
			
			editor.on( 'mouseup', function(event) {
				var node = event.target, dom = editor.dom;
				// Don't trigger on right-click
				if ( event.button && event.button > 1 ) {
					return;
				}
				eskript_handleElement(node, dom, editor);
			});
			
			editor.addCommand("code", function(event) {
                alert('executing code');
				var node = editor.selection, dom = editor.dom;
            });
        },
 
        /**
         * Creates control instances based in the incomming name. This method is normally not
         * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
         * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
         * method can be used to create those.
         *
         * @param {String} n Name of the control to create.
         * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
         * @return {tinymce.ui.Control} New control instance or null if no control was created.
         */
        createControl : function(n, cm) {
            return null;
        },
 
        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo : function() {
            return {
                longname : 'pbeskript Buttons',
                author : 'Dominic Michel',
                authorurl : 'https://github.com/stepmuel/pressbooks-eskript',
                infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/example',
                version : "0.1"
            };
        }
    });
 
    // Register plugin
    tinymce.PluginManager.add( 'pbeskript', tinymce.plugins.pbeskript );
})();