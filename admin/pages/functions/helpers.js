function getLastTwoDigits(year) {
  // console.log(year.toString())
  var lastTwo = year.toString().slice(-2);
  return lastTwo;
}
function fiscalYear() {
  var currentMonth = new Date().getMonth() + 1;
  // console.log(currentMonth);
  var currentYear = new Date().getFullYear();
  var currentFY = 0;
  // console.log('current year: ', currentYear)
  // console.log('current fy: ', currentFY)
  if (currentMonth < 6) {
    currentFYStart = currentYear - 1;
    currentFYEnd = currentYear;
  } else {
    currentFYStart = currentYear;
    currentFYEnd = currentYear + 1;
  }
  // console.log("Current Fiscal Year Start, year is: ", getLastTwoDigits(currentFYStart))
  // console.log("Current Fiscal Year End, year is: ", getLastTwoDigits(currentFYEnd))
  return [getLastTwoDigits(currentFYStart), getLastTwoDigits(currentFYEnd)];
}

function isThisFiscalYear(fy) {
  var newFiscalYear = fiscalYear();
  var fyStart = newFiscalYear[0];
  // console.log(fyStart)
  var fyEnd = newFiscalYear[1];
  // console.log(fyEnd)
  var currentFY = fyStart + fyEnd;
  // console.log('current fy is: ', currentFY)
  // console.log('fy passed in is: ', fy)
  return fy >= currentFY;
}
function money_format(amount) {
  let USDollar = new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
  });
  return USDollar.format(amount);
}

function extractDate(inputString) {
  if (inputString == null) {
    return "none";
  } else {
    const parts = inputString.split(" ");
    return parts[0];
  }
}

function extractDateFromDB(inputString) {
  const parts = inputString.split("T");
  return parts[0];
}

function formatDate(inputDate) {
  const dateObject = new Date(inputDate);
  const month = String(dateObject.getMonth() + 1).padStart(2, "0");
  const day = String(dateObject.getDate()).padStart(2, "0");
  const year = dateObject.getFullYear();

  return `${month}-${day}-${year}`;
}
