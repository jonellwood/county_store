function addPopoverAutoClose(popoverId) {
	const popover = document.querySelector(popoverId);
	// console.log(popover);
	popover.addEventListener('mouseenter', () => {
		console.log('mouse enter detected');
		clearTimeout(popover.autoCloseTimeout);
	});

	popover.addEventListener('mouseleave', () => {
		console.log('mouse leave detected');
		popover.autoCloseTimeout = setTimeout(() => {
			popover.hidePopover();
		}, 500);
	});
}
