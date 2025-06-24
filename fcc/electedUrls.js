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
		'Nancy Mace': 'https://mace.house.gov/',
		'Tim Schott': 'https://www.scott.senate.gov/',
		'Lindsey Graham': 'https://www.lgraham.senate.gov/public/',
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
