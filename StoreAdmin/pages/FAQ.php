<?php include include "components/commonHead.php"; ?>



<script src="./functions/helpers.js"></script>
<script type="module">
import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@11/dist/mermaid.esm.min.mjs';
mermaid.initialize({
    startOnLoad: true
});
</script>

<script>
function renderFAQ() {
    var html = `
        <div class='faq'>
        <div class='faq-title'>Frequently Asked Questions</div>
        <hr>
        <details>
            <summary>What is the process for making requests?</summary>
            <p class='mermaid'>
                flowchart TD
                A[User Submits Request] -->|Approver Account created| B(Approver Log In)
                B --> D{Request is Denied}
                D -->X[User notifed via Email]
                B --> E{No action taken}
                E --> Z[Request auto expires after 90 days]
                B --> C{Request is Approved}
                C -->|More Dept Request pending| N[Wait ] 
                C -->K[All Dept requests Approved / Denied]
                K-->I[Notify Store you're ready to order via button]
                I-->J[Store receives notfiction]
                J-->L[Store generates order for dept managers approval]
                L-->M[Order is approved by Department]
                M-->O{Order is submitted to Vendor}
            </p>
        </details>    
        <details>
            <summary>How are the invoices paid?</summary>
            <p>The vendor submits the invoice directly to the store. The Store Admin staff will submit the invoice for payment once confirmation is received from the department that all items are received and correct.</p>
        </details>
        <details>
        <summary>How do I mark items as received?</summary>
            <p>In the same interface where you approved and denied requests, you will mark the items as received. Once the invoice report shows all items are received, the invoice is sent to finance and billed against the 'bill-to-department' value for the order. The default department is the department the employee is assigned. This can be edited by you in the <code>edit-request</code> page BEFORE the order is sent to the vendor. If you need to make a change after that point, please contact the store.
        </details>
        </div>
        `
    return html;
}
document.getElementById('details').innerHTML = renderFAQ();
</script>







<?php include "../../footer.php" ?>
</body>

</html>
<script>
function displayAlert() {
    var fyData = fiscalYear();
    var html = '';
    html +=
        `<div class="info-banner">
    You can email store@berkeleycountysc.gov at any time to get  answers.</div>`
    document.getElementById('alert-banner').innerHTML = html
}
displayAlert();
</script>
<style>
.div2 {
    max-width: 5px !important;
    border: none !important;
}

.div3 {
    grid-area: 2/2/3/4 !important;
    border: none !important;

    /* background-color: hotpink; */
    .faq {
        margin: 20px;
        width: 100%;
    }

    .faq-title {
        font-size: larger;
        text-align: center;
        width: 100%;
    }

    summary {
        display: list-item;
        list-style: inside disclosure-closed;
    }

    details>summary {
        width: 100%;
        padding: 5px;
    }

    details>p {
        padding: 10px;
        margin-left: 35px;
    }


}

.div4,
.div6,
.div7 {
    border: none !important
}
</style>