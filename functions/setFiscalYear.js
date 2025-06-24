function setFiscalYear() {
	// console.log("Checking FY function")
	newFiscalYear = fiscalYear();
	fyStart = newFiscalYear[0];
	fyEnd = newFiscalYear[1];
	thisFiscalYear = fyStart + fyEnd;
	return thisFiscalYear;
}
