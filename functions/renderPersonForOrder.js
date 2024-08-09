function renderPerson(data) {
	var html = `
        <table class="table">
            <thead>
                <tr>
                    <th>Requested For</th>
                    <th>Department</th>
                    <th>Email</th>
                    <th>Reference Number</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>${data['rf_first_name'] ? data['rf_first_name'] : ''} ${data['rf_last_name'] ? data['rf_last_name'] : ''}</td>
                    <td>${data['dep_name'] ? data['dep_name'] : ''}</td>
                    <td>${data['email'] ? data['email'] : ''}</td>
                    <td>${data['order_id'] ? data['order_id'] : ''}</td>
                </tr>
            </tbody>
        </table>
        
    `;
	return html;
}
