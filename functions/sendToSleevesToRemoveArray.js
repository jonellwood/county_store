function sendToSleevesToRemoveArray(val) {
	if (sleevesToRemove.includes(val)) {
		sleevesToRemove = sleevesToRemove.filter((x) => x !== val);
		console.log(sleevesToRemove);
	} else {
		sleevesToRemove.push(val);
		console.log(sleevesToRemove);
	}

	// find all elements with class of home-product-info and if they have data-sleeves attribute equal to val add the class of hidden
	var elements = document.getElementsByClassName('card');
	console.log('Elements with sleeves ');
	console.log(elements);
	// check if elements data-sleeves attribute is in the array sleevesToRemove and hide if it is. show if it isn't
	for (var i = 0; i < elements.length; i++) {
		if (sleevesToRemove.includes(elements[i].getAttribute('data-sleeve'))) {
			elements[i].classList.add('hidden');
			console.log('element: ' + elements[i] + 'has been hidden');
		} else {
			elements[i].classList.remove('hidden');
			console.log('element: ' + elements[i] + 'has been shown');
		}
	}
}
