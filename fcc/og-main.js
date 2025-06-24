//load static json
let municipalOfficials, utilUrls;
//"/wp-content/js/cityofficials.json"
// Uncomment this for dev testing
$.getJSON('cityofficials.json', function (result) {
	municipalOfficials = result;
});
// Uncomment this for production and comment out the one above
// $.getJSON('/wp-content/js/cityofficials.json', function (result) {
// 	municipalOfficials = result;
// });
// Uncomment this for dev testing
$.getJSON('utilurls.json', function (result) {
	utilUrls = result;
});
// Uncomment this for production and comment out the one above
// $.getJSON('/wp-content/js/utilurls.json', function (result) {
// 	utilUrls = result;
// });

// var addressInput = document.getElementById('address');
// console.log('Address Input is: ', addressInput);
// addressInput.onkeyup = getAddressInfo();

$('#address').on('keyup', function (e) {
	if (e.keyCode === 13) {
		// console.log('13 is keyup');
		getAddressInfo();
	}
});

//Listen on event emit from autocomplete dropdown select.
document.addEventListener('autocomplete-event', function (e) {
	getAddressInfo();
});
//Check for url params
document.addEventListener('DOMContentLoaded', () => {
	const urlParams = new URLSearchParams(window.location.search);
	const myParam = urlParams.get('address');
	console.log(myParam);
});

function schoolUrlGetter(obj) {
	if (obj.ELEMENTARY.trim())
		obj['ELEMENTARYurl'] = schoolUrls[obj.ELEMENTARY.trim().toUpperCase()];
	if (obj.PRIMARY.trim())
		obj['PRIMARYurl'] = schoolUrls[obj.PRIMARY.trim().toUpperCase()];
	if (obj.MIDDLE.trim())
		obj['MIDDLEurl'] = schoolUrls[obj.MIDDLE.trim().toUpperCase()];
	if (obj.INTERMEDIATE.trim())
		obj['INTERMEDIATEurl'] = schoolUrls[obj.INTERMEDIATE.trim().toUpperCase()];
	if (obj.HIGH.trim())
		obj['HIGHurl'] = schoolUrls[obj.HIGH.trim().toUpperCase()];
	return obj;
}

function titleCase(str) {
	return str
		.toLowerCase()
		.split(' ')
		.map(function (word) {
			return word.charAt(0).toUpperCase() + word.slice(1);
		})
		.join(' ');
}

function findUrlByName(name) {
	console.log('Looking for url for : ', name);
	const foundEntry = Object.entries(electedUrls[0]).find(
		([key]) => key === name
	);
	return foundEntry ? foundEntry[1] : null;
}

function getAddressInfo() {
	$('.address-warning').hide();
	// line below for local dev
	let val = document.getElementById('address').value;

	// line below from original production
	// let val = document.getElementById('myInput').value;
	// console.log('val is: ', val);
	var validaddress = /^[0-9].+$/;
	// console.log(`?text=${encodeURIComponent(val)}`);
	// if (val.match(validaddress) && val.indexOf(',') != -1) {
	if (val.match(validaddress)) {
		//$("#spinner").show();
		// val = val.substring(0, val.indexOf(','));

		// let url =
		// 	'https://gis.berkeleycountysc.gov/arcgis/rest/services/custom/addresssearch/MapServer/1/query?where=ADDRESS+%3D+%27' +
		// 	val +
		// 	'%27&outFields=*&returnGeometry=false&f=pjson';
		// let url =
		// 	'https://gis.berkeleycountysc.gov/arcgis/rest/services/mobile/GEOCODE_SUGGEST/GeocodeServer/suggest?text=' +
		// 	val +
		// 	'&f=pjson';
		let url =
			'https://gis.berkeleycountysc.gov/arcgis/rest/services/custom/addresssearch/MapServer/1/query?where=ADDRESS+%3D+%27' +
			val.toUpperCase() +
			'%27&outFields=*&returnGeometry=false&f=pjson';
		let resultData = null;
		$.getJSON(url, function (data) {
			resultData = data.features[0].attributes;
			if (resultData['ADDRESS'] != null)
				resultData['ADDRESS'] = titleCase(resultData['ADDRESS']);
			if (resultData['ELEMENTARY'] != null)
				resultData['ELEMENTARY'] = titleCase(resultData['ELEMENTARY']);
			if (resultData['PRIMARY'] != null)
				resultData['PRIMARY'] = titleCase(resultData['PRIMARY']);
			if (resultData['MIDDLE'] != null)
				resultData['MIDDLE'] = titleCase(resultData['MIDDLE']);
			if (resultData['MIDDLE'] == 'Cross Middle/high')
				resultData['MIDDLE'] = 'Cross Middle/High';
			if (resultData['INTERMEDIATE'] != null)
				resultData['INTERMEDIATE'] = titleCase(resultData['INTERMEDIATE']);
			if (resultData['HIGH'] != null)
				resultData['HIGH'] = titleCase(resultData['HIGH']);
			if (resultData['HIGH'] == 'Cross Middle/high')
				resultData['HIGH'] = 'Cross Middle/High';
			if (resultData['PollName'] != null)
				resultData['PollName'] = titleCase(resultData['PollName']);
			if (resultData['SMALL_CLAIMS'] != null)
				resultData['SMALL_CLAIMS'] = titleCase(resultData['SMALL_CLAIMS']);
			if (
				resultData['SewerProvider'] != null &&
				resultData['SewerProvider'] != 'BCWSA'
			)
				resultData['SewerProvider'] = titleCase(resultData['SewerProvider']);
			if (resultData['SewerProvider'] == 'Summerville Cpw')
				resultData['SewerProvider'] = 'Summerville CPW';
			if (resultData['MAGISTRATE_OFFICE'] != null)
				resultData['MAGISTRATE_OFFICE'] = titleCase(
					resultData['MAGISTRATE_OFFICE']
				);
			if (resultData['Fire_Districts_Municipalities'] != null)
				resultData['Fire_Districts_Municipalities'] = titleCase(
					resultData['Fire_Districts_Municipalities']
				);
			if (resultData['Fire_Districts_Municipalities'] == 'C&b')
				resultData['Fire_Districts_Municipalities'] = 'C&B';
			if (resultData['Fire_Districts_Municipalities'] == 'C&b/pringletown')
				resultData['Fire_Districts_Municipalities'] = 'C&B/Pringletown';
			if (resultData['MosquitoZone'] != null)
				resultData['MosquitoZone'] = titleCase(resultData['MosquitoZone']);
			resultData = schoolUrlGetter(resultData);
			if (resultData.SolidWasteService != null)
				resultData['SolidWasteServiceurl'] =
					garbageUrls[resultData.SolidWasteService.trim()];
		}).done(async function () {
			var myTemplate = returnTemplate(val, resultData);
			$('#searchResult').empty();
			$('#searchResult').append(myTemplate);
			//$("#spinner").hide();

			document.querySelectorAll('.result-item').forEach(function (item) {
				var text = item.querySelector('.result-val').textContent.trim();
				if (!text || text === 'null') item.style.display = 'none';
			});
			if (resultData.FCCBlock !== null) {
				await fetch(
					`https://api.berkeleycountysc.gov/fcc_query_blockcode.php?blockcode=${resultData['FCCBlock']}`
				)
					.then((response) => response.json())
					.then((fccdata) => {
						document.querySelector(
							'.card-broadband'
						).innerHTML += `<p><a class="uk-link-heading" target="_blank" href="https://broadbandmap.fcc.gov">FCC Source Data<i class="fas fa-link fa-xs external-link"></i></a></p>`;
						fccdata.forEach((prov, i, arr) => {
							if (prov) {
								console.log(prov.technology);
								let tech = {
									10: 'Copper',
									11: 'ADSL',
									12: 'ADSL',
									13: 'ADSL',
									40: 'Cable',
									41: 'Cable',
									42: 'Cable',
									43: 'Cable',
									50: 'Fiber to the Premises',
									0: 'Other',
									90: 'Other',
									20: 'Other',
									30: 'Other',
									60: 'GSO Satellite',
									61: 'NGSO Saltellite',
									70: 'Fixed Wireless',
									71: 'Licensed Fixed Wireless',
								};
								let techType = tech[prov?.technology] ?? 'Unknown';
								document.querySelector(
									'.card-broadband'
								).innerHTML += `<p><span class="uk-text-bold">${prov.brand_name}</span><br>${prov.holding_company}<br><span class="fcc-bold">${techType}</span><br>Max Down: ${prov.max_down} Mbps<br>Max Up: ${prov.max_up} Mbps</p><hr>`;
							} else {
								console.log('skipping undefined element at index', i);
							}
						});
					});
			} else {
				setTimeout(
					() =>
						(document.querySelector('.card-broadband').style.display = 'none'),
					1000
				);
			}

			//get street Maintenance
			//https://gis.berkeleycountysc.gov/arcgis/rest/services/mobile/mobile_map/MapServer/4
			//SHORT_NAME = 'OAKLAND DR' AND ((1146 > L_ADD_FROM  AND 1146 < L_ADD_TO) OR(1146 > R_ADD_FROM  AND 1146 < R_ADD_TO ) )

			let addNum = val.substr(0, val.indexOf(' '));
			let addTxt = val.substr(val.indexOf(' ') + 1);
			if (addTxt.indexOf(' APT') > 0)
				addTxt = addTxt.substr(0, addTxt.indexOf(' APT'));
			if (addTxt.indexOf(' BLDG') > 0)
				addTxt = addTxt.substr(0, addTxt.indexOf(' BLDG'));
			if (addTxt.indexOf(' LOT') > 0)
				addTxt = addTxt.substr(0, addTxt.indexOf(' LOT'));
			if (addTxt.indexOf(' SUITE') > 0)
				addTxt = addTxt.substr(0, addTxt.indexOf(' SUITE'));
			if (addTxt.indexOf(' UNIT') > 0)
				addTxt = addTxt.substr(0, addTxt.indexOf(' UNIT'));

			//'https://gis.berkeleycountysc.gov/arcgis/rest/services/mobile/mobile_map/MapServer/4/query?where=SHORT_NAME='+addTxt+'+AND+(('+addTxt+' >= L_ADD_FROM AND '+addTxt+'<= L_ADD_TO)OR('+addTxt+' >= R_ADD_FROM AND '+addTxt+' <= R_ADD_TO))&outFields=CLASS_TEXT&returnGeometry=false&f=json'

			$.ajax({
				url:
					"https://gis.berkeleycountysc.gov/arcgis/rest/services/mobile/mobile_map/MapServer/4/query?where=SHORT_NAME='" +
					addTxt +
					"' AND ((" +
					addNum +
					' >= L_ADD_FROM AND ' +
					addNum +
					'<= L_ADD_TO)OR(' +
					addNum +
					' >= R_ADD_FROM AND ' +
					addNum +
					' <= R_ADD_TO))&outFields=CLASS_TEXT&returnGeometry=false&f=json',
				type: 'GET',
			}).done(function (streetmaint) {
				if ($.parseJSON(streetmaint).features.length > 0) {
					let strClass = titleCase(
						$.parseJSON(streetmaint).features[0].attributes.CLASS_TEXT
					);
					$('.other-util').append(
						`<p><span class="uk-text-bold">Street Maintenance</span></br> ${strClass}</p>`
					);
				}
			});
		}); //end done function
	} else {
		//alert("Enter a valid address.")
		$('.address-warning').show();
	}
}

function muniElected(val) {
	if (val != null) {
		var mayor = municipalOfficials[val]['Mayor'].name;
		console.log(mayor);

		let mayorHtml = `
             <p><span class="uk-text-bold">${val} Mayor</span></br>  
            <a class="uk-link-heading" href="${municipalOfficials[val]['Mayor'].url}" target="_blank">${mayor}</a></p>
        `;

		let councilMembersHtml = municipalOfficials[val]['Councilmember']
			.map(
				(member) =>
					`<a class="uk-link-heading" href=${member.url} target="_blank">${member.name}</a>`
			)
			.join(', ');

		let finalHtml = `${mayorHtml}<p><span class="uk-text-bold">${val} Council Members</span></br>${councilMembersHtml}</p>`;
		return finalHtml;
	} else {
		return ``;
	}
}

function returnTemplate(addy, data) {
	return ` <div class="uk-child-width-1-1 uk-text-center uk-flex-middle uk-background-secondary uk-padding uk-light uk-animation-fade uk-fake-padding">
    <h1 class="uk-text-center">${addy}</h1>
  </div>
  <div class="uk-section uk-align-center uk-width-xxlarge">
    

    <div class="uk-child-width-1-2@m uk-top uk-grid-small" uk-grid="masonry: true" uk-scrollspy="cls: uk-animation-slide-top; target: .uk-card; delay: 150; repeat: false" >
        <div>
            <div class="uk-card uk-card-default uk-card-body uk-text-center ">
                <h2 class="uk-card-title"><i class="fas fa-school uk-text-muted fa-header"></i> Schools</h2>
                <p class="result-item"><span class="uk-text-bold">Elementary School</span></br><span class="result-val"> <a class="uk-link-heading" target="_blank" href="${
									data.ELEMENTARYurl
								}">${
		data.ELEMENTARY
	}<i class="fas fa-link fa-xs external-link"></i></a></span></p>
                <p class="result-item"><span class="uk-text-bold">Primary School</span></br><span class="result-val"> <a class="uk-link-heading" target="_blank" href="${
									data.PRIMARYurl
								}">${
		data.PRIMARY
	}<i class="fas fa-link fa-xs external-link"></i></a></span></p>
                <p class="result-item"><span class="uk-text-bold">Middle School</span></br><span class="result-val"> <a class="uk-link-heading" target="_blank" href="${
									data.MIDDLEurl
								}">${
		data.MIDDLE
	}<i class="fas fa-link fa-xs external-link"></i></a></span></p>
                <p class="result-item"><span class="uk-text-bold">Intermediate School</span></br><span class="result-val"> <a class="uk-link-heading" target="_blank" href="${
									data.INTERMEDIATEurl
								}">${
		data.INTERMEDIATE
	}<i class="fas fa-link fa-xs external-link"></i></a></span></p>
                <p class="result-item"><span class="uk-text-bold">High School</span></br><span class="result-val"> <a class="uk-link-heading" target="_blank" href="${
									data.HIGHurl
								}">${
		data.HIGH
	}<i class="fas fa-link fa-xs external-link"></i></a></span></p>           
            </div>
        </div>
        <div>
            <div class="uk-card uk-card-default uk-card-body uk-text-center">
                <h2 class="uk-card-title"><i class="fas fa-gavel uk-text-muted fa-header"></i> Courts</h2>
                <p><span class="uk-text-bold">Small Claims Court</span></br> ${
									data.SMALL_CLAIMS
								}  </br>${data.SMALL_CLAIMS_ADDR}</p>
                <p><span class="uk-text-bold">Magistrate Court</span></br>${
									data.MAGISTRATE_OFFICE
								}</br>${data.MAGISTRATE_ADDR}</p>
            </div>
        </div>
        <div>
            <div class="uk-card uk-card-default uk-card-body uk-text-center">
                <h2 class="uk-card-title"><i class="fas fa-tint uk-text-muted fa-header"></i> Utilities</h2>
                <p><span class="uk-text-bold">Water Service</span></br> 
                <a class="uk-link-heading" href="${
									'waterlink' // utilUrls['water'][data.WaterServiceArea]
								}" target="_blank">
                                ${
																	data.WaterServiceArea
																} <i class="fas fa-link fa-xs external-link"></i></a></p>
                <p><span class="uk-text-bold">Sewer Service</span></br> ${
									data.SewerProvider != null
										? // ? `<a class="uk-link-heading" href="${
										  // 		utilUrls['sewer'][data.SewerProvider.toUpperCase()]
										  //   }" target="_blank">
										  `<a class="uk-link-heading" href="${
												'sewer'[data.SewerProvider.toUpperCase()]
										  }" target="_blank">
                                          ${
																						data.SewerProvider
																					} <i class="fas fa-link fa-xs external-link"></i></a>`
										: `Not Available`
								}</p>
                <p class="result-item"><span class="uk-text-bold">Solid Waste</span></br><span class="result-val"> ${
									data.SolidWasteService != 'Not Available'
										? // ? `<a class="uk-link-heading" href="${data.SolidWasteServiceurl}" target="_blank">${data.SolidWasteService} <i class="fas fa-link fa-xs external-link"></i></a>`
										  // : `${data.SolidWasteService}`
										  `<a class="uk-link-heading" href="${data.SolidWasteService}" target="_blank">${data.SolidWasteService} <i class="fas fa-link fa-xs external-link"></i></a>`
										: `${data.SolidWasteService}`
								} </span></p>
                <p><span class="uk-text-bold">Electrical Service</span></br> ${
									data.Electrical != null
										? `<a class="uk-link-heading" href="${
												electricUrls[data.Electrical]
										  }" target="_blank">${
												data.Electrical
										  } <i class="fas fa-link fa-xs external-link"></i></a>`
										: `Not Available`
								}</p>
            </div>
        </div>
        <div>
            <div class="uk-card uk-card-default uk-card-body uk-text-center">
                <h2 class="uk-card-title"><i class="fas fa-vote-yea uk-text-muted fa-header"></i> Voting</h2>                
                <p><span class="uk-text-bold">Voting Precinct</span></br>  ${
									data.PrecinctName
								}</p>
                <p><span class="uk-text-bold">Polling Location</span></br>  ${
									data.PollName
								}</br>${data.PollAddress}</p>
                <p><a class="uk-link-heading" target="_blank" href="https://gis.berkeleycountysc.gov/maps/voter/">View Voter Map<i class="fas fa-link fa-xs external-link"></i></a></p>
            </div>
        </div>
        <div> 
            <div class="uk-card uk-card-default uk-card-body uk-text-center other-util"> 
                <h2 class="uk-card-title"><i class="fas fa-fire-extinguisher uk-text-muted fa-header"></i> Other Services</h2>                
                <p><span class="uk-text-bold">Fire District</span></br> ${
									data.Fire_Districts_Municipalities
								}</p>
                <p class="result-item"><span class="uk-text-bold">Mosquito Spray Zone</span></br><span class="result-val"><a class="uk-link-heading" href="https://www.berkeleycountysc.gov/drupal/dept/mosquito" target="_blank"> ${
									data.MosquitoZone
								}<i class="fas fa-link fa-xs external-link"></i></a></span></p>
                <p class="result-item" ${
									data.TMS == null ? 'hidden' : ''
								}><span class="uk-text-bold">Real Property</span></br><span class="result-val"><a class="uk-link-heading" href="https://berkeleycountysc.gov/propcards/property_card.php?tms=${
		data.TMS
	}" target="_blank">Property Card<i class="fas fa-link fa-xs external-link"></i></a></span></p>
                <p><span class="uk-text-bold">Hurricane Evacuation Zone</span></br> <span class="result-val"><a class="uk-link-heading" href="https://www.scemd.org/stay-informed/publications/hurricane-guide/" target="_blank">Zone ${
									data.EvacZone
								} <i class="fas fa-link fa-xs external-link"></i></a></span></p>
            </div>
        </div>
        <div>
            <div class="uk-card uk-card-default uk-card-body uk-text-center">
                <h2 class="uk-card-title"><i class="fas fa-users uk-text-muted fa-header"></i> Political Boundaries</h2>
                <p><span class="uk-text-bold">Berkeley County Supervisor</span></br><a href='https://berkeleycountysc.gov/dept/council/elected-officials/supervisor/' target="_blank"> Johnny Cribb</a></p>
                ${muniElected(data.muniname)}
                <p><span class="uk-text-bold">School District</span></br> District ${
									data.District
								} </br><a href="${findUrlByName(data.Name)}" target=_"blank">${
		data.Name
	}
                                 <i class="fas fa-link fa-xs external-link"></i></p></a>
                <p><span class="uk-text-bold">County Council</span></br> District ${
									data.CountyCouncilDistrict
								} </br><a href=${findUrlByName(
		data.CountyCouncilName
	)} target="_blank">${
		data.CountyCouncilName
	} <i class="fas fa-link fa-xs external-link"></i></a></p>
                <p><span class="uk-text-bold">SC House District</span></br>  District ${
									data.SCHouseDistrict
								} </br><a href=${findUrlByName(
		data.SCHouseName
	)} target="_blank">${
		data.SCHouseName
	}<i class="fas fa-link fa-xs external-link"></i></a></p>
                <p><span class="uk-text-bold">SC Senate District</span></br>  District ${
									data.SCSenateDistrict
								} </br><a href=${findUrlByName(
		data.SCSenateName
	)} target="_blank">${
		data.SCSenateName
	}</a><i class="fas fa-link fa-xs external-link"></i></p>
                <p><span class="uk-text-bold">US Congress Representative</span></br>  District ${
									data.USCongressDistrict
								} </br><a href=${findUrlByName(
		data.USCongressName
	)} target="_blank">${
		data.USCongressName
	}</a><i class="fas fa-link fa-xs external-link"></i></p>
                <p><span class="uk-text-bold">US Congress Senators</span></br> <a href=${findUrlByName(
									'Sen. Lindsey Graham'
								)}>Sen. Lindsey Graham</a> </br><a href=${findUrlByName(
		'Sen. Tim Scott'
	)}> Sen. Tim Scott</a> </p>
                
            </div>
        </div>
        
        <div>
            <div class="uk-card uk-card-default uk-card-body uk-text-center card-broadband">
                <h2 class="uk-card-title"><span uk-icon="icon: rss; ratio: 1.5" class="uk-text-muted"></span> Broadband</h2>
                
            </div>
        </div>
    </div>
`;
}
// These url have been updated 6/2024
var schoolUrls = {
	'BERKELEY ELEMENTARY': 'https://www.bcsdschools.net/o/mce',
	'BERKELEY HIGH': 'https://www.bcsdschools.net/o/bhs',
	'BERKELEY INTERMEDIATE': 'https://www.bcsdschools.net/Domain/11',
	'BERKELEY MIDDLE': 'https://www.bcsdschools.net/o/bms',
	'BERKELEY ALTERNATIVE': 'https://www.bcsdschools.net/o/bas',
	'BOULDER BLUFF ELEMENTARY': 'https://www.bcsdschools.net/o/bbe',
	'BOWENS CORNER ELEMENTARY': 'https://www.bcsdschools.net/o/bce',
	'CAINHOY ELEMENTARY/MIDDLE': 'https://www.bcsdschools.net/o/che',
	'CANE BAY ELEMENTARY': 'https://www.bcsdschools.net/o/cbe',
	'CANE BAY HIGH': 'https://www.bcsdschools.net/o/cbh',
	'CANE BAY MIDDLE': 'https://www.bcsdschools.net/o/cbm',
	'CAROLYN LEWIS SCHOOL': 'https://www.bcsdschools.net/o/cls',
	'COLLEGE PARK ELEMENTARY': 'https://www.bcsdschools.net/o/cpe',
	'COLLEGE PARK MIDDLE': 'https://www.bcsdschools.net/o/cpm',
	'CROSS ELEMENTARY': 'https://www.bcsdschools.net/o/ces',
	'CROSS MIDDLE/HIGH': 'https://www.bcsdschools.net/Domain/333',
	'CROSS HIGH': 'https://www.bcsdschools.net/o/chs',
	'DANIEL ISLAND SCHOOL': 'https://www.bcsdschools.net/o/dis',
	'DEVON FOREST ELEMENTARY': 'https://www.bcsdschools.net/o/dfe',
	'FOXBANK ELEMENTARY': 'https://www.bcsdschools.net/o/fbe',
	'GOOSE CREEK ELEMENTARY': 'https://www.bcsdschools.net/o/gce',
	'GOOSE CREEK HIGH': 'https://www.bcsdschools.net/o/gchs',
	'GOOSE CREEK PRIMARY': 'https://www.bcsdschools.net/Domain/26',
	'HE BONNER ELEMENTARY': 'https://www.bcsdschools.net/o/hbe',
	'HANAHAN ELEMENTARY': 'https://www.bcsdschools.net/o/hes',
	'HANAHAN HIGH': 'https://www.bcsdschools.net/o/hhs',
	'HANAHAN MIDDLE': 'https://www.bcsdschools.net/o/hms',
	'HOWE HALL AIMS ELEMENTARY': 'https://www.bcsdschools.net/o/hha',
	'JK GOURDIN ELEMENTARY': 'https://www.bcsdschools.net/o/jke',
	'MACEDONIA MIDDLE': 'https://www.bcsdschools.net/o/mms',
	'MARRINGTON ELEMENTARY': 'https://www.bcsdschools.net/o/mne',
	'MARRINGTON MIDDLE': 'https://www.bcsdschools.net/o/mmsoa',
	'MOUNT HOLLY ELEMENTARY': 'https://www.bcsdschools.net/o/mhe',
	'MONCKS CORNER ELEMENTARY': 'https://www.bcsdschools.net/o/mce',
	'NEXTON ELEMENTARY': 'https://www.bcsdschools.net/o/nes',
	'PHILIP SIMMONS ELEMENTARY': 'https://www.bcsdschools.net/o/pse',
	'PHILIP SIMMONS HIGH': 'https://www.bcsdschools.net/o/psh',
	'PHILIP SIMMONS MIDDLE': 'https://www.bcsdschools.net/o/psm',
	'SANGAREE ELEMENTARY': 'https://www.bcsdschools.net/o/sre',
	'SANGAREE INTERMEDIATE': 'https://www.bcsdschools.net/o/sri',
	'SANGAREE MIDDLE': 'https://www.bcsdschools.net/o/srm',
	'SEDGEFIELD MIDDLE': 'https://www.bcsdschools.net/o/sfm',
	'ST STEPHEN ELEMENTARY': 'https://www.bcsdschools.net/o/sse',
	'ST STEPHEN MIDDLE': 'https://www.bcsdschools.net/o/ssm',
	'STRATFORD HIGH': 'https://www.bcsdschools.net/o/shs',
	'TIMBERLAND HIGH': 'https://www.bcsdschools.net/o/ths',
	'WESTVIEW ELEMENTARY': 'https://www.bcsdschools.net/o/wve',
	'WESTVIEW MIDDLE': 'https://www.bcsdschools.net/o/wvm',
	'WESTVIEW PRIMARY': 'https://www.bcsdschools.net/o/wvp',
	'WHITESVILLE ELEMENTARY': 'https://www.bcsdschools.net/o/wes',
};

var garbageUrls = {
	'Carolina Waste': 'http://www.carolinawaste.com/',
	'Goose Creek Public Works':
		'https://www.cityofgoosecreek.com/government/departments/public-works/sanitation/collection-schedule',
	'Hanahan Public Works': 'https://cityofhanahan.com/government/public-works/',
	Republic: 'https://www.republicservices.com/',
};
var countyDistrictUrls = {
	'District 1':
		'https://www.berkeleycountysc.gov/drupal/countyofficials/district1',
	'District 2':
		'https://www.berkeleycountysc.gov/drupal/countyofficials/district2',
	'District 3':
		'https://www.berkeleycountysc.gov/drupal/countyofficials/district3',
	'District 4':
		'https://www.berkeleycountysc.gov/drupal/countyofficials/district4',
	'District 5':
		'https://www.berkeleycountysc.gov/drupal/countyofficials/district5',
	'District 6':
		'https://www.berkeleycountysc.gov/drupal/countyofficials/district6',
	'District 7':
		'https://www.berkeleycountysc.gov/drupal/countyofficials/district7',
	'District 8':
		'https://www.berkeleycountysc.gov/drupal/countyofficials/district8',
};

var electricUrls = {
	'Berkeley Electric Coop': 'https://www.berkeleyelectric.coop/',
	'Dominion Energy': 'https://www.dominionenergy.com/',
	'Santee Cooper': 'https://www.santeecooper.com/',
	'Edisto Electric Coop': 'http://edistoelectric.com/',
};
var electedUrls = [
	{
		'Dan Owens':
			'https://berkeleycountysc.gov/dept/council/elected-officials/dan_owens/',
		'Joshua Whitley':
			'https://berkeleycountysc.gov/dept/council/elected-officials/josh_whitley//',
		'Phillip Obie':
			'https://berkeleycountysc.gov/dept/council/elected-officials/phillip_obie/',
		'Tommy Newell':
			'https://berkeleycountysc.gov/dept/council/elected-officials/tommy_newell/',
		'Any Stern':
			'https://berkeleycountysc.gov/dept/council/elected-officials/amy_stern/',
		'Marshall West':
			'https://berkeleycountysc.gov/dept/council/elected-officials/marshall_west/',
		'Caldwell Pinckney':
			'https://berkeleycountysc.gov/dept/council/elected-officials/caldwell_pinckney/',
		'Steve Davis':
			'https://berkeleycountysc.gov/dept/council/elected-officials/steve_davis/',
		'Johnny Crib':
			'https://berkeleycountysc.gov/dept/council/elected-officials/supervisor/',
		'Thomas Hamilton Jr':
			'https://monckscornersc.gov/elected/thomas-hamilton-jr',
		'David A Dennis': 'https://monckscornersc.gov/elected/david-a-dennis',
		'DeWayne Kitts': 'https://monckscornersc.gov/elected/dewayne-kitts',
		'James N Law': 'https://monckscornersc.gov/elected/james-n-law',
		'Latorie S Lloyd': 'https://monckscornersc.gov/elected/latorie-s-lloyd',
		'Chad Sweatman': 'https://monckscornersc.gov/elected/chad-sweatman',
		'James Bryan Ware III':
			'https://monckscornersc.gov/elected/james-bryan-ware-iii',
		'Russ Touchberry': 'https://summervillesc.gov/270/Mayor---Russ-Touchberry',
		'Aaron Brown':
			'https://summervillesc.gov/268/Council-District-1---Aaron-Brown',
		'Tiffany Johnson-Wilson':
			'https://summervillesc.gov/593/Council-District-2---Tiffany-Johnson-Wil',
		'Matt Halter':
			'https://summervillesc.gov/621/Council-District-3---Matt-Halter',
		'Richard Waring':
			'https://summervillesc.gov/271/Council-District-4---Richard-G-Waring-IV',
		'Kima Garten-Schmidt':
			'https://summervillesc.gov/272/Council-District-5---Kima-Garten-Schmidt',
		'Bob Jackson':
			'https://summervillesc.gov/273/Council-District-6---Bob-Jackson',
		'Harriet Holman':
			'https://www.dorchestercountysc.gov/government/county-council/council-members/district-1-harriet-holman',
		'David Chinnis':
			'https://www.dorchestercountysc.gov/government/county-council/council-members/district-2-david-chinnis',
		'Rita May Ranck':
			'https://www.dorchestercountysc.gov/government/county-council/council-members/district-3-rita-may-ranck-2873',
		'Todd Friddle':
			'https://www.dorchestercountysc.gov/government/county-council/council-members/district-4-todd-friddle',
		'Eddie Crosby':
			'https://www.dorchestercountysc.gov/government/county-council/council-members/district-5-eddie-crosby-2821',
		'William Hearn':
			'https://www.dorchestercountysc.gov/government/county-council/council-members/district-6-william-hearn',
		'Jay Byars':
			'https://www.dorchestercountysc.gov/government/county-council/council-members/district-7-jay-byars',
		'Gregory Habib': 'https://www.cityofgoosecreek.com/staff/gregory-habib',
		'Debra Green-Fletcher':
			'https://www.cityofgoosecreek.com/staff/debra-green-fletcher',
		'Jerry Tekac': 'https://www.cityofgoosecreek.com/staff/jerry-tekac',
		'Christopher Harmon':
			'https://www.cityofgoosecreek.com/staff/christopher-harmon',
		'Gayla Mcswain': 'https://www.cityofgoosecreek.com/staff/gayla-mcswain',
		'Hannah Cox': 'https://www.cityofgoosecreek.com/staff/hannah-cox',
		'Melissa Enos-Sims':
			'https://www.cityofgoosecreek.com/staff/melissa-enos-sims',
		'Mr. Michael Ramsey':
			'https://go.boarddocs.com/sc/berkeley/Board.nsf/Public',
		'Mr. Mac McQuillin':
			'https://go.boarddocs.com/sc/berkeley/Board.nsf/Public',
		'Mr. Joe Baker': 'https://go.boarddocs.com/sc/berkeley/Board.nsf/Public',
		'Mrs. Kathy Littleton':
			'https://go.boarddocs.com/sc/berkeley/Board.nsf/Public',
		'Dr. Jimmy Hinson': 'https://go.boarddocs.com/sc/berkeley/Board.nsf/Public',
		'Mrs. Sally Wofford':
			'https://go.boarddocs.com/sc/berkeley/Board.nsf/Public',
		'Mrs. Yvonne Bradley':
			'https://go.boarddocs.com/sc/berkeley/Board.nsf/Public',
		'Dr. Crystal Wright':
			'https://go.boarddocs.com/sc/berkeley/Board.nsf/Public',
		'Mr. David Barrow': 'https://go.boarddocs.com/sc/berkeley/Board.nsf/Public',
		'Rep. Nancy Mace': 'https://mace.house.gov/',
		'Sen. Tim Scott': 'https://www.scott.senate.gov/',
		'Sen. Lindsey Graham': 'https://www.lgraham.senate.gov/public/',
		'Mark Smith': 'https://www.scstatehouse.gov/member.php?code=1724999793',
		'Sylleste Davis': 'https://www.scstatehouse.gov/member.php?code=0456249946',
		'Cezar McKnight': 'https://www.scstatehouse.gov/member.php?code=1276136211',
		'Joseph Jefferson Jr.':
			'https://www.scstatehouse.gov/member.php?code=0924999889',
		'J.A. Moore': 'https://www.scstatehouse.gov/member.php?code=1356818019',
		'Joseph Daning': 'https://www.scstatehouse.gov/member.php?code=0451136310',
		'Krystle Simmons':
			'https://www.scstatehouse.gov/member.php?code=1694886161',
		'Ronnie Sabb': 'https://www.scstatehouse.gov/member.php?code=1617045261',
		'Lawrence Grooms':
			'https://www.scstatehouse.gov/member.php?code=0729545367',
		'Brian Adams': 'https://www.scstatehouse.gov/member.php?code=0002272727',
		'Vernon Stephens':
			'https://www.scstatehouse.gov/member.php?code=1752272517',
		'Christie Rainwater':
			'https://www.cityofhanahan.com/directory-listing/christie-rainwater',
	},
];
