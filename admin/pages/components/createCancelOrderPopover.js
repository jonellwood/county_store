const createCancelOrderPopover = (id) => {
	let html = `
    <button class="btn btn-close" popovertarget="cancel-confirm" popovertargetaction="hide">
        <span aria-hidden="true"></span>
        <span class="sr-only"></span>
    </button>
    <h3 p-2 m-2> Are you sure you want to cancel this request? It can not be undone.</h3>
    <div class='cancel-btn-holder d-flex justify-content-center gap-5'>
    <button id='cancel-button' class="btn btn-danger" onclick="cancelOrder('${id}')">Cancel Request</button>
    <button id='update-button' class="btn btn-success" popovertarget="cancel-confirm" popovertargetaction="hide">Nevermind. Keep the request.</button>
    </div>`;
	document.getElementById('cancel-confirm').innerHTML = html;
};
