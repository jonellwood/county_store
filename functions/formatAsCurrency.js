function formatAsCurrency(value) {
	return Intl.NumberFormat('en-US', {
		style: 'currency',
		currency: 'USD',
	}).format(value);
}
