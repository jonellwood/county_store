function renderDepartmentOrdersList(data, target) {
  // console.log("Data passsed into render function");
  // console.log(data);
  const departments = {};
  for (const item of data) {
    const department = item.department;
    const departmentName = item.dep_name;
    if (!departments[department]) {
      departments[department] = [];
    }
    departments[department].push(item);
    // console.log(item.dep_name);
  }

  const tablesContainer = document.getElementById(target);
  for (const department in departments) {
    const items = departments[department];
    console.log("department");
    console.log(departments);
    console.log("items");
    console.log(items);

    let tableHTML = `<div id="${department}" popover class="po-enter-popover">
  
                        <div class="popover-top-bar">          
                          <p>Enter PO Number</p>
                          
                        </div>
                            <form action='change-all-to-ordered.php' method='post' class='poform'>
                                <label class="center-text tiny-text"></label></br>
                                <span>PO# 
                                    <input type="text" name='POnumber'>
                                </span>
                                <input type='hidden' value='${department}' name='dept_id'>
                                <br>
                                <button class="btn btn-outline-dark place-order-button" type="submit">Place Order for ${department} </button>
                            </form>
                            <p class="text-center">If no PO number is required, enter 00000</p>
                        </div>
                        <div class="dept-orders-table">
                        <div class="dept-table-list-header">
                            <h1 class="dept-table-list-title">Requests for ${department} ready to be ordered</h1>
                           
                            
                          </div>
                          <table class='table table-striped'>
                            <thead>
                              <tr>
                                <th class='thead-dark'>Order ID</th>
                                <th class='thead-dark'>Qty</th>
                                <th class='thead-dark'>Item Number</th>
                                <th class='thead-dark'>Product Name</th>
                                <th class='thead-dark'>Color</th>
                                <th class='thead-dark'>Size</th>
                                <th class='thead-dark'>Total w/Tax</th>
                                <th class='thead-dark'>Requested For</th>
                              </tr></thead>`;
    for (const item of items) {
      tableHTML += `<tbody>
                      <tr>
                        <td>${item.order_id}</td>
                          <td>${item.quantity}</td>
                          <td>${item.product_code}</td>
                          <td>${item.product_name}</td>
                          <td>${item.color_name}</td>
                          <td>${item.size_name}</td>
                          <td>${money_format(item.line_item_total)}</td>
                          <td>${item.req_for}</td>
                        </tr>
                      </tbody>`;
    }
    tableHTML += `<tr class="table-footer-row"><td colspan='8' class="table-footer-td"><button class="order-button btn btn-outline-warning" popovertarget="${department}">Order All</button></td></tr>`;
    tableHTML += "</table></div>";
    tablesContainer.innerHTML += tableHTML;
  }
}

// <button
//   popovertarget="po-enter-popover"
//   popovertargetaction="hide"
//   class="btn btn-dark-outline"
// >
//   X
// </button>;
//  <button
//    class="order-button btn btn-outline-warning"
//    popovertarget="${department}"
//  >
//    Order All
//  </button>;
