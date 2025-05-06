/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import Alpine from 'alpinejs';
import yaml from 'js-yaml';

import './styles/app.scss';

window['AceToolbar'] = {
    toggleFormatJson(editorId) {
        const el = document.getElementById(editorId);
        let isOneLine = (el.dataset.isOneLine === 'true');
        let editor = ace.edit(editorId);
        try {
            let content = editor.getValue();
            let formatted_content;
            if (isOneLine) {
                formatted_content = JSON.stringify(JSON.parse(content), null, 4);
                editor.getSession().setUseWrapMode(false);
            } else {
                formatted_content = JSON.stringify(JSON.parse(content));
                editor.getSession().setUseWrapMode(true);
            }
            editor.setValue(formatted_content, -1);
            el.dataset.isOneLine = !isOneLine;
        } catch (e) {
            alert("Invalid JSON");
        }
    },
    setEditorMode(editorId, mode) {
        ace.edit(editorId).session.setMode(`ace/mode/${mode}`);
    },
    saveContent(editorId, localStorageKey) {
        let content = ace.edit(editorId).getValue();
        localStorage.setItem(localStorageKey, content);
    },
    toggleFullScreen(editorId) {
        let editor = document.getElementById(editorId);
        if (!document.fullscreenElement) {
            editor.requestFullscreen().catch(err => {
                alert(`Error attempting to enable full-screen mode: ${err.message} (${err.name})`);
            });
        } else {
            document.exitFullscreen();
        }
    },
    toggleLineWrap(editorId) {
        let editor = ace.edit(editorId);
        let wrap = editor.getSession().getUseWrapMode();
        editor.getSession().setUseWrapMode(!wrap);
    },
    copyToClipboard(editorId) {
        let content = ace.edit(editorId).getValue();
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(content).then(() => {
                alert("Content copied to clipboard");
            }).catch(err => {
                alert("Failed to copy content: " + err);
            });
        } else {
            // Fallback for browsers that do not support navigator.clipboard
            let textarea = document.createElement("textarea");
            textarea.value = content;
            textarea.style.position = "fixed"; // Prevent scrolling to bottom of page
            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();
            try {
                document.execCommand("copy");
                alert("Content copied to clipboard");
            } catch (err) {
                alert("Failed to copy content: " + err);
            }
            document.body.removeChild(textarea);
        }
    }
}

window['Alpine'] = Alpine;
Alpine.start();

// expose js-yaml as a global variable
window.jsyaml = yaml;