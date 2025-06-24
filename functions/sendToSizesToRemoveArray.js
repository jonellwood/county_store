function sendToSizesToRemoveArray(val) {
	if (sizesToRemove.includes(val)) {
		sizesToRemove = sizesToRemove.filter((x) => x !== val);
		console.log(sizesToRemove);
	} else {
		sizesToRemove.push(val);
		console.log(sizesToRemove);
	}
	// find all elements with class of card and if they have data-sizes attribute equal to val add the class of hidden
	var elements = document.getElementsByClassName('card');
	console.log('Elements with sizes ');
	console.log(elements);
	// check if elements data-size attribute is in the array sizesToRemove and hide if it is. show if it isn't
	for (var i = 0; i < elements.length; i++) {
		if (sizesToRemove.includes(elements[i].getAttribute('data-size'))) {
			elements[i].classList.add('hidden');
			console.log('element: ' + elements[i] + 'has been hidden');
		} else {
			elements[i].classList.remove('hidden');
			console.log('element: ' + elements[i] + 'has been shown');
		}
	}
}
