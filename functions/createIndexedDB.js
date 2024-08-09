function copyDataToIndexedDB() {
	const apiUrl = `API/fetchAllProductData.php`;

	fetch(apiUrl)
		.then((response) => response.json())
		.then((data) => {
			const version = 2; // Update version for changes
			const request = indexedDB.open('store_database', version);

			request.onerror = (event) => {
				console.error('Error opening database:', event);
			};

			request.onsuccess = (event) => {
				const db = event.target.result;

				const transaction = db.transaction('myObjectStore', 'readwrite');

				request.onupgradeneeded = (event) => {
					const db = event.target.result;
					const objectStore = db.createObjectStore('myObjectStore', {
						keyPath: 'id',
						autoIncrement: true,
					});

					objectStore.createIndex('nameIndex', 'name');
				};

				const objectStore = transaction.objectStore('myObjectStore');
				data.forEach((item) => {
					objectStore.add(item);
				});

				transaction.oncomplete = () => {
					db.close();
					console.log('Data copied to IndexedDB successfully');
				};

				transaction.onerror = (event) => {
					console.error('Error adding data to IndexedDB:', event);
				};
			};
		})
		.catch((error) => {
			console.error('Error fetching data:', error);
		});
}
