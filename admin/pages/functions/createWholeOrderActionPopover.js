function createWholeOrderActionPopover(id) {
	let html = `
    <div class="action-popover" id="action-popover">
            <button class="btn btn-close" popovertarget="action-jackson" popovertargetaction="hide">
                <span aria-hidden="true"></span>
                <span class="sr-only"></span>  
            </button>
            <h3 class='cap'> This will approve or deny every item in request # ${id} </h3>
            <div class='btn-holder-in-popover'>
            <button type="button" class="btn btn-approve cap" onclick="approveWholeOrder(${id})">Approve</button> 
            <button type="button" class="btn btn-deny cap" onclick="denyWholeOrder(${id})">Deny</button> 
            </div>
        </div>
    `;
	document.getElementById('action-jackson').innerHTML = html;
}
