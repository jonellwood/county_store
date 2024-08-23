function logAction(data) {
	console.log('logging data');
	console.log(data);
	fetch('..pages/API/addToEventLog.php', {
		method: 'POST',
		body: JSON.stringify(data),
	});
}

// logAction({
// 	id: 1,
// 	event: 'event1',
// 	assocOrderDetailsId: 2,
// 	assocOrderId: 3,
// 	eventType: 'eventType1',
// });
