function createPopoverButton(popover, text) {
	var button = `
    <button class="button" popovertarget="${popover}" popovertargetaction="show">
            ${text}   
    </button>`;
	return button;
}
