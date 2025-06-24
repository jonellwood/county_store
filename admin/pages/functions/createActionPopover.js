function createActionPopover(id, action) {
	commentData = getComments(id);
	let formAction;
	if (action === 'comment') {
		formAction = 'add-comment-noaction.php';
	} else {
		formAction = 'set-request-status.php';
	}
	let status;
	if (action === 'approve') {
		status = 'Approved';
	} else if (action === 'deny') {
		status = 'Denied';
	} else if (action === 'receive') {
		status = 'Received';
	} else if (action === 'comment') {
		status = 'comment';
	} else {
		throw new Error('Invalid action: ' + action);
	}

	let html = `
        <div class="action-popover" id="action-popover">
            <button class="btn btn-close" popovertarget="action-jackson" popovertargetaction="hide">
                <span aria-hidden="true"></span>
                <span class="sr-only"></span>  
            </button>
            <h3 class='cap'> Adding ${action} to request # ${id} </h3>
            <form action="${formAction}" method="POST">
                <input type="hidden" name="id" value="${id}">`;
                if (action !== 'comment') {
                    html += `<input type="hidden" name="status" value="${status}">`;
                }
	        html +=`
                <label for="comment">Comment</label><br />
                <textarea class="comment" id="comment" name="comment" cols="102" rows="3"></textarea>
                <br/>
                <button type="submit" class="btn btn-success cap" onclick="logAction({
                    init_id: ${id},
                    event_desc: 'Status Change to ${status}',
                    assoc_order_details_id: ${id},
                    assoc_order_id: ,
                    event_type_id: 1,
                })">${action}</button> 
            </form>
            <p id="display-comments"></p>
        </div>
        `;
	document.getElementById('action-jackson').innerHTML = html;
}
