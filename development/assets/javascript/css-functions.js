// Useful fucntion for CSS proceeding
function getBackgroundCSSPosition(element) {
	var pos = $(element).css('background-position').split(' ');
	if(pos[0] === 'left') {
		return {
			x: pos[1],
			y: pos[3]
		};
	} else {
		return {
			x: pos[0],
			y: pos[1]
		};
	}
}