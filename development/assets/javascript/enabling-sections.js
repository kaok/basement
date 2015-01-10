// Provide functionality to enable and disable sections
if ((enablingSectionsTriggers = $('.basement_section_enabling')).length) {
	enablingSectionsTriggers.change(function() {
		var sectionName = $(this).data('section'),
			enabledOn = $(this).data('enable-section-on'),
			section,
			show = false;

		if (sectionName && (section = $('.basement_enabling_section[data-section="' + sectionName + '"]')).length) {
			if (enabledOn == 'checked') {
				show = $(this).is(':' + enabledOn);
			}
			if (show) {
				section.removeClass('basement_section_hidden');
			} else {
				section.addClass('basement_section_hidden');
			}
		}
		createCodeEditors();
		$(this).trigger('basement_content_changed');
	});
}