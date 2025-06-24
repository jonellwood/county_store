function sendToTypesToRemoveArray(val) {
	if (typesToRemove.includes(val)) {
		typesToRemove = typesToRemove.filter((x) => x !== val);
		console.log(typesToRemove);
	} else {
		typesToRemove.push(val);
		console.log(typesToRemove);
	}

	// find all elements with class of home-product-info and if they have data-types attribute equal to val add the class of hidden
	var elements = document.getElementsByClassName('card');
	console.log('Elements with types ');
	console.log(elements);
	// check if elements data-type attribute is in the array typesToRemove and hide if it is. show if it isn't
	for (var i = 0; i < elements.length; i++) {
		if (typesToRemove.includes(elements[i].getAttribute('data-type'))) {
			elements[i].classList.add('hidden');
			console.log('element: ' + elements[i] + 'has been hidden');
		} else {
			elements[i].classList.remove('hidden');
			console.log('element: ' + elements[i] + 'has been shown');
		}
	}
}
