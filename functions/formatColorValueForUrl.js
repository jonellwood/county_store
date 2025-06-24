function formatColorValueForUrl(str) {
	var noSpaces = str.replace(/[\s/]/g, '');
	var lowercaseString = noSpaces.toLowerCase();
	return lowercaseString;
}
