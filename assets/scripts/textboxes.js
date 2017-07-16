/**
 * textboxes.js
 *
 * Copyright, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://www.tinymce.com/license
 * Contributing: http://www.tinymce.com/contributing
 */

tinymce.PluginManager.add('textboxes', function (editor) {
    'use strict';
    function showDialog() {
        var selectedNode = editor.selection.getNode();

        editor.windowManager.open({
            title: editor.getLang('strings.customtextbox'),
            body: {type: 'textbox', name: 'className', size: 40, label: editor.getLang('strings.classtitle'), value: selectedNode.name || selectedNode.id},
            onsubmit: function (e) {
                editor.execCommand('mceReplaceContent', false, '<div class="textbox ' + e.data.className + '">{$selection}</div>');
            }
        });
    }
    
    editor.addButton('textboxes', {
        type: 'menubutton',
        text: editor.getLang('strings.textboxes'),
        icon: false,
        menu: [
            { text: 'Formula', onclick: function () {
                var selection = editor.selection.getContent();
                if ( selection !== '' ) {
                    editor.execCommand('mceReplaceContent', false, '<div class="textbox tbformel">\n'+selection+'</div><p></p>');
                } else {
                    editor.execCommand('mceInsertContent', 0, '<div class="textbox tbformel"><p>Formula</p>\n</div><p></p>');
                }
            } },
            { text: 'How To', onclick: function () {
                var selection = editor.selection.getContent();
                if ( selection !== '' ) {
                    editor.execCommand('mceReplaceContent', false, '<div class="textbox tbhowto"><h1 class="not-in-list">How To</h1>\n<p>'+selection+'</p>\n</div><p></p>');
                } else {
                    editor.execCommand('mceInsertContent', 0, '<div class="textbox tbhowto"><h1 class="not-in-list">How To: </h1>\n<p>Text</p>\n</div><p></p>');
                }
            } },
            { text: 'Definition', onclick: function () {
                var selection = editor.selection.getContent();
                if ( selection !== '' ) {
                    editor.execCommand('mceReplaceContent', false, '<div class="textbox tbdefinition">\n'+selection+'</div><p></p>');
                } else {
                    editor.execCommand('mceInsertContent', 0, '<div class="textbox tbdefinition">\n<p>Definition</p>\n</div><p></p>');
                }
            } },
            { text: 'Example', onclick: function () {
                var selection = editor.selection.getContent();
                if ( selection !== '' ) {
                    editor.execCommand('mceReplaceContent', false, '<div class="textbox tbbeispiel"><h1 class="not-in-list">Example</h1>\n<p>'+selection+'</p>\n</div><p></p>');
                } else {
                    editor.execCommand('mceInsertContent', 0, '<div class="textbox tbbeispiel"><h1 class="not-in-list">Example</h1>\n<p>Text</p>\n</div><p></p>');
                }
            } },
            { text: 'Question', onclick: function () {
                var selection = editor.selection.getContent();
                if ( selection !== '' ) {
                    editor.execCommand('mceReplaceContent', false, '<div class="textbox tbfragen"><h1 class="not-in-list">Question</h1>\n<p>'+selection+'</p>\n</div><p></p>');
                } else {
                    editor.execCommand('mceInsertContent', 0, '<div class="textbox tbfragen"><h1 class="not-in-list">Question</h1>\n<p>Text</p>\n</div><p></p>');
                }
            } },
            { text: 'Reference', onclick: function () {
                var selection = editor.selection.getContent();
                if ( selection !== '' ) {
                    editor.execCommand('mceReplaceContent', false, '<div class="textbox tbverweis">\n'+selection+'</div><p></p>');
                } else {
                    editor.execCommand('mceInsertContent', 0, '<div class="textbox tbverweis">\n<p>Reference</p>\n</div><p></p>');
                }
            } },
            { text: 'Excursus', onclick: function () {
                var selection = editor.selection.getContent();
                if ( selection !== '' ) {
                    editor.execCommand('mceReplaceContent', false, '<div class="textbox tbexkurs"><h1 class="not-in-list">Excursus</h1>\n<p>'+selection+'</p>\n</div><p></p>');
                } else {
                    editor.execCommand('mceInsertContent', 0, '<div class="textbox tbexkurs"><h1 class="not-in-list">Excursus</h1>\n<p>Text</p>\n</div><p></p>');
                }
            } },
            { text: 'Export Only', onclick: function () {
                var selection = editor.selection.getContent();
                if ( selection !== '' ) {
                    editor.execCommand('mceReplaceContent', false, '<div class="not-web textbox tbexkurs"><h1 class="not-in-list">Missing Content</h1>\n<p>'+selection+'</p>\n</div><p></p>');
                } else {
                    editor.execCommand('mceInsertContent', 0, '<div class="not-web textbox tbexkurs"><h1 class="not-in-list">Missing Content</h1>\n<p>Visit <a href="https://eskript.ethz.ch/" target="_blank">eskript.ethz.ch</a> to see everything.</p>\n</div><p></p>');
                }
            } },
        ]
    });

});
