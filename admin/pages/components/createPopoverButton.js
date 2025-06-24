function createPopoverButton(popover, text) {
	var button = `
    <button class="btn btn-outline-dark" popovertarget="${popover}" popovertargetaction="show">
            ${text}   
    </button>`;
	return button;
}
