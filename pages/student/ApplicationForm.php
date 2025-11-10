<?php
require_once '../../config.php';

// Require student role
requireRole('student');

// Get user data
$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$userEmail = $_SESSION['user_email'];
$studentId = $_SESSION['student_id'];

// Get user's bank details from database
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT bank_name, bank_number FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$userBankName = $userData['bank_name'] ?? '';
$userBankNumber = $userData['bank_number'] ?? '';
$stmt->close();
$conn->close();

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = trim($_POST['category'] ?? '');
    $subcategory = trim($_POST['subcategory'] ?? '');
    $amountApplied = trim($_POST['amount_applied'] ?? '');
    $applicationData = trim($_POST['application_data'] ?? '');
    $bankName = trim($_POST['bank_name'] ?? '');
    $bankAccountNumber = trim($_POST['bank_account_number'] ?? '');
    
    // Define category limits
    $categoryLimits = [
        'Bereavement (Khairat)' => [
            'Student' => ['fixed' => 500, 'max' => 500],
            'Parent' => ['fixed' => 200, 'max' => 200],
            'Sibling' => ['fixed' => 100, 'max' => 100]
        ],
        'Illness & Injuries' => [
            'Out-patient Treatment' => ['fixed' => null, 'max' => 30],
            'In-patient Treatment' => ['fixed' => null, 'max' => 1000],
            'Injuries' => ['fixed' => null, 'max' => 200]
        ],
        'Emergency' => [
            'Critical Illness' => ['fixed' => null, 'max' => 200],
            'Natural Disaster' => ['fixed' => null, 'max' => 200],
            'Others' => ['fixed' => null, 'max' => null]
        ]
    ];
    
    // Validation
    if (empty($category) || empty($subcategory) || empty($amountApplied) || empty($applicationData)) {
        $error = 'Please fill in all required fields.';
    } elseif (!is_numeric($amountApplied) || $amountApplied <= 0) {
        $error = 'Please enter a valid amount.';
    } elseif (!isset($categoryLimits[$category]) || !isset($categoryLimits[$category][$subcategory])) {
        $error = 'Invalid category or subcategory selected.';
    } else {
        // Validate amount against limits
        $limitInfo = $categoryLimits[$category][$subcategory];
        
        // Check if amount is fixed
        if ($limitInfo['fixed'] !== null) {
            // For fixed amounts, ensure the submitted amount matches
            if (abs($amountApplied - $limitInfo['fixed']) > 0.01) {
                $error = 'Invalid amount. This subcategory has a fixed amount of RM ' . number_format($limitInfo['fixed'], 2) . '.';
            }
        } else {
            // For variable amounts, check maximum limit
            if ($limitInfo['max'] !== null && $amountApplied > $limitInfo['max']) {
                $error = 'Amount exceeds the maximum limit of RM ' . number_format($limitInfo['max'], 2) . ' for this subcategory.';
            }
        }
    }
    
    if (empty($error)) {
        $conn = getDBConnection();
        
        // Insert application
        $stmt = $conn->prepare("INSERT INTO applications (user_id, category, subcategory, amount_applied, application_data, bank_name, bank_account_number, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("issdsss", $userId, $category, $subcategory, $amountApplied, $applicationData, $bankName, $bankAccountNumber);
        
        if ($stmt->execute()) {
            $applicationId = $conn->insert_id;
            
            // Handle file uploads - use relative path from current file location
            $baseDir = dirname(dirname(__DIR__)); // Go up to project root
            $uploadDir = $baseDir . '/storage/uploads/applications/' . $applicationId . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $uploadedFiles = [];
            if (isset($_FILES['documents']) && !empty($_FILES['documents']['name'][0])) {
                $files = $_FILES['documents'];
                $fileCount = count($files['name']);
                
                for ($i = 0; $i < $fileCount; $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $fileName = $files['name'][$i];
                        $fileTmpName = $files['tmp_name'][$i];
                        $fileSize = $files['size'][$i];
                        $fileType = $files['type'][$i];
                        
                        // Validate file type
                        $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
                        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        
                        if (!in_array($fileExt, $allowedExtensions)) {
                            continue; // Skip invalid file types
                        }
                        
                        // Validate file size (10MB max)
                        if ($fileSize > 10 * 1024 * 1024) {
                            continue; // Skip files that are too large
                        }
                        
                        // Generate unique filename
                        $uniqueFileName = uniqid() . '_' . time() . '.' . $fileExt;
                        $filePath = $uploadDir . $uniqueFileName;
                        
                        // Move uploaded file
                        if (move_uploaded_file($fileTmpName, $filePath)) {
                            // Get document type from form or derive from file
                            $documentType = $_POST['document_types'][$i] ?? 'supporting_document';
                            
                            // Save to database - use relative path from root
                            $relativePath = 'storage/uploads/applications/' . $applicationId . '/' . $uniqueFileName;
                            $docStmt = $conn->prepare("INSERT INTO application_documents (application_id, document_type, file_path, file_name, file_size, mime_type) VALUES (?, ?, ?, ?, ?, ?)");
                            $docStmt->bind_param("isssis", $applicationId, $documentType, $relativePath, $fileName, $fileSize, $fileType);
                            $docStmt->execute();
                            $docStmt->close();
                            
                            $uploadedFiles[] = $fileName;
                        }
                    }
                }
            }
            
            $stmt->close();
            $conn->close();
            
            // Set success message in session and redirect
            $_SESSION['message'] = 'Application submitted successfully! Your application is now pending review.';
            $_SESSION['message_type'] = 'success';
            header('Location: StudentDashboard.php');
            exit();
        } else {
            $error = 'Failed to submit application. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>New Application - RCMP UniFa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/styles.css" />
    <style>
        .application-form-container {
            min-height: 100vh;
            background: var(--light);
            padding: 32px 0;
        }
        .form-header {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #fff;
            padding: 32px 0;
            margin-bottom: 32px;
        }
        .form-header h1 {
            margin: 0 0 8px;
            font-size: 2rem;
            font-weight: 700;
        }
        .form-header p {
            margin: 0;
            opacity: 0.9;
        }
        .form-content {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 24px;
        }
        .form-section {
            background: var(--card);
            padding: 32px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 24px;
        }
        .form-section h2 {
            margin: 0 0 24px;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text);
            border-bottom: 2px solid var(--primary);
            padding-bottom: 12px;
        }
        .form-group {
            margin-bottom: 24px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text);
        }
        .form-group label .required {
            color: #dc2626;
        }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #fff;
            color: var(--text);
            outline: none;
            transition: border-color .2s ease, box-shadow .2s ease;
            font-family: inherit;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(10,61,98,0.12);
        }
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        .form-group .help-text {
            font-size: 0.875rem;
            color: var(--muted);
            margin-top: 6px;
        }
        .file-upload-area {
            border: 2px dashed #cbd5e1;
            border-radius: 10px;
            padding: 24px;
            text-align: center;
            background: var(--light);
            transition: all 0.2s ease;
        }
        .file-upload-area:hover {
            border-color: var(--primary);
            background: #f8fafc;
        }
        .file-upload-area.dragover {
            border-color: var(--primary);
            background: #eff6ff;
        }
        .file-list {
            margin-top: 16px;
        }
        .file-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px;
            background: var(--light);
            border-radius: 8px;
            margin-bottom: 8px;
        }
        .file-item-info {
            flex: 1;
        }
        .file-item-name {
            font-weight: 500;
            color: var(--text);
        }
        .file-item-size {
            font-size: 0.875rem;
            color: var(--muted);
        }
        .file-item-remove {
            padding: 6px 12px;
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.875rem;
        }
        .file-item-remove:hover {
            background: #fdd;
        }
        .form-actions {
            display: flex;
            gap: 16px;
            justify-content: flex-end;
            margin-top: 32px;
        }
        .nav-bar {
            background: var(--card);
            padding: 16px 0;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 0;
        }
        .nav-bar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }
        .nav-links {
            display: flex;
            gap: 24px;
            align-items: center;
        }
        .nav-links a {
            color: var(--muted);
            font-weight: 500;
            transition: color 0.2s ease;
        }
        .nav-links a:hover {
            color: var(--primary);
        }
        .logout-btn {
            padding: 8px 16px;
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .logout-btn:hover {
            background: #fdd;
        }
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }
        .alert-error {
            background-color: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        .alert-success {
            background-color: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
        .form-group input[readonly] {
            background-color: #f3f4f6 !important;
            cursor: not-allowed;
            border-color: #d1d5db;
        }
        .form-group input[readonly]:focus {
            border-color: #d1d5db;
            box-shadow: none;
        }
    </style>
</head>
<body>
    <?php 
    include '../component/MessageDialog.php';
    renderMessageDialogScript();
    if ($error) {
        showErrorMessage($error, true, null, 5000);
    }
    ?>
    <!-- Navigation Bar -->
    <div class="nav-bar">
        <div class="nav-bar-content">
            <a href="../../index.php" class="brand">
                <img src="../../public/unikl-rcmp.png" alt="UniKL RCMP logo" class="logo" style="height: 40px;" />
            </a>
            <div class="nav-links">
                <a href="StudentDashboard.php">Dashboard</a>
                <a href="ApplicationForm.php">New Application</a>
                <a href="#">Applications</a>
                <form method="post" action="../logout.php" style="display: inline;">
                    <button type="submit" class="logout-btn" name="logout">Logout</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Form Header -->
    <div class="form-header">
        <div class="form-content">
            <h1>New Financial Aid Application</h1>
            <p>Fill in the details below to apply for financial assistance</p>
        </div>
    </div>

    <!-- Application Form -->
    <div class="application-form-container">
        <div class="form-content">

            <form method="post" action="" enctype="multipart/form-data" id="applicationForm">
                <!-- Application Details Section -->
                <div class="form-section">
                    <h2>Application Details</h2>
                    
                    <div class="form-group">
                        <label for="category">Category <span class="required">*</span></label>
                        <select id="category" name="category" required onchange="updateSubcategories()">
                            <option value="">Select a category</option>
                            <option value="Bereavement (Khairat)">Bereavement (Khairat)</option>
                            <option value="Illness & Injuries">Illness & Injuries</option>
                            <option value="Emergency">Emergency</option>
                        </select>
                        <div class="help-text">Select the type of financial aid you are applying for</div>
                    </div>

                    <div class="form-group">
                        <label for="subcategory">Subcategory <span class="required">*</span></label>
                        <select id="subcategory" name="subcategory" required disabled>
                            <option value="">Select a category first</option>
                        </select>
                        <div class="help-text" id="subcategoryHelp">Please select a category first to see available subcategories</div>
                    </div>

                    <div class="form-group">
                        <label for="amount_applied">Amount Applied (RM) <span class="required">*</span></label>
                        <input type="number" id="amount_applied" name="amount_applied" step="0.01" min="0" placeholder="0.00" required />
                        <div class="help-text" id="amountHelp">Enter the amount you are requesting in Malaysian Ringgit</div>
                    </div>

                    <div class="form-group">
                        <label for="application_data">Application Description <span class="required">*</span></label>
                        <textarea id="application_data" name="application_data" placeholder="Please provide detailed information about your financial need, circumstances, and how this assistance will help you..." required></textarea>
                        <div class="help-text">Explain your situation and why you need financial assistance</div>
                    </div>
                </div>

                <!-- Bank Details Section -->
                <div class="form-section">
                    <h2>Bank Account Details</h2>
                    <p style="color: var(--muted); margin-bottom: 24px;">Provide your bank account details for fund transfer if your application is approved</p>
                    
                    <div class="form-group">
                        <label for="bank_name">Bank Name</label>
                        <input type="text" id="bank_name" name="bank_name" value="<?php echo htmlspecialchars($userBankName); ?>" placeholder="e.g. Maybank, CIMB, Public Bank" />
                        <div class="help-text">Your bank account will be used for fund transfer if approved</div>
                    </div>

                    <div class="form-group">
                        <label for="bank_account_number">Bank Account Number</label>
                        <input type="text" id="bank_account_number" name="bank_account_number" value="<?php echo htmlspecialchars($userBankNumber); ?>" placeholder="e.g. 1234567890" />
                        <div class="help-text">Your bank account number for fund transfer</div>
                    </div>
                </div>

                <!-- Supporting Documents Section -->
                <div class="form-section">
                    <h2>Supporting Documents</h2>
                    <p style="color: var(--muted); margin-bottom: 24px;">Upload relevant documents to support your application (e.g. medical reports, receipts, bank statements, etc.)</p>
                    
                    <div class="form-group">
                        <label for="documents">Upload Documents</label>
                        <div class="file-upload-area" id="fileUploadArea">
                            <input type="file" id="documents" name="documents[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" style="display: none;" />
                            <p style="margin: 0; color: var(--muted);">
                                <strong>Click to upload</strong> or drag and drop files here
                            </p>
                            <p style="margin: 8px 0 0; font-size: 0.875rem; color: var(--muted);">
                                Supported formats: PDF, DOC, DOCX, JPG, PNG (Max 10MB per file)
                            </p>
                        </div>
                        <div class="file-list" id="fileList"></div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="StudentDashboard.php" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit Application</button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../component/footer.php'; renderFooter('../../'); ?>
    <script src="../../js/main.js"></script>
    <script>
        // File upload handling
        const fileUploadArea = document.getElementById('fileUploadArea');
        const fileInput = document.getElementById('documents');
        const fileList = document.getElementById('fileList');
        const selectedFiles = [];

        fileUploadArea.addEventListener('click', () => fileInput.click());

        fileUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadArea.classList.add('dragover');
        });

        fileUploadArea.addEventListener('dragleave', () => {
            fileUploadArea.classList.remove('dragover');
        });

        fileUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadArea.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });

        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        function handleFiles(files) {
            Array.from(files).forEach(file => {
                if (file.size > 10 * 1024 * 1024) {
                    alert('File ' + file.name + ' is too large. Maximum size is 10MB.');
                    return;
                }
                selectedFiles.push(file);
                displayFile(file);
            });
        }

        function displayFile(file) {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            fileItem.dataset.fileName = file.name;
            
            const fileInfo = document.createElement('div');
            fileInfo.className = 'file-item-info';
            fileInfo.innerHTML = `
                <div class="file-item-name">${file.name}</div>
                <div class="file-item-size">${formatFileSize(file.size)}</div>
            `;
            
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'file-item-remove';
            removeBtn.textContent = 'Remove';
            removeBtn.addEventListener('click', () => {
                fileItem.remove();
                const index = selectedFiles.findIndex(f => f.name === file.name);
                if (index > -1) {
                    selectedFiles.splice(index, 1);
                }
                updateFileInput();
            });
            
            fileItem.appendChild(fileInfo);
            fileItem.appendChild(removeBtn);
            fileList.appendChild(fileItem);
            
            updateFileInput();
        }

        function updateFileInput() {
            const dt = new DataTransfer();
            selectedFiles.forEach(file => dt.items.add(file));
            fileInput.files = dt.files;
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        // Category and subcategory mapping
        const categorySubcategories = {
            'Bereavement (Khairat)': [
                { value: 'Student', label: 'Student (RM 500 fixed)' },
                { value: 'Parent', label: 'Parent (RM 200 fixed)' },
                { value: 'Sibling', label: 'Sibling (RM 100 fixed)' }
            ],
            'Illness & Injuries': [
                { value: 'Out-patient Treatment', label: 'Out-patient Treatment (RM 30/semester, 2 claims/year)' },
                { value: 'In-patient Treatment', label: 'In-patient Treatment (Up to RM 1,000)' },
                { value: 'Injuries', label: 'Injuries (Up to RM 200 for support equipment)' }
            ],
            'Emergency': [
                { value: 'Critical Illness', label: 'Critical Illness (Up to RM 200)' },
                { value: 'Natural Disaster', label: 'Natural Disaster (RM 200 limit)' },
                { value: 'Others', label: 'Others (Subject to SWF Campus committee approval)' }
            ]
        };

        const categoryLimits = {
            'Bereavement (Khairat)': {
                'Student': { fixed: 500, max: 500 },
                'Parent': { fixed: 200, max: 200 },
                'Sibling': { fixed: 100, max: 100 }
            },
            'Illness & Injuries': {
                'Out-patient Treatment': { fixed: null, max: 30 },
                'In-patient Treatment': { fixed: null, max: 1000 },
                'Injuries': { fixed: null, max: 200 }
            },
            'Emergency': {
                'Critical Illness': { fixed: null, max: 200 },
                'Natural Disaster': { fixed: null, max: 200 },
                'Others': { fixed: null, max: null } // Subject to approval
            }
        };

        function updateSubcategories() {
            const categorySelect = document.getElementById('category');
            const subcategorySelect = document.getElementById('subcategory');
            const subcategoryHelp = document.getElementById('subcategoryHelp');
            const amountInput = document.getElementById('amount_applied');
            
            const selectedCategory = categorySelect.value;
            
            // Clear existing options
            subcategorySelect.innerHTML = '<option value="">Select a subcategory</option>';
            
            if (selectedCategory && categorySubcategories[selectedCategory]) {
                // Enable subcategory select
                subcategorySelect.disabled = false;
                subcategorySelect.required = true;
                
                // Add subcategories for selected category
                categorySubcategories[selectedCategory].forEach(sub => {
                    const option = document.createElement('option');
                    option.value = sub.value;
                    option.textContent = sub.label;
                    subcategorySelect.appendChild(option);
                });
                
                subcategoryHelp.textContent = 'Select the appropriate subcategory for your application';
            } else {
                // Disable subcategory select if no category selected
                subcategorySelect.disabled = true;
                subcategorySelect.required = false;
                subcategoryHelp.textContent = 'Please select a category first to see available subcategories';
            }
            
            // Reset amount field
            amountInput.value = '';
            updateAmountField();
        }

        function updateAmountField() {
            const categorySelect = document.getElementById('category');
            const subcategorySelect = document.getElementById('subcategory');
            const amountInput = document.getElementById('amount_applied');
            const amountHelp = amountInput.nextElementSibling;
            
            const category = categorySelect.value;
            const subcategory = subcategorySelect.value;
            
            if (category && subcategory && categoryLimits[category] && categoryLimits[category][subcategory]) {
                const limitInfo = categoryLimits[category][subcategory];
                
                // Check if amount is fixed
                if (limitInfo.fixed !== null) {
                    // Fixed amount - auto-fill and disable
                    amountInput.value = limitInfo.fixed;
                    amountInput.readOnly = true;
                    amountInput.style.backgroundColor = '#f3f4f6';
                    amountInput.style.cursor = 'not-allowed';
                    amountInput.setAttribute('data-max-amount', limitInfo.fixed);
                    if (amountHelp) {
                        amountHelp.textContent = 'This is a fixed amount for this subcategory (RM ' + limitInfo.fixed.toFixed(2) + ').';
                        amountHelp.style.color = 'var(--primary)';
                    }
                } else {
                    // Variable amount
                    amountInput.readOnly = false;
                    amountInput.style.backgroundColor = '#fff';
                    amountInput.style.cursor = 'text';
                    
                    if (limitInfo.max !== null) {
                        amountInput.max = limitInfo.max;
                        amountInput.placeholder = 'Maximum: RM ' + limitInfo.max.toFixed(2);
                        amountInput.setAttribute('data-max-amount', limitInfo.max);
                        if (amountHelp) {
                            amountHelp.textContent = 'Enter the amount you are requesting (maximum RM ' + limitInfo.max.toFixed(2) + ').';
                            amountHelp.style.color = 'var(--muted)';
                        }
                    } else {
                        amountInput.removeAttribute('max');
                        amountInput.placeholder = 'Amount (subject to SWF Campus committee approval)';
                        amountInput.removeAttribute('data-max-amount');
                        if (amountHelp) {
                            amountHelp.textContent = 'Enter the amount you are requesting. This amount is subject to SWF Campus committee approval.';
                            amountHelp.style.color = 'var(--muted)';
                        }
                    }
                }
            } else {
                // Reset to default
                amountInput.value = '';
                amountInput.readOnly = false;
                amountInput.style.backgroundColor = '#fff';
                amountInput.style.cursor = 'text';
                amountInput.removeAttribute('max');
                amountInput.placeholder = '0.00';
                amountInput.removeAttribute('data-max-amount');
                if (amountHelp) {
                    amountHelp.textContent = 'Enter the amount you are requesting in Malaysian Ringgit';
                    amountHelp.style.color = 'var(--muted)';
                }
            }
        }

        // Update amount field when subcategory changes
        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('category');
            const subcategorySelect = document.getElementById('subcategory');
            
            if (categorySelect) {
                categorySelect.addEventListener('change', function() {
                    // Reset subcategory when category changes
                    subcategorySelect.value = '';
                    updateSubcategories();
                });
            }
            
            if (subcategorySelect) {
                subcategorySelect.addEventListener('change', updateAmountField);
            }
            
            // Validate amount on input
            const amountInput = document.getElementById('amount_applied');
            if (amountInput) {
                amountInput.addEventListener('input', function() {
                    const maxAmount = this.getAttribute('data-max-amount');
                    if (maxAmount && parseFloat(this.value) > parseFloat(maxAmount)) {
                        this.setCustomValidity('Amount exceeds the maximum limit of RM ' + parseFloat(maxAmount).toFixed(2));
                    } else {
                        this.setCustomValidity('');
                    }
                });
                
                // Validate on form submit
                const form = document.getElementById('applicationForm');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        // Re-enable disabled fields so they are submitted
                        const subcategorySelect = document.getElementById('subcategory');
                        if (subcategorySelect && subcategorySelect.disabled) {
                            subcategorySelect.disabled = false;
                        }
                        
                        const maxAmount = amountInput.getAttribute('data-max-amount');
                        if (maxAmount && parseFloat(amountInput.value) > parseFloat(maxAmount)) {
                            e.preventDefault();
                            amountInput.setCustomValidity('Amount exceeds the maximum limit of RM ' + parseFloat(maxAmount).toFixed(2));
                            amountInput.reportValidity();
                            return false;
                        }
                    });
                }
            }
        });
    </script>
</body>
</html>
