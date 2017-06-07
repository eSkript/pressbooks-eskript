(function() {
    tinymce.create('tinymce.plugins.textboxbuttons', {
        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         *
         * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init: function(ed, url) {
            ed.addButton('tbformel', {
                title: 'Formula Box',
                cmd: 'tbformel',
                image: url + '/tbformel.png'
            });

            ed.addCommand('tbformel', function() {
                var selected_text = ed.selection.getContent();
                var return_text = '';
                if(selected_text != ''){
                    return_text = '<div class="textbox tbformel">\n\
					' + selected_text + '</div>';
                } else {
                    return_text = '<div class="textbox tbformel">\n\
					<p>Formula</p>\n\
					</div>';
                }
                ed.execCommand('mceInsertContent', 0, return_text);
            });
            ed.addButton('tbhowto', {
                title: 'How To Box',
                cmd: 'tbhowto',
                image: url + '/tbhowto.png'
            });

            ed.addCommand('tbhowto', function() {
                var selected_text = ed.selection.getContent();
                var return_text = '';
                if(selected_text != ''){
                    return_text = '<div class="textbox tbhowto">\n\
					' + selected_text + '</div>';
                } else {
                    return_text = '<div class="textbox tbhowto">\n\
                    <h1 class="not-in-list">How To: </h1>\n\
					<p>Text</p>\n\
					</div>';
                }
                ed.execCommand('mceInsertContent', 0, return_text);
            });
            ed.addButton('tbdefinition', {
                title: 'Definition Box',
                cmd: 'tbdefinition',
                image: url + '/tbdefinition.png'
            });

            ed.addCommand('tbdefinition', function() {
                var selected_text = ed.selection.getContent();
                var return_text = '';
                if(selected_text != ''){
                    return_text = '<div class="textbox tbdefinition">\n\
					' + selected_text + '</div>';
                } else {
                    return_text = '<div class="textbox tbdefinition">\n\
					<p>Definition</p>\n\
					</div>';
                }
                ed.execCommand('mceInsertContent', 0, return_text);
            });
            ed.addButton('tbbeispiel', {
                title: 'Example Box',
                cmd: 'tbbeispiel',
                image: url + '/tbbeispiel.png'
            });

            ed.addCommand('tbbeispiel', function() {
                var selected_text = ed.selection.getContent();
                var return_text = '';
                if(selected_text != ''){
                    return_text = '<div class="textbox tbbeispiel">\n\
					' + selected_text + '</div>';
                } else {
                    return_text = '<div class="textbox tbbeispiel">\n\
                    <h1 class="not-in-list">Example: </h1>\n\
					<p>Text</p>\n\
					</div>';
                }
                ed.execCommand('mceInsertContent', 0, return_text);
            });
            ed.addButton('tbfragen', {
                title: 'Question Box',
                cmd: 'tbfragen',
                image: url + '/tbfragen.png'
            });

            ed.addCommand('tbfragen', function() {
                var selected_text = ed.selection.getContent();
                var return_text = '';
                if(selected_text != ''){
                    return_text = '<div class="textbox tbfragen">\n\
					' + selected_text + '</div>';
                } else {
                    return_text = '<div class="textbox tbfragen">\n\
                    <h1 class="not-in-list">Question: </h1>\n\
					<p>Text</p>\n\
					</div>';
                }
                ed.execCommand('mceInsertContent', 0, return_text);
            });
            ed.addButton('tbverweis', {
                title: 'Reference Box',
                cmd: 'tbverweis',
                image: url + '/tbverweis.png'
            });

            ed.addCommand('tbverweis', function() {
                var selected_text = ed.selection.getContent();
                var return_text = '';
                if(selected_text != ''){
                    return_text = '<div class="textbox tbverweis">\n\
					' + selected_text + '</div>';
                } else {
                    return_text = '<div class="textbox tbverweis">\n\
					<p>Reference</p>\n\
					</div>';
                }
                ed.execCommand('mceInsertContent', 0, return_text);
            });
            ed.addButton('tbexkurs', {
                title: 'Excursus Box',
                cmd: 'tbexkurs',
                image: url + '/tbexkurs.png'
            });

            ed.addCommand('tbexkurs', function() {
                var selected_text = ed.selection.getContent();
                var return_text = '';
                if(selected_text != ''){
                    return_text = '<div class="textbox tbexkurs">\n\
					' + selected_text + '</div>';
                } else {
                    return_text = '<div class="textbox tbexkurs">\n\
                    <h1 class="not-in-list">Excursus: </h1>\n\
					<p>Text</p>\n\
					</div>';
                }
                ed.execCommand('mceInsertContent', 0, return_text);
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
        createControl: function(n, cm) {
            return null;
        }

    });

    // Register plugin
    tinymce.PluginManager.add('textboxbuttons', tinymce.plugins.textboxbuttons);

})();