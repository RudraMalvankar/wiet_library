<?php
session_start();

// No database connection needed for frontend development
// Sample data will be used to demonstrate functionality

$admin_id = $_SESSION['admin_id'] ?? 1;
$admin_name = $_SESSION['admin_name'] ?? 'Admin User';

// Sample import history data
$import_history = [
    [
        'ImportID' => 1,
        'ImportType' => 'Books',
        'FileName' => 'computer_science_books_2024.xlsx',
        'TotalRecords' => 150,
        'SuccessfulRecords' => 147,
        'FailedRecords' => 3,
        'Status' => 'Completed',
        'ImportedBy' => 'Dr. Rajesh Kumar',
        'ImportDate' => '2024-12-15 14:30:00',
        'ProcessingTime' => '2 min 45 sec'
    ],
    [
        'ImportID' => 2,
        'ImportType' => 'Members',
        'FileName' => 'new_students_semester_7.csv',
        'TotalRecords' => 89,
        'SuccessfulRecords' => 89,
        'FailedRecords' => 0,
        'Status' => 'Completed',
        'ImportedBy' => 'Ms. Priya Patel',
        'ImportDate' => '2024-12-10 10:15:00',
        'ProcessingTime' => '1 min 23 sec'
    ],
    [
        'ImportID' => 3,
        'ImportType' => 'Holdings',
        'FileName' => 'library_inventory_update.xlsx',
        'TotalRecords' => 75,
        'SuccessfulRecords' => 70,
        'FailedRecords' => 5,
        'Status' => 'Completed with Errors',
        'ImportedBy' => 'Mr. Amit Sharma',
        'ImportDate' => '2024-12-08 16:45:00',
        'ProcessingTime' => '3 min 12 sec'
    ],
    [
        'ImportID' => 4,
        'ImportType' => 'E-Resources',
        'FileName' => 'digital_library_collection.csv',
        'TotalRecords' => 45,
        'SuccessfulRecords' => 0,
        'FailedRecords' => 45,
        'Status' => 'Failed',
        'ImportedBy' => 'Dr. Rajesh Kumar',
        'ImportDate' => '2024-12-05 09:20:00',
        'ProcessingTime' => '15 sec'
    ]
];

// Import templates configuration
$import_templates = [
    'Books' => [
        'description' => 'Import book catalog with complete bibliographic information',
        'required_fields' => ['Title', 'Author1', 'ISBN', 'Publisher', 'Subject'],
        'optional_fields' => ['SubTitle', 'Author2', 'Author3', 'Year', 'Edition', 'Keywords', 'Language'],
        'sample_file' => 'books_template.xlsx',
        'max_records' => 1000
    ],
    'Holdings' => [
        'description' => 'Import book copy details and inventory information',
        'required_fields' => ['CatNo', 'AccNo', 'CopyNo', 'Status', 'Location'],
        'optional_fields' => ['BarCode', 'Section', 'Collection', 'Binding', 'Remarks'],
        'sample_file' => 'holdings_template.xlsx',
        'max_records' => 2000
    ],
    'Members' => [
        'description' => 'Import library members with contact and group information',
        'required_fields' => ['MemberName', 'Group', 'Phone', 'Email'],
        'optional_fields' => ['Designation', 'Entitlement', 'AdmissionDate', 'FinePerDay'],
        'sample_file' => 'members_template.xlsx',
        'max_records' => 500
    ],
    'Students' => [
        'description' => 'Import student records with academic details',
        'required_fields' => ['Name', 'PRN', 'Branch', 'MemberNo'],
        'optional_fields' => ['DOB', 'BloodGroup', 'Mobile', 'Address', 'ValidTill'],
        'sample_file' => 'students_template.xlsx',
        'max_records' => 500
    ],
    'Acquisition' => [
        'description' => 'Import book acquisition and purchase records',
        'required_fields' => ['CatNo', 'Vendor', 'OrderNo', 'ItemPrice'],
        'optional_fields' => ['OrderDate', 'InvoiceNo', 'InvoiceDate', 'ProcessStatus'],
        'sample_file' => 'acquisition_template.xlsx',
        'max_records' => 300
    ],
    'E-Resources' => [
        'description' => 'Import digital resources and electronic materials',
        'required_fields' => ['Title', 'ResourceType', 'FilePath'],
        'optional_fields' => ['Author', 'Publisher', 'Year', 'UploadedBy'],
        'sample_file' => 'eresources_template.xlsx',
        'max_records' => 200
    ]
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Import Management</title>
    <style>
        .bulk-import-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #cfac69;
        }

        .bulk-import-title {
            color: #263c79;
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 15px 0;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: flex-start;
        }

        /* Desktop view */
        @media (min-width: 768px) {
            .bulk-import-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .bulk-import-title {
                margin: 0;
            }
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            flex-shrink: 0;
            white-space: nowrap;
        }

        .btn-primary {
            background-color: #263c79;
            color: white;
        }

        .btn-primary:hover {
            background-color: #1e2d5f;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .btn-info {
            background-color: #17a2b8;
            color: white;
        }

        .btn-info:hover {
            background-color: #138496;
        }

        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .btn-warning:hover {
            background-color: #e0a800;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #cfac69;
        }

        .stat-card.success {
            border-left-color: #28a745;
        }

        .stat-card.error {
            border-left-color: #dc3545;
        }

        .stat-card.processing {
            border-left-color: #ffc107;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #263c79;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #6c757d;
            font-size: 14px;
            font-weight: 500;
        }

        .tabs-container {
            margin-bottom: 20px;
        }

        .tab-buttons {
            display: flex;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 20px;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 12px 20px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            color: #6c757d;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .tab-btn.active {
            color: #263c79;
            border-bottom-color: #cfac69;
            font-weight: 600;
        }

        .tab-btn:hover {
            color: #263c79;
            background-color: rgba(207, 172, 105, 0.1);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .import-templates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .template-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .template-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-color: #cfac69;
        }

        .template-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .template-title {
            color: #263c79;
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }

        .template-icon {
            font-size: 24px;
            color: #cfac69;
        }

        .template-description {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .template-fields {
            margin-bottom: 15px;
        }

        .field-group {
            margin-bottom: 10px;
        }

        .field-label {
            font-size: 12px;
            font-weight: 600;
            color: #495057;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .field-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .field-tag {
            background: rgba(38, 60, 121, 0.1);
            color: #263c79;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 500;
        }

        .field-tag.required {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .template-actions {
            display: flex;
            gap: 10px;
        }

        .template-limit {
            font-size: 12px;
            color: #6c757d;
            margin-top: 10px;
            text-align: center;
            font-style: italic;
        }

        .upload-area {
            border: 2px dashed #cfac69;
            border-radius: 8px;
            padding: 40px 20px;
            text-align: center;
            background: #f8f9fa;
            margin: 20px 0;
            transition: all 0.3s ease;
        }

        .upload-area.dragover {
            background: rgba(207, 172, 105, 0.1);
            border-color: #263c79;
        }

        .upload-icon {
            font-size: 48px;
            color: #cfac69;
            margin-bottom: 15px;
        }

        .upload-text {
            color: #263c79;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .upload-subtext {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .file-input {
            display: none;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .history-table th {
            background-color: #263c79;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .history-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
        }

        .history-table tr:hover {
            background-color: rgba(207, 172, 105, 0.1);
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-processing {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-errors {
            background-color: #ffeaa7;
            color: #b7791f;
        }

        .progress-bar {
            width: 100%;
            height: 20px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #263c79, #cfac69);
            border-radius: 10px;
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            font-weight: 600;
        }

        .validation-results {
            margin: 20px 0;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #17a2b8;
            background: #f0f8ff;
        }

        .validation-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .validation-item:last-child {
            margin-bottom: 0;
        }

        .error-list {
            background: #fff5f5;
            border: 1px solid #fed7d7;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
        }

        .error-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            font-size: 14px;
            color: #c53030;
        }

        .error-item:last-child {
            margin-bottom: 0;
        }

        .error-icon {
            margin-right: 8px;
        }

        .mapping-section {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .mapping-row {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 15px;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            background: white;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }

        .mapping-arrow {
            color: #cfac69;
            font-size: 18px;
        }

        @media (max-width: 768px) {
            .import-templates-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .template-actions {
                flex-direction: column;
            }

            .history-table {
                font-size: 12px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .tab-buttons {
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>
</head>

<body>
    <div class="bulk-import-header">
        <h1 class="bulk-import-title">
            <i class="fas fa-upload"></i>
            Bulk Import Management
        </h1>
        <div class="action-buttons">
            <button class="btn btn-info" onclick="downloadTemplate()">
                <i class="fas fa-download"></i>
                Download Templates
            </button>
            <button class="btn btn-warning" onclick="validateData()">
                <i class="fas fa-check-circle"></i>
                Validate Data
            </button>
            <button class="btn btn-primary" onclick="viewGuidelines()">
                <i class="fas fa-book"></i>
                Import Guidelines
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number" id="totalImports">-</div>
            <div class="stat-label">Total Imports</div>
        </div>
        <div class="stat-card success">
            <div class="stat-number" id="successfulImports">-</div>
            <div class="stat-label">Successful Imports</div>
        </div>
        <div class="stat-card error">
            <div class="stat-number" id="failedImports">-</div>
            <div class="stat-label">Failed Imports</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="totalRecords">-</div>
            <div class="stat-label">Records Processed</div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs-container">
        <div class="tab-buttons">
            <button class="tab-btn active" onclick="showTab('import-data')">
                <i class="fas fa-cloud-upload-alt"></i>
                Import Data
            </button>
            <button class="tab-btn" onclick="showTab('import-history')">
                <i class="fas fa-history"></i>
                Import History
            </button>
            <button class="tab-btn" onclick="showTab('field-mapping')">
                <i class="fas fa-exchange-alt"></i>
                Field Mapping
            </button>
            <button class="tab-btn" onclick="showTab('validation-rules')">
                <i class="fas fa-shield-alt"></i>
                Validation Rules
            </button>
        </div>

        <!-- Import Data Tab -->
        <div id="import-data" class="tab-content active">
            <div style="margin-bottom: 20px;">
                <h3 style="color: #263c79; margin-bottom: 10px;">Select Import Type</h3>
                <p style="color: #6c757d; margin-bottom: 20px;">Choose the type of data you want to import and download the appropriate template.</p>
            </div>

            <div class="import-templates-grid">
                <?php foreach ($import_templates as $type => $template): ?>
                    <div class="template-card" onclick="selectImportType('<?php echo $type; ?>')">
                        <div class="template-header">
                            <h4 class="template-title"><?php echo $type; ?></h4>
                            <i class="template-icon fas fa-<?php echo getTemplateIcon($type); ?>"></i>
                        </div>

                        <div class="template-description">
                            <?php echo $template['description']; ?>
                        </div>

                        <div class="template-fields">
                            <div class="field-group">
                                <div class="field-label">Required Fields</div>
                                <div class="field-tags">
                                    <?php foreach ($template['required_fields'] as $field): ?>
                                        <span class="field-tag required"><?php echo $field; ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="field-group">
                                <div class="field-label">Optional Fields</div>
                                <div class="field-tags">
                                    <?php foreach (array_slice($template['optional_fields'], 0, 4) as $field): ?>
                                        <span class="field-tag"><?php echo $field; ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($template['optional_fields']) > 4): ?>
                                        <span class="field-tag">+<?php echo count($template['optional_fields']) - 4; ?> more</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="template-actions">
                            <button class="btn btn-primary" onclick="event.stopPropagation(); downloadSampleFile('<?php echo $type; ?>')" style="flex: 1;">
                                <i class="fas fa-download"></i>
                                Download Template
                            </button>
                            <button class="btn btn-success" onclick="event.stopPropagation(); startImport('<?php echo $type; ?>')" style="flex: 1;">
                                <i class="fas fa-upload"></i>
                                Import Now
                            </button>
                        </div>

                        <div class="template-limit">
                            Maximum <?php echo number_format($template['max_records']); ?> records per import
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Upload Area (Hidden by default, shown when import type is selected) -->
            <div id="uploadSection" style="display: none;">
                <h3 style="color: #263c79; margin-bottom: 15px;">
                    Upload <span id="selectedImportType">Data</span> File
                </h3>

                <div class="upload-area" id="uploadArea" ondrop="handleDrop(event)" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)">
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <div class="upload-text">Drag & Drop your file here</div>
                    <div class="upload-subtext">or click to browse files</div>
                    <button class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
                        <i class="fas fa-folder-open"></i>
                        Choose File
                    </button>
                    <input type="file" id="fileInput" class="file-input" accept=".xlsx,.xls,.csv" onchange="handleFileSelect(event)">
                </div>

                <div id="fileInfo" style="display: none;">
                    <!-- File information will be displayed here -->
                </div>

                <div id="validationResults" style="display: none;">
                    <!-- Validation results will be displayed here -->
                </div>

                <div id="importProgress" style="display: none;">
                    <h4 style="color: #263c79;">Import Progress</h4>
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill" style="width: 0%;">0%</div>
                    </div>
                    <div id="progressStatus" style="text-align: center; margin-top: 10px; color: #6c757d;">
                        Preparing import...
                    </div>
                </div>
            </div>
        </div>

        <!-- Import History Tab -->
        <div id="import-history" class="tab-content">
            <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="color: #263c79; margin: 0;">Import History</h3>
                <button class="btn btn-info" onclick="exportImportLog()">
                    <i class="fas fa-download"></i>
                    Export Log
                </button>
            </div>

            <table class="history-table">
                <thead>
                    <tr>
                        <th>Import Type</th>
                        <th>File Name</th>
                        <th>Records</th>
                        <th>Success Rate</th>
                        <th>Status</th>
                        <th>Imported By</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($import_history as $import): ?>
                        <tr>
                            <td>
                                <strong><?php echo $import['ImportType']; ?></strong>
                            </td>
                            <td>
                                <i class="fas fa-file-excel" style="color: #28a745; margin-right: 5px;"></i>
                                <?php echo $import['FileName']; ?>
                            </td>
                            <td>
                                <div style="font-size: 13px;">
                                    <div>Total: <strong><?php echo number_format($import['TotalRecords']); ?></strong></div>
                                    <div style="color: #28a745;">Success: <?php echo number_format($import['SuccessfulRecords']); ?></div>
                                    <?php if ($import['FailedRecords'] > 0): ?>
                                        <div style="color: #dc3545;">Failed: <?php echo number_format($import['FailedRecords']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php $successRate = ($import['TotalRecords'] > 0) ? round(($import['SuccessfulRecords'] / $import['TotalRecords']) * 100, 1) : 0; ?>
                                <strong style="color: <?php echo $successRate >= 95 ? '#28a745' : ($successRate >= 80 ? '#ffc107' : '#dc3545'); ?>">
                                    <?php echo $successRate; ?>%
                                </strong>
                            </td>
                            <td>
                                <?php
                                $statusClass = 'status-completed';
                                if ($import['Status'] === 'Failed') $statusClass = 'status-failed';
                                elseif ($import['Status'] === 'Processing') $statusClass = 'status-processing';
                                elseif (strpos($import['Status'], 'Error') !== false) $statusClass = 'status-errors';
                                ?>
                                <span class="status-badge <?php echo $statusClass; ?>"><?php echo $import['Status']; ?></span>
                            </td>
                            <td><?php echo $import['ImportedBy']; ?></td>
                            <td>
                                <div style="font-size: 13px;">
                                    <div><?php echo date('M j, Y', strtotime($import['ImportDate'])); ?></div>
                                    <div style="color: #6c757d;"><?php echo date('g:i A', strtotime($import['ImportDate'])); ?></div>
                                </div>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <button class="btn btn-info" onclick="viewImportDetails(<?php echo $import['ImportID']; ?>)" style="padding: 4px 8px; font-size: 12px;">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($import['FailedRecords'] > 0): ?>
                                        <button class="btn btn-warning" onclick="downloadErrorReport(<?php echo $import['ImportID']; ?>)" style="padding: 4px 8px; font-size: 12px;">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-secondary" onclick="reprocessImport(<?php echo $import['ImportID']; ?>)" style="padding: 4px 8px; font-size: 12px;">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Field Mapping Tab -->
        <div id="field-mapping" class="tab-content">
            <div style="margin-bottom: 20px;">
                <h3 style="color: #263c79; margin-bottom: 10px;">Field Mapping Configuration</h3>
                <p style="color: #6c757d;">Map your file columns to database fields. This helps when your file headers don't exactly match our expected field names.</p>
            </div>

            <div class="mapping-section">
                <h4 style="color: #263c79; margin-bottom: 15px;">Sample Mapping: Books Import</h4>

                <div class="mapping-row">
                    <div>
                        <strong>Your File Column:</strong><br>
                        <span style="color: #6c757d;">Book Title</span>
                    </div>
                    <div class="mapping-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                    <div>
                        <strong>Database Field:</strong><br>
                        <span style="color: #263c79;">Title</span>
                    </div>
                </div>

                <div class="mapping-row">
                    <div>
                        <strong>Your File Column:</strong><br>
                        <span style="color: #6c757d;">Primary Author</span>
                    </div>
                    <div class="mapping-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                    <div>
                        <strong>Database Field:</strong><br>
                        <span style="color: #263c79;">Author1</span>
                    </div>
                </div>

                <div class="mapping-row">
                    <div>
                        <strong>Your File Column:</strong><br>
                        <span style="color: #6c757d;">ISBN Number</span>
                    </div>
                    <div class="mapping-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                    <div>
                        <strong>Database Field:</strong><br>
                        <span style="color: #263c79;">ISBN</span>
                    </div>
                </div>

                <button class="btn btn-primary" onclick="createCustomMapping()" style="margin-top: 15px;">
                    <i class="fas fa-plus"></i>
                    Create Custom Mapping
                </button>
            </div>
        </div>

        <!-- Validation Rules Tab -->
        <div id="validation-rules" class="tab-content">
            <div style="margin-bottom: 20px;">
                <h3 style="color: #263c79; margin-bottom: 10px;">Data Validation Rules</h3>
                <p style="color: #6c757d;">Review the validation rules applied during import to ensure data integrity.</p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <div style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px;">
                    <h4 style="color: #263c79; margin-bottom: 15px;">
                        <i class="fas fa-book" style="margin-right: 8px; color: #cfac69;"></i>
                        Books Validation
                    </h4>
                    <ul style="color: #6c757d; font-size: 14px; line-height: 1.6;">
                        <li>Title must not be empty and max 255 characters</li>
                        <li>ISBN format validation (10 or 13 digits)</li>
                        <li>Publication year between 1000 and current year</li>
                        <li>Author names must not contain numbers</li>
                        <li>Subject classification validation</li>
                    </ul>
                </div>

                <div style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px;">
                    <h4 style="color: #263c79; margin-bottom: 15px;">
                        <i class="fas fa-users" style="margin-right: 8px; color: #cfac69;"></i>
                        Members Validation
                    </h4>
                    <ul style="color: #6c757d; font-size: 14px; line-height: 1.6;">
                        <li>Email format validation</li>
                        <li>Phone number format (10 digits)</li>
                        <li>Member group must be valid (Student/Faculty/Staff)</li>
                        <li>Duplicate email/phone detection</li>
                        <li>Fine amount must be numeric and positive</li>
                    </ul>
                </div>

                <div style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px;">
                    <h4 style="color: #263c79; margin-bottom: 15px;">
                        <i class="fas fa-warehouse" style="margin-right: 8px; color: #cfac69;"></i>
                        Holdings Validation
                    </h4>
                    <ul style="color: #6c757d; font-size: 14px; line-height: 1.6;">
                        <li>Accession number uniqueness check</li>
                        <li>Valid CatNo reference in Books table</li>
                        <li>Status must be predefined value</li>
                        <li>Location format validation</li>
                        <li>Barcode uniqueness verification</li>
                    </ul>
                </div>

                <div style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px;">
                    <h4 style="color: #263c79; margin-bottom: 15px;">
                        <i class="fas fa-graduation-cap" style="margin-right: 8px; color: #cfac69;"></i>
                        Students Validation
                    </h4>
                    <ul style="color: #6c757d; font-size: 14px; line-height: 1.6;">
                        <li>PRN uniqueness validation</li>
                        <li>Valid branch code verification</li>
                        <li>Date of birth logical range check</li>
                        <li>Mobile number format validation</li>
                        <li>Aadhaar number format (12 digits)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        const importHistory = <?php echo json_encode($import_history); ?>;
        const importTemplates = <?php echo json_encode($import_templates); ?>;
        let selectedImportType = null;
        let currentFile = null;

        // PHP function to get template icons
        <?php
        function getTemplateIcon($type)
        {
            $icons = [
                'Books' => 'book',
                'Holdings' => 'warehouse',
                'Members' => 'users',
                'Students' => 'graduation-cap',
                'Acquisition' => 'shopping-cart',
                'E-Resources' => 'cloud'
            ];
            return $icons[$type] ?? 'file';
        }
        ?>

        // Tab functionality
        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }

        // Import type selection
        function selectImportType(type) {
            selectedImportType = type;
            document.getElementById('selectedImportType').textContent = type;
            document.getElementById('uploadSection').style.display = 'block';
            document.getElementById('uploadSection').scrollIntoView({
                behavior: 'smooth'
            });
        }

        function startImport(type) {
            selectImportType(type);
        }

        // File handling
        function handleDragOver(event) {
            event.preventDefault();
            document.getElementById('uploadArea').classList.add('dragover');
        }

        function handleDragLeave(event) {
            event.preventDefault();
            document.getElementById('uploadArea').classList.remove('dragover');
        }

        function handleDrop(event) {
            event.preventDefault();
            document.getElementById('uploadArea').classList.remove('dragover');

            const files = event.dataTransfer.files;
            if (files.length > 0) {
                handleFile(files[0]);
            }
        }

        function handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                handleFile(file);
            }
        }

        function handleFile(file) {
            currentFile = file;

            // Validate file type
            const validTypes = ['.xlsx', '.xls', '.csv'];
            const fileExtension = '.' + file.name.split('.').pop().toLowerCase();

            if (!validTypes.includes(fileExtension)) {
                alert('Please select a valid Excel (.xlsx, .xls) or CSV (.csv) file.');
                return;
            }

            // Validate file size (max 10MB)
            if (file.size > 10 * 1024 * 1024) {
                alert('File size must be less than 10MB.');
                return;
            }

            displayFileInfo(file);
            validateFile(file);
        }

        function displayFileInfo(file) {
            const fileInfo = document.getElementById('fileInfo');
            fileInfo.style.display = 'block';

            fileInfo.innerHTML = `
                <div style="background: #e7f3ff; border: 1px solid #b8daff; border-radius: 6px; padding: 15px; margin: 15px 0;">
                    <h4 style="color: #004085; margin-bottom: 10px;">
                        <i class="fas fa-file-excel" style="margin-right: 8px;"></i>
                        Selected File
                    </h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                        <div><strong>Name:</strong> ${file.name}</div>
                        <div><strong>Size:</strong> ${formatFileSize(file.size)}</div>
                        <div><strong>Type:</strong> ${file.type || 'Unknown'}</div>
                        <div><strong>Last Modified:</strong> ${new Date(file.lastModified).toLocaleString()}</div>
                    </div>
                    <div style="margin-top: 15px;">
                        <button class="btn btn-success" onclick="processImport()" style="margin-right: 10px;">
                            <i class="fas fa-play"></i>
                            Process Import
                        </button>
                        <button class="btn btn-secondary" onclick="clearFile()">
                            <i class="fas fa-times"></i>
                            Clear
                        </button>
                    </div>
                </div>
            `;
        }

        function validateFile(file) {
            const validationResults = document.getElementById('validationResults');
            validationResults.style.display = 'block';

            // Simulate file validation
            setTimeout(() => {
                const template = importTemplates[selectedImportType];
                const mockValidation = {
                    totalRows: Math.floor(Math.random() * 200) + 50,
                    validRows: 0,
                    errors: []
                };

                mockValidation.validRows = mockValidation.totalRows - Math.floor(Math.random() * 10);

                if (mockValidation.validRows < mockValidation.totalRows) {
                    mockValidation.errors = [
                        'Row 15: Missing required field "Title"',
                        'Row 23: Invalid ISBN format',
                        'Row 47: Publication year out of range'
                    ];
                }

                validationResults.innerHTML = `
                    <div class="validation-results">
                        <h4 style="color: #263c79; margin-bottom: 15px;">
                            <i class="fas fa-check-circle" style="color: #28a745; margin-right: 8px;"></i>
                            Validation Results
                        </h4>
                        <div class="validation-item">
                            <span>Total Rows Found:</span>
                            <strong>${mockValidation.totalRows.toLocaleString()}</strong>
                        </div>
                        <div class="validation-item">
                            <span>Valid Rows:</span>
                            <strong style="color: #28a745;">${mockValidation.validRows.toLocaleString()}</strong>
                        </div>
                        <div class="validation-item">
                            <span>Rows with Errors:</span>
                            <strong style="color: #dc3545;">${(mockValidation.totalRows - mockValidation.validRows).toLocaleString()}</strong>
                        </div>
                        <div class="validation-item">
                            <span>Maximum Allowed:</span>
                            <strong>${template.max_records.toLocaleString()}</strong>
                        </div>
                        ${mockValidation.errors.length > 0 ? `
                            <div class="error-list">
                                <h5 style="color: #c53030; margin-bottom: 10px;">Validation Errors:</h5>
                                ${mockValidation.errors.map(error => `
                                    <div class="error-item">
                                        <i class="fas fa-exclamation-circle error-icon"></i>
                                        ${error}
                                    </div>
                                `).join('')}
                            </div>
                        ` : ''}
                    </div>
                `;
            }, 1500);
        }

        function processImport() {
            if (!currentFile || !selectedImportType) {
                alert('Please select a file and import type first.');
                return;
            }

            const progressSection = document.getElementById('importProgress');
            progressSection.style.display = 'block';
            progressSection.scrollIntoView({
                behavior: 'smooth'
            });

            // Simulate import process
            simulateImportProgress();
        }

        function simulateImportProgress() {
            const progressFill = document.getElementById('progressFill');
            const progressStatus = document.getElementById('progressStatus');

            const stages = [{
                    progress: 10,
                    status: 'Validating file format...'
                },
                {
                    progress: 25,
                    status: 'Reading data rows...'
                },
                {
                    progress: 40,
                    status: 'Validating field mappings...'
                },
                {
                    progress: 60,
                    status: 'Processing records...'
                },
                {
                    progress: 80,
                    status: 'Creating database entries...'
                },
                {
                    progress: 95,
                    status: 'Finalizing import...'
                },
                {
                    progress: 100,
                    status: 'Import completed successfully!'
                }
            ];

            let currentStage = 0;

            const updateProgress = () => {
                if (currentStage < stages.length) {
                    const stage = stages[currentStage];
                    progressFill.style.width = stage.progress + '%';
                    progressFill.textContent = stage.progress + '%';
                    progressStatus.textContent = stage.status;

                    currentStage++;
                    setTimeout(updateProgress, 1000 + Math.random() * 1000);
                } else {
                    // Import completed
                    setTimeout(() => {
                        alert('Import completed successfully! Check the Import History tab for details.');
                        // Reset the form
                        clearFile();
                        document.getElementById('importProgress').style.display = 'none';
                        // Refresh statistics
                        loadStatistics();
                    }, 1000);
                }
            };

            updateProgress();
        }

        function clearFile() {
            currentFile = null;
            document.getElementById('fileInput').value = '';
            document.getElementById('fileInfo').style.display = 'none';
            document.getElementById('validationResults').style.display = 'none';
            document.getElementById('uploadSection').style.display = 'none';
        }

        // Utility functions
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Action functions
        function downloadTemplate() {
            console.log('Downloading import templates...');
            alert('Downloading all import templates as ZIP file...');
        }

        function downloadSampleFile(type) {
            console.log('Downloading sample file for:', type);
            alert(`Downloading ${type} template file...`);
        }

        function validateData() {
            console.log('Opening data validation tool...');
            alert('Opening advanced data validation interface...');
        }

        function viewGuidelines() {
            console.log('Opening import guidelines...');
            alert('Opening comprehensive import guidelines and best practices...');
        }

        function viewImportDetails(importId) {
            console.log('Viewing import details:', importId);
            alert(`Opening detailed view for Import ID: ${importId}`);
        }

        function downloadErrorReport(importId) {
            console.log('Downloading error report:', importId);
            alert(`Downloading error report for Import ID: ${importId}`);
        }

        function reprocessImport(importId) {
            if (confirm('Are you sure you want to reprocess this import?')) {
                console.log('Reprocessing import:', importId);
                alert(`Reprocessing Import ID: ${importId}...`);
            }
        }

        function exportImportLog() {
            console.log('Exporting import log...');
            alert('Exporting complete import history to CSV...');
        }

        function createCustomMapping() {
            console.log('Creating custom field mapping...');
            alert('Opening custom field mapping interface...');
        }

        // Load statistics
        function loadStatistics() {
            const totalImports = importHistory.length;
            const successfulImports = importHistory.filter(i => i.Status === 'Completed').length;
            const failedImports = importHistory.filter(i => i.Status === 'Failed').length;
            const totalRecords = importHistory.reduce((sum, i) => sum + i.TotalRecords, 0);

            document.getElementById('totalImports').textContent = totalImports.toLocaleString();
            document.getElementById('successfulImports').textContent = successfulImports.toLocaleString();
            document.getElementById('failedImports').textContent = failedImports.toLocaleString();
            document.getElementById('totalRecords').textContent = totalRecords.toLocaleString();
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadStatistics();
        });
    </script>
</body>

</html>