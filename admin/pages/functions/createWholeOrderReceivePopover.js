function createWholeOrderReceivePopover(id) {
  //   console.log("making popover for " + id);
  let html = `
    
        <p>This will <mark class="approve">receive</mark> <b>every item</b> listed
            in this order. Receive
            specific lines items in this
            request by selecting the individual line item.</p>
        <p>Contact the Help Desk regarding any changes to this request.</p>
        <br>
        <div id="receive-popover-btns">
            <button class='approve receive-btn' onclick='receiveWholeOrder(${id})'>Receive All Items</button>
            <button class=' deny receive-btn' popovertarget="receive-whole-order-confirm" popovertargetaction="hide">Nope, I am out of here</button>
        </div>

    `;
  //   console.log(html);
  var target = document.getElementById("whole-order-receive-popover-values");
  //   console.log(target);
  target.innerHTML = html;
}
