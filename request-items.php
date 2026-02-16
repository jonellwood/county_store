<?php
/*
Created: 2026/02/11
Purpose: Allow employees to request new items be added to the store
Organization: Berkeley County IT Department
*/

include_once "config.php";
$conn = new mysqli($host, $user, $password, $dbname, $port, $socket)
    or die('Could not connect to the database server' . mysqli_connect_error());

include_once 'Cart.class.php';
$cart = new Cart;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="Description" content="Request Items for Berkeley County Store" />

    <link href="./style/global-variables.css" rel="stylesheet" />
    <link href="./style/storeLux.css" rel="stylesheet" />
    <link href="./style/custom.css" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="favicons/favicon.ico">

    <title>Request Items - Berkeley County Store</title>
</head>

<body class="body">
    <?php include "components/viewHead.php" ?>

    <div class="container" style="margin-top: 2rem; margin-bottom: 4rem;">
        <section class="request-items-section">
            <div class="row justify-content-center">
                <div class="col-lg-8">

                    <?php
                    // Display success message
                    if (isset($_GET['success']) && $_GET['success'] == '1') {
                        echo '
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>Success!</strong> Your item request has been submitted successfully. You will receive a confirmation email shortly.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
                    }

                    // Display error messages
                    if (isset($_GET['error'])) {
                        $error_msg = '';
                        if ($_GET['error'] == 'validation') {
                            $error_msg = 'Please fill in all required fields correctly.';
                        } elseif ($_GET['error'] == 'email') {
                            $error_msg = 'There was an error sending your request. Please try again or contact support.';
                        }
                        echo '
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Error!</strong> ' . $error_msg . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
                    }
                    ?>

                    <div class="text-center mb-5">
                        <img src="assets/icons/add.svg" alt="request items" width="48" class="mb-3" style="opacity: 0.7;">
                        <h2 class="h1-responsive font-weight-bold mb-3">Request New Items</h2>
                        <p class="text-muted">Can't find what you're looking for? Submit a request to have new items added to the store. We review all requests and will notify you of the outcome.</p>
                    </div>

                    <div class="card shadow">
                        <div class="card-body p-4 p-md-5">
                            <!-- Step 1: Employee Validation -->
                            <div id="validation-section">
                                <h5 class="section-title mb-4">Step 1: Verify Your Information</h5>
                                <p class="text-muted mb-4">Please enter your employee number to verify your identity.</p>

                                <div class="row mb-3">

                                    <label for="lookup_emp_number" class="form-label">Employee Number *</label>
                                    <input type="text" id="lookup_emp_number" class="form-control" required>
                                </div>
                            </div>

                            <div id="validation-error" class="alert alert-danger d-none" role="alert"></div>

                            <button type="button" id="validate-btn" class="btn btn-primary">Verify Information</button>
                        </div>

                        <!-- Step 2: Show Validated Info & Request Form -->
                        <div id="request-form-section" class="d-none">
                            <form id="item-request-form" name="item-request-form" action="submit-item-request.php" method="POST">

                                <!-- Validated Employee Information (Read-only) -->
                                <h5 class="section-title mb-4">Your Information</h5>
                                <div class="alert alert-success mb-4">
                                    <strong>✓ Employee Verified</strong>
                                    <div class="mt-2">
                                        <strong>Name:</strong> <span id="display_name"></span><br>
                                        <strong>Employee #:</strong> <span id="display_emp_num"></span><br>
                                        <strong>Department:</strong> <span id="display_dept"></span><br>
                                        <strong>Email:</strong> <span id="display_email"></span>
                                    </div>
                                </div>

                                <!-- Hidden fields to submit -->
                                <input type="hidden" id="emp_number" name="emp_number" required>
                                <input type="hidden" id="emp_name" name="emp_name" required>
                                <input type="hidden" id="dept_name" name="dept_name" required>
                                <input type="hidden" id="dept_number" name="dept_number">
                                <input type="hidden" id="email_from_sync" name="email_from_sync" value="0">

                                <!-- Email field - conditional based on emp_sync -->
                                <div id="email-input-section" class="mb-4 d-none">
                                    <label for="emp_email" class="form-label">Email Address *</label>
                                    <input type="email" id="emp_email" name="emp_email" class="form-control">
                                    <small class="text-muted">No email address found in our records. Please provide one.</small>
                                </div>

                                <hr class="my-4">

                                <!-- Items Requested Section -->
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="section-title mb-0">Items Requested</h5>
                                    <button type="button" id="add-item-btn" class="btn btn-sm btn-outline-primary">+ Add Another Item</button>
                                </div>

                                <div id="items-container">
                                    <!-- Item 1 (template) -->
                                    <div class="item-card card mb-3" data-item-index="0">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="mb-0">Item #<span class="item-number">1</span></h6>
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn d-none">Remove</button>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Item Category *</label>
                                                <select name="items[0][category]" class="form-control form-select" required>
                                                    <option value="">-- Select Category --</option>
                                                    <option value="pants">Pants</option>
                                                    <option value="shirts">Shirts</option>
                                                    <option value="hats">Hats</option>
                                                    <option value="outerwear">Outerwear</option>
                                                    <option value="sweatshirts">Sweatshirts</option>
                                                    <option value="boots">Boots</option>
                                                    <option value="accessories">Accessories</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Product Link *</label>
                                                <input type="url" name="items[0][product_url]" class="form-control"
                                                    placeholder="https://example.com/product" required>
                                                <small class="text-muted">Paste the link to the product you want us to add</small>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Item Name / Description *</label>
                                                <input type="text" name="items[0][name]" class="form-control"
                                                    placeholder="e.g., Long-sleeve moisture-wicking polo" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Additional Details</label>
                                                <textarea name="items[0][details]" rows="3" class="form-control"
                                                    placeholder="Brand preferences, specific features, colors, sizes, etc."></textarea>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Estimated Quantity</label>
                                                    <input type="number" name="items[0][quantity]" class="form-control" min="1" value="1">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Priority *</label>
                                                    <select name="items[0][priority]" class="form-control form-select" required>
                                                        <option value="">-- Select --</option>
                                                        <option value="low">Low - Nice to have</option>
                                                        <option value="medium">Medium - Needed soon</option>
                                                        <option value="high">High - Urgent</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <!-- General Request Information -->
                                <h5 class="section-title mb-4">Request Details</h5>

                                <div class="mb-4">
                                    <label for="reason" class="form-label">Reason for Request *</label>
                                    <textarea id="reason" name="reason" rows="3" class="form-control"
                                        placeholder="Why do you need these items? How will they be used?" required></textarea>
                                </div>

                                <div class="mb-4">
                                    <label for="additional_notes" class="form-label">Additional Notes</label>
                                    <textarea id="additional_notes" name="additional_notes" rows="2" class="form-control"
                                        placeholder="Any other information we should know?"></textarea>
                                </div>

                                <hr class="my-4">

                                <!-- Submit Section -->
                                <div class="alert alert-info" role="alert">
                                    <strong>Note:</strong> All requests will be reviewed by the store management team. You will receive an email notification regarding the status of your request within 5-7 business days.
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="support.php" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" name="submitRequestBtn" class="btn btn-primary px-5" id="submit-btn">Submit Request</button>
                                </div>
                            </form>
                            <div id="form-status" class="mt-3"></div>
                        </div>
                    </div>
                </div>

                <!-- FAQ Section -->
                <div class="card mt-4 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-3">Frequently Asked Questions</h5>
                        <div class="faq-item mb-3">
                            <strong>How long does it take to review requests?</strong>
                            <p class="mb-0 text-muted">We typically review requests within 5-7 business days and will notify you via email.</p>
                        </div>
                        <div class="faq-item mb-3">
                            <strong>What happens after my request is approved?</strong>
                            <p class="mb-0 text-muted">Once approved, we'll work with vendors to acquire the item. You'll be notified when it becomes available in the store.</p>
                        </div>
                        <div class="faq-item">
                            <strong>Can I track the status of my request?</strong>
                            <p class="mb-0 text-muted">Yes! You'll receive email updates at each stage: received, under review, approved/denied, and available (if approved).</p>
                        </div>
                    </div>
                </div>

            </div>
    </div>
    </section>
    </div>

    <footer>
        <?php include "footer.php" ?>
    </footer>

    <script>
        // Employee Validation
        document.getElementById('validate-btn').addEventListener('click', function() {
            // const lastName = document.getElementById('lookup_last_name').value.trim();
            const empNumber = document.getElementById('lookup_emp_number').value.trim();
            const errorDiv = document.getElementById('validation-error');

            if (!empNumber) {
                errorDiv.textContent = 'Please enter your employee number.';
                errorDiv.classList.remove('d-none');
                return;
            }

            // Show loading state
            this.disabled = true;
            this.textContent = 'Verifying...';
            errorDiv.classList.add('d-none');

            // AJAX call to validate employee
            fetch('validate-employee.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `emp_number=${encodeURIComponent(empNumber)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Populate form with validated data
                        document.getElementById('emp_number').value = data.data.emp_number;
                        document.getElementById('emp_name').value = data.data.full_name;
                        document.getElementById('dept_name').value = data.data.dept_name;
                        document.getElementById('dept_number').value = data.data.dept_number;

                        // Display validated info
                        document.getElementById('display_name').textContent = data.data.full_name;
                        document.getElementById('display_emp_num').textContent = data.data.emp_number;
                        document.getElementById('display_dept').textContent = data.data.dept_name;

                        // Handle email
                        const emailSection = document.getElementById('email-input-section');
                        const emailInput = document.getElementById('emp_email');
                        if (data.data.has_email) {
                            // Email exists in emp_sync - keep it hidden but populated
                            document.getElementById('display_email').textContent = data.data.email;
                            emailInput.value = data.data.email;
                            emailInput.setAttribute('readonly', 'readonly');
                            emailInput.removeAttribute('required');
                            document.getElementById('email_from_sync').value = '1';
                            emailSection.classList.add('d-none');
                        } else {
                            // No email - prompt employee for address
                            document.getElementById('display_email').textContent = 'Not on file - please provide below';
                            emailInput.value = '';
                            emailInput.removeAttribute('readonly');
                            emailInput.setAttribute('required', 'required');
                            document.getElementById('email_from_sync').value = '0';
                            emailSection.classList.remove('d-none');
                            setTimeout(() => emailInput.focus(), 100);
                        }

                        // Hide validation section, show form
                        document.getElementById('validation-section').classList.add('d-none');
                        document.getElementById('request-form-section').classList.remove('d-none');
                    } else {
                        errorDiv.textContent = data.error;
                        errorDiv.classList.remove('d-none');
                        this.disabled = false;
                        this.textContent = 'Verify Information';
                    }
                })
                .catch(error => {
                    errorDiv.textContent = 'An error occurred. Please try again.';
                    errorDiv.classList.remove('d-none');
                    this.disabled = false;
                    this.textContent = 'Verify Information';
                });
        });

        // Multiple Items Management
        let itemCount = 1;

        document.getElementById('add-item-btn').addEventListener('click', function() {
            const container = document.getElementById('items-container');
            const template = container.querySelector('.item-card').cloneNode(true);

            // Update item number and index
            template.setAttribute('data-item-index', itemCount);
            template.querySelector('.item-number').textContent = itemCount + 1;

            // Update all name attributes
            template.querySelectorAll('select, input, textarea').forEach(field => {
                const name = field.getAttribute('name');
                if (name) {
                    field.setAttribute('name', name.replace(/\\[\\d+\\]/, '[' + itemCount + ']'));
                    field.value = '';
                }
            });

            // Show remove button
            template.querySelector('.remove-item-btn').classList.remove('d-none');

            container.appendChild(template);
            itemCount++;

            // Update remove button visibility
            updateRemoveButtons();
        });

        // Remove item
        document.getElementById('items-container').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-item-btn') || e.target.closest('.remove-item-btn')) {
                const itemCard = e.target.closest('.item-card');
                itemCard.remove();
                updateItemNumbers();
                updateRemoveButtons();
            }
        });

        function updateItemNumbers() {
            const items = document.querySelectorAll('.item-card');
            items.forEach((item, index) => {
                item.querySelector('.item-number').textContent = index + 1;
                item.setAttribute('data-item-index', index);

                // Update field names
                item.querySelectorAll('select, input, textarea').forEach(field => {
                    const name = field.getAttribute('name');
                    if (name) {
                        field.setAttribute('name', name.replace(/\\[\\d+\\]/, '[' + index + ']'));
                    }
                });
            });
            itemCount = items.length;
        }

        function updateRemoveButtons() {
            const items = document.querySelectorAll('.item-card');
            const removeButtons = document.querySelectorAll('.remove-item-btn');

            if (items.length === 1) {
                removeButtons.forEach(btn => btn.classList.add('d-none'));
            } else {
                removeButtons.forEach(btn => btn.classList.remove('d-none'));
            }
        }

        // Form submission - copy email if needed
        document.getElementById('item-request-form').addEventListener('submit', function(e) {
            console.log('Form submission event triggered');

            // Verify employee validation happened
            const empNumber = document.getElementById('emp_number').value;
            const empName = document.getElementById('emp_name').value;

            console.log('Employee fields:', {
                empNumber,
                empName
            });

            if (!empNumber || !empName) {
                e.preventDefault();
                alert('Please complete the employee verification step first.');
                console.log('Form submission blocked - missing employee verification');
                return false;
            }

            // Validate at least one item is filled out
            const categoryFields = document.querySelectorAll('select[name^="items"][name$="[category]"]');
            let hasValidItem = false;

            categoryFields.forEach(field => {
                if (field.value.trim() !== '') {
                    const itemIndex = field.getAttribute('name').match(/\[(\d+)\]/)[1];
                    const nameField = document.querySelector(`input[name="items[${itemIndex}][name]"]`);
                    const urlField = document.querySelector(`input[name="items[${itemIndex}][product_url]"]`);
                    const priorityField = document.querySelector(`select[name="items[${itemIndex}][priority]"]`);

                    if (nameField && urlField && priorityField &&
                        nameField.value.trim() !== '' &&
                        urlField.value.trim() !== '' &&
                        priorityField.value.trim() !== '') {
                        hasValidItem = true;
                    }
                }
            });

            if (!hasValidItem) {
                e.preventDefault();
                alert('Please fill out at least one complete item (category, name, URL, and priority).');
                console.log('Form submission blocked - no valid items');
                return false;
            }

            // Debug log
            console.log('Form submitting successfully with data:', {
                emp_number: empNumber,
                emp_name: empName,
                email: document.getElementById('emp_email').value,
                items: categoryFields.length
            });

            // Show loading state on submit button
            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Processing...';
        });
    </script>
</body>

</html>

<style>
    .request-items-section {
        max-width: 100%;
    }

    .section-title {
        color: var(--text-primary);
        font-weight: 600;
        border-left: 4px solid var(--color-primary);
        padding-left: 1rem;
    }

    .card {
        border: 1px solid var(--border-light);
        border-radius: 0.5rem;
    }

    .card-title {
        color: var(--text-primary);
        font-weight: 600;
    }

    .form-label {
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .form-control,
    .form-select {
        border: 1px solid var(--border-medium);
        padding: 0.65rem 0.75rem;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 0.2rem rgba(0, 86, 119, 0.15);
    }

    .btn-primary {
        background-color: var(--color-primary);
        border-color: var(--color-primary);
        font-weight: 500;
    }

    .btn-primary:hover {
        background-color: var(--color-primary-hover);
        border-color: var(--color-primary-hover);
    }

    .btn-outline-secondary {
        color: var(--text-secondary);
        border-color: var(--border-medium);
    }

    .btn-outline-secondary:hover {
        background-color: var(--bg-hover);
        color: var(--text-primary);
    }

    .faq-item {
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-light);
    }

    .faq-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .faq-item strong {
        color: var(--text-primary);
        display: block;
        margin-bottom: 0.5rem;
    }

    .alert-info {
        background-color: var(--color-info-light);
        border-color: var(--color-info);
        color: var(--text-primary);
    }

    .item-card {
        border: 2px solid var(--border-light);
        background-color: var(--bg-surface);
        transition: border-color 0.2s;
    }

    .item-card:hover {
        border-color: var(--border-medium);
    }

    .item-card h6 {
        color: var(--color-primary);
        font-weight: 600;
    }
</style>