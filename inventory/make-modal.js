// saving this js function in case I need it again later




// this function creates the modal on demand with params for the specific piece of inventory and then shows the modal.
intended to be intiated with onclick of the update button
function makeModal(uid) {
const html = `
<bx-modal id="${uid}">
    <bx-modal-header>
        <button id="close-modal" value="${uid}">Close</button>
        <bx-modal-label>Assign, re-assign, return, or remove inventory</bx-modal-label>
        <bx-modal-heading>Inventory Management Modal</bx-modal-heading>
    </bx-modal-header>
    <bx-modal-body>
        <div id="left">
            <h5>Assign Item to an employee</h5>
            <form name="assign" id="assign" method="post" action="assign-to-emp-in-db.php">
                <label for "emp_pick_list">Employee: </label>
                <select name="emp_pick_list" id="emp_pick_list" title="Employee Number" form_id="assign"></select>
                <input name="inv_UID" type="hidden" id="uid-holder" />
                <button class="btn btn-primary" id="assign-btn" type="submit">Assign to Employee</button>
            </form>
        </div>
        <div id="center">
            <h5>Mark Item as returned from employee</h5>
            <form name="return" id="return" method="post" action="return-from-emp-in-db.php">
                <input name="inv_UID" type="hidden" id="uid-holder2" />
                <button class="btn btn-info" id="return-btn" type="submit">Return Item to Inventory</button>
            </form>
        </div>
        <div id="right">
            <h5>Mark item as destroyed</h5>
            <form name="destroy" id="destroy" method="post" action="mark-as-destroyed-in-db.php">
                <input name="inv_UID" type="hidden" id="uid-holder3" />
                <button class="btn btn-danger" id="return-btn" type="submit">Mark Item as Destroyed</button>
            </form>
        </div>
    </bx-modal-body>
</bx-modal>
`;
// Get the element of the close button from the current active modal
document.getElementById("modal-holder").innerHTML = html;

// Add an event listener to listen to the click event on the close button
// Upon clicking, retrieve the element containing the passed unique id and remove it from the DOM
const closeButton = document.getElementById('close-modal');
closeButton.addEventListener('click', () => {
const modal = document.getElementById(uid);
modal.remove();
});
}