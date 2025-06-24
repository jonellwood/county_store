function createAlertPopover(msg, redirect) {
	let html = `

        
            <button class="btn btn-close" popovertarget="alert-popover" popovertargetaction="hide">
                <span aria-hidden="true"></span>
                <span class="sr-only"></span>  
            </button>
            <h3 class='cap'> ${msg} </h3>
            <button class="btn btn-success cap" onclick="window.location.href = '${redirect}'">OK</button>
       

    `;
	document.getElementById('alert-popover').innerHTML = html;
	showPopover();
}

// <div class="alert-popover" id="alert-popover" popover=manual></div>
//  </div>
