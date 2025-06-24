function fiscalYear() {
  var currentMonth = new Date().getMonth() + 1;
  // console.log(currentMonth);
  var currentYear = new Date().getFullYear();
  var currentFY = 0;
  // console.log('current year: ', currentYear)
  // console.log('current fy: ', currentFY)
  if (currentMonth < 6) {
    // console.log('less than 6')
    var currentFYStart = currentYear - 1;
    var currentFYEnd = currentYear;
  } else {
    console.log("else");
    currentFYStart = currentYear;
    currentFYEnd = currentYear + 1;
  }
  // console.log("Current Fiscal Year Start, year is: ", currentFYStart)
  // console.log("Current Fiscal Year End, year is: ", currentFYEnd)
  return [currentFYStart, currentFYEnd, currentMonth];
}

function renderBanner() {
  var fyData = fiscalYear();
  var html = "";
  if (fyData[2] < 6) {
    html += `<div class="alert-text">
        All requests must be submitted by May 14th, ${fyData[1]}. Requests will not be able to be submitted between May 15th and June 30th, ${fyData[1]}</div>
        `;
    document.getElementById("alert-banner").innerHTML = html;
  } else {
    html += `<div class="alert-text">
            All requests must be submitted by May 14th, ${fyData[1]}. Requests will not be able to be submitted between May 15th and June 30th, ${fyData[1]}</div>
            `;
    document.getElementById("alert-banner").innerHTML = html;
  }
}
