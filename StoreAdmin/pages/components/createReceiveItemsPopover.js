createReceiveItemsPopover = (data) => {
	const buttonsHtml = `
            <div class="buttons-in-approval-popover-holder">
                <button class="btn btn-approve" value="${order_id}" onclick="receiveWholeOrder(this.value)">Receive All</button>
                <button class="deny" popovertarget="receive-whole-order-confirm" popovertargetaction="hide">Nope, I'm out of here</button>
            </div>
        `;
	document.getElementById('receive-popover-btns').innerHTML = buttonsHtml;
};
