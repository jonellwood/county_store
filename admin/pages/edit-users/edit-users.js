/**
 * Modern Edit Users Dashboard - Berkeley County Store Admin
 * Created: 2025/08/29
 * JavaScript functionality for user management interface
 */

class UsersManager {
	constructor() {
		this.users = [];
		this.filteredUsers = [];
		this.currentFilters = {
			role: '',
			department: '',
			search: '',
		};

		this.init();
	}

	async init() {
		console.log('🚀 Initializing Users Manager...');
		this.bindEvents();
		await this.loadInitialData();
	}

	bindEvents() {
		// Header actions
		document
			.getElementById('refreshBtn')
			.addEventListener('click', () => this.loadInitialData());
		document
			.getElementById('addUserBtn')
			.addEventListener('click', () => this.showAddUserModal());
		document
			.getElementById('exportBtn')
			.addEventListener('click', () => this.exportUsers());

		// Filter controls
		document
			.getElementById('applyFilters')
			.addEventListener('click', () => this.applyFilters());
		document
			.getElementById('clearFilters')
			.addEventListener('click', () => this.clearFilters());

		// Real-time search
		document.getElementById('searchFilter').addEventListener('input', (e) => {
			clearTimeout(this.searchTimeout);
			this.searchTimeout = setTimeout(() => {
				this.currentFilters.search = e.target.value;
				this.applyFilters();
			}, 300);
		});

		// Filter change events
		document.getElementById('roleFilter').addEventListener('change', (e) => {
			this.currentFilters.role = e.target.value;
			this.applyFilters();
		});

		document
			.getElementById('departmentFilter')
			.addEventListener('change', (e) => {
				this.currentFilters.department = e.target.value;
				this.applyFilters();
			});

		// Modal events
		document
			.getElementById('editUserBtn')
			.addEventListener('click', () => this.handleEditUser());
		document
			.getElementById('saveUserBtn')
			.addEventListener('click', () => this.saveUserChanges());
		document
			.getElementById('createUserBtn')
			.addEventListener('click', () => this.createNewUser());

		// Error retry
		document
			.getElementById('retryBtn')
			.addEventListener('click', () => this.loadInitialData());
	}

	async loadInitialData() {
		console.log('📊 Loading users data...');
		this.showLoading();

		try {
			const [usersResponse, filtersResponse] = await Promise.all([
				this.fetchUsers(),
				this.fetchFilters(),
			]);

			if (usersResponse.success && filtersResponse.success) {
				this.users = usersResponse.data;
				this.filteredUsers = [...this.users];
				this.populateFilters(filtersResponse.data);
				this.updateStats();
				this.renderUsers();
				this.showContent();
				console.log(`✅ Loaded ${this.users.length} users successfully`);
			} else {
				throw new Error(
					usersResponse.message ||
						filtersResponse.message ||
						'Failed to load data'
				);
			}
		} catch (error) {
			console.error('❌ Error loading data:', error);
			this.showError(error.message);
		}
	}

	async fetchUsers() {
		try {
			const response = await fetch('api.php?action=getUsers');
			const data = await response.json();
			return data;
		} catch (error) {
			throw new Error('Network error: ' + error.message);
		}
	}

	async fetchFilters() {
		try {
			const response = await fetch('api.php?action=getFilters');
			const data = await response.json();
			return data;
		} catch (error) {
			throw new Error('Network error: ' + error.message);
		}
	}

	populateFilters(filters) {
		// Store the filters data and system type for later use
		this.filters = filters;

		// Populate role filter
		const roleSelect = document.getElementById('roleFilter');
		if (roleSelect) {
			roleSelect.innerHTML = '<option value="">All Roles</option>';
		}

		// Populate edit modal role select
		const editRoleSelect = document.getElementById('role_id');
		if (editRoleSelect) {
			editRoleSelect.innerHTML = '<option value="">Select Role</option>';
		}

		// Populate add modal role select
		const addRoleSelect = document.getElementById('addRoleId');
		if (addRoleSelect) {
			addRoleSelect.innerHTML = '<option value="">Select Role</option>';
		}

		filters.roles.forEach((role) => {
			const option = document.createElement('option');
			option.value = role.role_id;

			// Enhanced display for new role system
			if (filters.useNewRoleSystem && role.department_name) {
				option.textContent = `${role.role_name} (${role.department_name})`;
			} else {
				option.textContent = role.role_name;
			}

			// Add data attributes for new role system
			if (filters.useNewRoleSystem) {
				option.setAttribute('data-department-id', role.department_id || '');
				option.setAttribute(
					'data-hierarchy-level',
					role.hierarchy_level || '0'
				);
				option.setAttribute(
					'data-is-department-specific',
					role.is_department_specific || 'false'
				);

				if (role.role_description) {
					option.setAttribute('title', role.role_description);
				}
			}

			// Add to all select elements
			if (roleSelect) {
				roleSelect.appendChild(option.cloneNode(true));
			}
			if (editRoleSelect) {
				editRoleSelect.appendChild(option.cloneNode(true));
			}
			if (addRoleSelect) {
				addRoleSelect.appendChild(option.cloneNode(true));
			}
		});

		// Populate department filter
		const deptSelect = document.getElementById('departmentFilter');
		if (deptSelect) {
			deptSelect.innerHTML = '<option value="">All Departments</option>';
		}

		// Populate add modal department select
		const addDeptSelect = document.getElementById('addDeptId');
		if (addDeptSelect) {
			addDeptSelect.innerHTML = '<option value="">Select Department</option>';
		}

		filters.departments.forEach((dept) => {
			const option = document.createElement('option');
			option.value = dept.dept_id;
			option.textContent = dept.dept_name;

			if (deptSelect) {
				deptSelect.appendChild(option.cloneNode(true));
			}
			if (addDeptSelect) {
				addDeptSelect.appendChild(option.cloneNode(true));
			}
		});

		// Show system info in console
		if (filters.useNewRoleSystem) {
			console.log(
				'🆕 Using new role system with',
				filters.roles.length,
				'roles'
			);
		} else {
			console.log('📊 Using legacy role system');
		}
	}

	updateStats() {
		const totalUsers = this.users.length;
		const totalAdmins = this.users.filter((user) => user.role_id == 1).length;
		const activeUsers = this.users.filter((user) => user.active == 1).length;

		// Get unique departments
		const departments = new Set(this.users.map((user) => user.dept_id));
		const totalDepartments = departments.size;

		document.getElementById('totalUsers').textContent = totalUsers;
		document.getElementById('totalAdmins').textContent = totalAdmins;
		document.getElementById('totalDepartments').textContent = totalDepartments;
		document.getElementById('activeUsers').textContent = activeUsers;
	}

	applyFilters() {
		console.log('🔍 Applying filters:', this.currentFilters);

		this.filteredUsers = this.users.filter((user) => {
			// Role filter
			if (
				this.currentFilters.role &&
				user.role_id != this.currentFilters.role
			) {
				return false;
			}

			// Department filter
			if (
				this.currentFilters.department &&
				user.dept_id != this.currentFilters.department
			) {
				return false;
			}

			// Search filter
			if (this.currentFilters.search) {
				const searchTerm = this.currentFilters.search.toLowerCase();
				const searchFields = [
					user.first_name,
					user.last_name,
					user.employee_number,
					user.email,
					user.role_name,
					user.dept_name,
				]
					.filter((field) => field)
					.join(' ')
					.toLowerCase();

				if (!searchFields.includes(searchTerm)) {
					return false;
				}
			}

			return true;
		});

		console.log(`📋 Filtered to ${this.filteredUsers.length} users`);
		this.renderUsers();
	}

	clearFilters() {
		console.log('🗑️ Clearing all filters...');

		this.currentFilters = {
			role: '',
			department: '',
			search: '',
		};

		document.getElementById('roleFilter').value = '';
		document.getElementById('departmentFilter').value = '';
		document.getElementById('searchFilter').value = '';

		this.filteredUsers = [...this.users];
		this.renderUsers();
	}

	renderUsers() {
		const container = document.getElementById('usersList');

		if (this.filteredUsers.length === 0) {
			this.showEmpty();
			return;
		}

		const usersHtml = this.filteredUsers
			.map((user) => this.createUserCard(user))
			.join('');
		container.innerHTML = usersHtml;

		// Bind user card events
		this.bindUserCardEvents();
		this.showContent();
	}

	createUserCard(user) {
		const initials = this.getInitials(user.first_name, user.last_name);
		const roleClass = user.role_id == 1 ? 'role-admin' : 'role-user';
		const statusClass = user.active == 1 ? 'status-active' : 'status-inactive';

		return `
            <div class="user-card" data-user-id="${user.id}">
                <div class="user-avatar">${initials}</div>
                <div class="user-info">
                    <h3 class="user-name">${user.first_name} ${
			user.last_name
		}</h3>
                    <p class="user-details">
                        #${user.employee_number} • ${
			user.dept_name || 'No Department'
		} • ${user.email || 'No Email'}
                    </p>
                </div>
                <div class="user-role ${roleClass}">
                    ${user.role_name || 'User'}
                </div>
                <div class="user-status ${statusClass}">
                    ${user.active == 1 ? 'Active' : 'Inactive'}
                </div>
                <div class="user-actions">
                    <button class="btn btn-primary btn-sm view-user" data-user-id="${
											user.id
										}">
                        <i class="fas fa-eye"></i>
                        View
                    </button>
                    <button class="btn btn-secondary btn-sm edit-user" data-user-id="${
											user.id
										}">
                        <i class="fas fa-edit"></i>
                        Edit
                    </button>
                </div>
            </div>
        `;
	}

	bindUserCardEvents() {
		// View user buttons
		document.querySelectorAll('.view-user').forEach((btn) => {
			btn.addEventListener('click', (e) => {
				e.stopPropagation();
				const userId = btn.dataset.userId;
				this.showUserDetails(userId);
			});
		});

		// Edit user buttons
		document.querySelectorAll('.edit-user').forEach((btn) => {
			btn.addEventListener('click', (e) => {
				e.stopPropagation();
				const userId = btn.dataset.userId;
				this.showEditUserModal(userId);
			});
		});

		// Card click for details
		document.querySelectorAll('.user-card').forEach((card) => {
			card.addEventListener('click', () => {
				const userId = card.dataset.userId;
				this.showUserDetails(userId);
			});
		});
	}

	getInitials(firstName, lastName) {
		const first = firstName ? firstName.charAt(0).toUpperCase() : '';
		const last = lastName ? lastName.charAt(0).toUpperCase() : '';
		return first + last || '??';
	}

	async showUserDetails(userId) {
		console.log('👤 Showing user details for ID:', userId);

		const user = this.users.find((u) => u.id == userId);
		if (!user) {
			console.error('User not found:', userId);
			return;
		}

		const modalBody = document.getElementById('userModalBody');
		modalBody.innerHTML = `
            <div class="user-details-grid">
                <div class="detail-item">
                    <div class="detail-label">Full Name</div>
                    <div class="detail-value">${user.first_name} ${
			user.last_name
		}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Employee Number</div>
                    <div class="detail-value">#${user.employee_number}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Email</div>
                    <div class="detail-value">${
											user.email || 'Not provided'
										}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Phone</div>
                    <div class="detail-value">${
											user.phone || 'Not provided'
										}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Department</div>
                    <div class="detail-value">${
											user.dept_name || 'No Department'
										}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Role</div>
                    <div class="detail-value">${user.role_name || 'User'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Status</div>
                    <div class="detail-value">
                        <span class="user-status ${
													user.active == 1 ? 'status-active' : 'status-inactive'
												}">
                            ${user.active == 1 ? 'Active' : 'Inactive'}
                        </span>
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Created</div>
                    <div class="detail-value">${
											user.created_at
												? new Date(user.created_at).toLocaleDateString()
												: 'Unknown'
										}</div>
                </div>
            </div>
        `;

		// Store current user for edit button
		this.currentUser = user;

		const modal = new bootstrap.Modal(document.getElementById('userModal'));
		modal.show();
	}

	async showEditUserModal(userId) {
		console.log('✏️ Showing edit modal for user ID:', userId);

		const user = this.users.find((u) => u.id == userId);
		if (!user) {
			console.error('User not found:', userId);
			return;
		}

		this.currentUser = user;
		await this.renderEditUserForm();

		const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
		modal.show();
	}

	async showAddUserModal() {
		console.log('➕ Showing add user modal...');

		this.currentUser = null;
		await this.renderAddUserForm();

		const modal = new bootstrap.Modal(document.getElementById('addUserModal'));
		modal.show();
	}

	async renderEditUserForm() {
		const modalBody = document.getElementById('editUserModalBody');
		const filtersData = await this.fetchFilters();

		modalBody.innerHTML = `
            <form class="user-form" id="editUserForm">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-user"></i>
                            First Name
                        </label>
                        <input type="text" class="form-control" name="first_name" value="${
													this.currentUser.first_name
												}" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-user"></i>
                            Last Name
                        </label>
                        <input type="text" class="form-control" name="last_name" value="${
													this.currentUser.last_name
												}" readonly>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-id-badge"></i>
                            Employee Number
                        </label>
                        <input type="text" class="form-control" name="employee_number" value="${
													this.currentUser.employee_number ||
													this.currentUser.emp_num
												}" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-envelope"></i>
                            Email
                        </label>
                        <input type="email" class="form-control" name="email" value="${
													this.currentUser.email || ''
												}" readonly>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-phone"></i>
                            Phone
                        </label>
                        <input type="tel" class="form-control" name="phone" value="${
													this.currentUser.phone || ''
												}" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-building"></i>
                            Department
                        </label>
                        <input type="text" class="form-control" name="dept_display" value="${
													this.currentUser.dept_name || 'No Department'
												}" readonly>
                        <input type="hidden" name="dept_id" value="${
													this.currentUser.dept_id || ''
												}">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">
                            <i class="fas fa-user-tag"></i>
                            Role
                        </label>
                        <select class="form-control" name="role_id" required>
                            ${filtersData.data.roles
															.map(
																(role) =>
																	`<option value="${role.role_id}" ${
																		role.role_id == this.currentUser.role_id
																			? 'selected'
																			: ''
																	}>${role.role_name}</option>`
															)
															.join('')}
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-info-circle"></i>
                            Leadership Roles
                        </label>
                        <input type="text" class="form-control" name="leadership_display" value="${
													this.currentUser.all_leadership_roles || 'None'
												}" readonly>
                    </div>
                </div>
                
                <input type="hidden" name="user_id" value="${
									this.currentUser.id
								}">
            </form>
        `;
	}

	async renderAddUserForm() {
		const modalBody = document.getElementById('addUserModalBody');
		const filtersData = await this.fetchFilters();

		modalBody.innerHTML = `
            <form class="user-form" id="addUserForm">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">
                            <i class="fas fa-user"></i>
                            First Name
                        </label>
                        <input type="text" class="form-control" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">
                            <i class="fas fa-user"></i>
                            Last Name
                        </label>
                        <input type="text" class="form-control" name="last_name" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">
                            <i class="fas fa-id-badge"></i>
                            Employee Number
                        </label>
                        <input type="text" class="form-control" name="employee_number" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-envelope"></i>
                            Email
                        </label>
                        <input type="email" class="form-control" name="email">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-phone"></i>
                            Phone
                        </label>
                        <input type="tel" class="form-control" name="phone">
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-building"></i>
                            Department
                        </label>
                        <select class="form-control" name="dept_id">
                            <option value="">No Department</option>
                            ${filtersData.data.departments
															.map(
																(dept) =>
																	`<option value="${dept.dept_id}">${dept.dept_name}</option>`
															)
															.join('')}
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">
                            <i class="fas fa-user-tag"></i>
                            Role
                        </label>
                        <select class="form-control" name="role_id" required>
                            ${filtersData.data.roles
															.map(
																(role) =>
																	`<option value="${role.role_id}" ${
																		role.role_id == 2 ? 'selected' : ''
																	}>${role.role_name}</option>`
															)
															.join('')}
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-key"></i>
                            Initial Password
                        </label>
                        <input type="password" class="form-control" name="password" placeholder="Leave blank for default">
                    </div>
                </div>
            </form>
        `;
	}

	handleEditUser() {
		if (this.currentUser) {
			// Close details modal and open edit modal
			bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
			this.showEditUserModal(this.currentUser.id);
		}
	}

	async saveUserChanges(forceUpdate = false) {
		console.log('💾 Saving user changes...');

		const form = document.getElementById('editUserForm');
		const formData = new FormData(form);

		// Add force update flag if set
		if (forceUpdate) {
			formData.append('force_update', 'true');
		}

		try {
			// First check for conflicts unless we're forcing the update
			if (!forceUpdate && this.filters.useNewRoleSystem) {
				const conflictFormData = new FormData();
				conflictFormData.append('user_id', formData.get('user_id'));
				conflictFormData.append('role_id', formData.get('role_id'));

				// Get user's department for conflict checking
				const currentUser = this.allUsers.find(
					(user) => user.emp_num === formData.get('user_id')
				);
				if (currentUser && currentUser.emp_dept) {
					conflictFormData.append('user_dept_id', currentUser.emp_dept);
				}

				const conflictResponse = await fetch(
					'api.php?action=checkRoleConflicts',
					{
						method: 'POST',
						body: conflictFormData,
					}
				);

				const conflictResult = await conflictResponse.json();

				if (conflictResult.success && conflictResult.has_conflicts) {
					// Show conflict resolution modal
					this.showRoleConflictModal(conflictResult.conflicts, formData);
					return;
				}
			}

			// Proceed with the update
			const response = await fetch('api.php?action=updateUser', {
				method: 'POST',
				body: formData,
			});

			const result = await response.json();

			if (result.success) {
				console.log('✅ User updated successfully');

				// Close modal
				bootstrap.Modal.getInstance(
					document.getElementById('editUserModal')
				).hide();

				// Reload data
				await this.loadInitialData();

				// Show success message
				this.showToast('User role updated successfully!', 'success');
			} else if (result.conflicts) {
				// Server-side conflict detection
				this.showRoleConflictModal(result.conflicts, formData);
			} else {
				throw new Error(result.message);
			}
		} catch (error) {
			console.error('❌ Error updating user:', error);
			this.showToast('Error updating user: ' + error.message, 'error');
		}
	}

	showRoleConflictModal(conflicts, originalFormData) {
		const conflictHtml = conflicts
			.map((conflict) => {
				if (conflict.type === 'department_conflict') {
					return `
					<div class="alert alert-warning mb-3">
						<i class="fas fa-exclamation-triangle me-2"></i>
						<strong>Role Conflict Detected</strong><br>
						${conflict.message}
					</div>
				`;
				}
				return `
				<div class="alert alert-danger mb-3">
					<i class="fas fa-times-circle me-2"></i>
					${conflict.message}
				</div>
			`;
			})
			.join('');

		const modalHtml = `
			<div class="modal fade" id="roleConflictModal" tabindex="-1">
				<div class="modal-dialog">
					<div class="modal-content bg-dark-custom">
						<div class="modal-header border-secondary">
							<h5 class="modal-title text-light">
								<i class="fas fa-exclamation-triangle text-warning me-2"></i>
								Role Assignment Conflict
							</h5>
							<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
						</div>
						<div class="modal-body">
							${conflictHtml}
							<p class="text-light mb-3">
								Do you want to proceed and replace the existing role assignment(s)?
							</p>
						</div>
						<div class="modal-footer border-secondary">
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
								<i class="fas fa-times me-2"></i>Cancel
							</button>
							<button type="button" class="btn btn-warning" id="forceUpdateBtn">
								<i class="fas fa-exclamation-triangle me-2"></i>Yes, Replace
							</button>
						</div>
					</div>
				</div>
			</div>
		`;

		// Remove existing conflict modal if any
		const existingModal = document.getElementById('roleConflictModal');
		if (existingModal) {
			existingModal.remove();
		}

		// Add new modal to body
		document.body.insertAdjacentHTML('beforeend', modalHtml);

		// Show the modal
		const conflictModal = new bootstrap.Modal(
			document.getElementById('roleConflictModal')
		);
		conflictModal.show();

		// Handle force update
		document.getElementById('forceUpdateBtn').addEventListener('click', () => {
			conflictModal.hide();
			this.saveUserChanges(true); // Force update
		});

		// Clean up modal when hidden
		document
			.getElementById('roleConflictModal')
			.addEventListener('hidden.bs.modal', () => {
				document.getElementById('roleConflictModal').remove();
			});
	}

	async createNewUser() {
		console.log('➕ Creating new user...');

		const form = document.getElementById('addUserForm');
		const formData = new FormData(form);

		try {
			const response = await fetch('api.php?action=createUser', {
				method: 'POST',
				body: formData,
			});

			const result = await response.json();

			if (result.success) {
				console.log('✅ User created successfully');

				// Close modal
				bootstrap.Modal.getInstance(
					document.getElementById('addUserModal')
				).hide();

				// Reload data
				await this.loadInitialData();

				// Show success message
				this.showToast('User created successfully!', 'success');
			} else {
				throw new Error(result.message);
			}
		} catch (error) {
			console.error('❌ Error creating user:', error);
			this.showToast('Error creating user: ' + error.message, 'error');
		}
	}

	async exportUsers() {
		console.log('📊 Exporting users...');

		try {
			const response = await fetch('api.php?action=exportUsers');
			const blob = await response.blob();

			const url = window.URL.createObjectURL(blob);
			const a = document.createElement('a');
			a.href = url;
			a.download = `users_export_${new Date().toISOString().split('T')[0]}.csv`;
			document.body.appendChild(a);
			a.click();
			document.body.removeChild(a);
			window.URL.revokeObjectURL(url);

			this.showToast('Users exported successfully!', 'success');
		} catch (error) {
			console.error('❌ Error exporting users:', error);
			this.showToast('Error exporting users: ' + error.message, 'error');
		}
	}

	showLoading() {
		document.getElementById('loadingState').style.display = 'flex';
		document.getElementById('errorState').style.display = 'none';
		document.getElementById('emptyState').style.display = 'none';
		document.getElementById('usersContent').style.display = 'none';
	}

	showError(message) {
		document.getElementById('errorMessage').textContent = message;
		document.getElementById('loadingState').style.display = 'none';
		document.getElementById('errorState').style.display = 'flex';
		document.getElementById('emptyState').style.display = 'none';
		document.getElementById('usersContent').style.display = 'none';
	}

	showEmpty() {
		document.getElementById('loadingState').style.display = 'none';
		document.getElementById('errorState').style.display = 'none';
		document.getElementById('emptyState').style.display = 'flex';
		document.getElementById('usersContent').style.display = 'none';
	}

	showContent() {
		document.getElementById('loadingState').style.display = 'none';
		document.getElementById('errorState').style.display = 'none';
		document.getElementById('emptyState').style.display = 'none';
		document.getElementById('usersContent').style.display = 'block';
	}

	showToast(message, type = 'info') {
		// Create toast notification
		const toast = document.createElement('div');
		toast.className = `toast-notification toast-${type}`;
		toast.innerHTML = `
            <div class="toast-content">
                <i class="fas fa-${
									type === 'success'
										? 'check-circle'
										: type === 'error'
										? 'exclamation-circle'
										: 'info-circle'
								}"></i>
                <span>${message}</span>
            </div>
        `;

		document.body.appendChild(toast);

		// Show toast
		setTimeout(() => toast.classList.add('show'), 100);

		// Remove toast
		setTimeout(() => {
			toast.classList.remove('show');
			setTimeout(() => document.body.removeChild(toast), 300);
		}, 3000);
	}
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
	new UsersManager();
});

// Toast notification styles (add to CSS if not already present)
const toastStyles = `
<style>
.toast-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background: var(--bg-secondary);
    border: 1px solid var(--border-primary);
    border-radius: 8px;
    padding: 1rem;
    box-shadow: 0 4px 20px var(--shadow-primary);
    z-index: 9999;
    transform: translateX(400px);
    transition: transform 0.3s ease;
}

.toast-notification.show {
    transform: translateX(0);
}

.toast-content {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-primary);
}

.toast-success {
    border-left: 4px solid var(--accent-green);
}

.toast-error {
    border-left: 4px solid var(--accent-red);
}

.toast-info {
    border-left: 4px solid var(--accent-blue);
}
</style>
`;

// Inject toast styles
document.head.insertAdjacentHTML('beforeend', toastStyles);
