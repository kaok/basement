// Creates code editors for visible textareas
var codeEditorOptions = {
	'text/html': {
		lineNumbers: true,
		matchBrackets: true,
		mode: "text/html",
		indentUnit: 4,
		indentWithTabs: true
	},
	'text/javascript': {
		lineNumbers: true,
		matchBrackets: true,
		mode: "text/javascript",
		indentUnit: 4,
		indentWithTabs: true
	},
	'text/x-php': {
		lineNumbers: true,
		matchBrackets: true,
		mode: "text/x-php",
		indentUnit: 4,
		indentWithTabs: true
	},
	'text/css': {
		lineNumbers: true,
		matchBrackets: true,
		mode: "text/css",
		indentUnit: 4,
		indentWithTabs: true
	}
};

function createCodeEditors() {
	if ((codeTextareas = $('.basement_code_editor')).length) {
		$.each(codeTextareas, function(index, codeTextarea) {
			if ($(codeTextarea).is(':visible')) {
				var mode = $(codeTextarea).data('editor-mode');
				if (!mode) {
					return;
				}
				$(codeTextarea).data('code-editor', CodeMirror.fromTextArea(codeTextarea, codeEditorOptions[mode]));
				$(codeTextarea).data('code-editor').on('blur', function() {
					$(codeTextarea).data('code-editor').save();
				});
			} else {
				if ($(codeTextarea).data('code-editor')) {
					if ($(codeTextarea).siblings('.CodeMirror').is(':visible')) {
						return;
					} 
					$(codeTextarea).data('code-editor', '').show();
					$(codeTextarea).siblings('.CodeMirror').remove();
				}
			}
		});
	}
}

createCodeEditors();