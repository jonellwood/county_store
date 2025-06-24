
// This function fires a request to the PHP script that marks all items in a department as `Ordered`
async function markOrdered(deptNum) {
        await fetch('./change-all-to-ordered.php?dept_id=' + deptNum)
    }
    
