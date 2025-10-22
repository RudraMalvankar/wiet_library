<?php
session_start();

// No database connection needed for frontend development
// Sample data will be used to demonstrate functionality

$admin_id = $_SESSION['admin_id'] ?? 1;
$admin_name = $_SESSION['admin_name'] ?? 'Admin User';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .student-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #cfac69;
        }

        .student-title {
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
            .student-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .student-title {
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

        .add-student-section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            border: 1px solid #e9ecef;
        }

        .add-student-section .section-title {
            color: #263c79;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #cfac69;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-filters {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }

        .search-row {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            min-width: 200px;
        }

        .form-group label {
            font-weight: 600;
            color: #263c79;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .form-control {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-control:focus {
            outline: none;
            border-color: #cfac69;
            box-shadow: 0 0 0 2px rgba(207, 172, 105, 0.2);
        }

        .students-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .students-table th {
            background-color: #263c79;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .students-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
        }

        .students-table tr:hover {
            background-color: rgba(207, 172, 105, 0.1);
        }

        .action-links {
            display: flex;
            gap: 8px;
        }

        .action-links a {
            padding: 4px 8px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
        }

        .btn-view {
            background-color: #17a2b8;
            color: white;
        }

        .btn-edit {
            background-color: #ffc107;
            color: #212529;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-suspended {
            background-color: #fff3cd;
            color: #856404;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 8px;
            width: 95%;
            max-width: 900px;
            max-height: 85vh;
            overflow-y: auto;
            margin-top: 160px;
            /* Add top margin to clear navbar */
            position: relative;
            z-index: 1001;
            /* Ensure modal is above navbar */
        }

        .modal-header {
            background-color: #263c79;
            color: white;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .close {
            color: white;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            background: none;
            border: none;
        }

        .close:hover {
            opacity: 0.7;
        }

        .modal-body {
            padding: 25px;
        }

        .form-section {
            margin-bottom: 25px;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            background: #f8f9fa;
        }

        .section-title {
            color: #263c79;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #cfac69;
        }

        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .form-group-modal {
            flex: 1;
            min-width: 250px;
        }

        .form-group-modal label {
            display: block;
            font-weight: 600;
            color: #263c79;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .form-group-modal input,
        .form-group-modal select,
        .form-group-modal textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-group-modal textarea {
            min-height: 80px;
            resize: vertical;
        }

        .required {
            color: #dc3545;
        }

        .photo-upload {
            text-align: center;
            border: 2px dashed #cfac69;
            padding: 20px;
            border-radius: 6px;
            background: white;
        }

        .photo-preview {
            width: 120px;
            height: 150px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            overflow: hidden;
        }

        .photo-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }

        .qr-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .qr-preview {
            text-align: center;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            background: white;
        }

        .qr-code {
            width: 150px;
            height: 150px;
            border: 1px solid #ddd;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
        }

        .modal-footer {
            padding: 15px 25px;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .form-actions {
            padding: 20px 0 0 0;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }

        .page-link {
            padding: 8px 12px;
            border: 1px solid #ddd;
            color: #263c79;
            text-decoration: none;
            border-radius: 4px;
        }

        .page-link:hover,
        .page-link.active {
            background-color: #263c79;
            color: white;
        }

        @media (max-width: 767px) {
            .search-row {
                flex-direction: column;
            }

            .form-group {
                min-width: 100%;
            }

            .students-table {
                font-size: 12px;
            }

            .tab-buttons {
                overflow-x: auto;
                white-space: nowrap;
            }

            .qr-section {
                grid-template-columns: 1fr;
            }

            .form-row {
                flex-direction: column;
            }

            .form-group-modal {
                min-width: 100%;
            }

            .modal-content {
                margin-top: 150px;
                /* Adjust for mobile navbar */
                width: 98%;
                max-height: 80vh;
            }
        }

        @media (max-width: 480px) {
            .modal-content {
                margin-top: 135px;
                /* Adjust for smaller mobile navbar */
                width: 99%;
                max-height: 75vh;
            }
        }
    </style>
</head>

<body>
    <div class="student-header">
        <h1 class="student-title">
            <i class="fas fa-user-graduate"></i>
            Student Management
        </h1>
        <div class="action-buttons">
            <button class="btn btn-info" onclick="generateReports()">
                <i class="fas fa-chart-line"></i>
                Generate Report
            </button>
            <button class="btn btn-warning" onclick="bulkOperations()">
                <i class="fas fa-tasks"></i>
                Bulk Operations
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number" id="totalStudents">-</div>
            <div class="stat-label">Total Students</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="activeMembers">-</div>
            <div class="stat-label">Active Members</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="booksIssued">-</div>
            <div class="stat-label">Books Currently Issued</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="overdueBooks">-</div>
            <div class="stat-label">Overdue Books</div>
        </div>
    </div>

    <!-- Add New Student Section -->
    <div class="add-student-section">
        <h3 class="section-title">
            <i class="fas fa-user-plus"></i>
            Add New Student
        </h3>
        <form id="addStudentInlineForm">
            <!-- Member Information Section -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-user"></i>
                    Member Information
                </div>
                <div class="form-row">
                    <div class="form-group-modal">
                        <label for="memberNameInline">Full Name <span class="required">*</span></label>
                        <input type="text" id="memberNameInline" name="MemberName" required>
                    </div>
                    <div class="form-group-modal">
                        <label for="memberGroupInline">Member Group <span class="required">*</span></label>
                        <select id="memberGroupInline" name="Group" required>
                            <option value="">Select Group</option>
                            <option value="Student">Student</option>
                            <option value="Faculty">Faculty</option>
                            <option value="Staff">Staff</option>
                            <option value="Guest">Guest</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group-modal">
                        <label for="memberPhoneInline">Phone Number</label>
                        <input type="tel" id="memberPhoneInline" name="Phone">
                    </div>
                    <div class="form-group-modal">
                        <label for="memberEmailInline">Email Address</label>
                        <input type="email" id="memberEmailInline" name="Email">
                    </div>
                    <div class="form-group-modal">
                        <label for="memberDesignationInline">Designation</label>
                        <input type="text" id="memberDesignationInline" name="Designation">
                    </div>
                </div>
            </div>

            <!-- Student Details Section -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-graduation-cap"></i>
                    Student Details
                </div>
                <div class="form-row">
                    <div class="form-group-modal">
                        <label for="studentNameInline">Student Name <span class="required">*</span></label>
                        <input type="text" id="studentNameInline" name="Name" required>
                    </div>
                    <div class="form-group-modal">
                        <label for="studentPRNInline">PRN <span class="required">*</span></label>
                        <input type="text" id="studentPRNInline" name="PRN" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group-modal">
                        <label for="studentDOBInline">Date of Birth</label>
                        <input type="date" id="studentDOBInline" name="DOB">
                    </div>
                    <div class="form-group-modal">
                        <label for="studentBranchInline">Branch <span class="required">*</span></label>
                        <select id="studentBranchInline" name="Branch" required>
                            <option value="">Select Branch</option>
                            <option value="Computer Engineering">Computer Engineering</option>
                            <option value="Mechanical Engineering">Mechanical Engineering</option>
                            <option value="Electronics Engineering">Electronics Engineering</option>
                            <option value="Civil Engineering">Civil Engineering</option>
                            <option value="Information Technology">Information Technology</option>
                        </select>
                    </div>
                    <div class="form-group-modal">
                        <label for="studentBloodGroupInline">Blood Group</label>
                        <select id="studentBloodGroupInline" name="BloodGroup">
                            <option value="">Select Blood Group</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group-modal">
                        <label for="studentMobileInline">Mobile Number</label>
                        <input type="tel" id="studentMobileInline" name="Mobile">
                    </div>
                    <div class="form-group-modal">
                        <label for="studentAadhaarInline">Aadhaar Number</label>
                        <input type="text" id="studentAadhaarInline" name="Aadhaar" maxlength="12">
                    </div>
                    <div class="form-group-modal">
                        <label for="validTillInline">Valid Till</label>
                        <input type="date" id="validTillInline" name="ValidTill">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group-modal">
                        <label for="studentAddressInline">Address</label>
                        <textarea id="studentAddressInline" name="Address" rows="3"></textarea>
                    </div>
                </div>
            </div>

            <!-- Photo Upload Section -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-camera"></i>
                    Student Photo
                </div>
                <div class="photo-upload">
                    <div class="photo-preview" id="photoPreviewInline">
                        <i class="fas fa-user" style="font-size: 48px; color: #ccc;"></i>
                    </div>
                    <input type="file" id="studentPhotoInline" name="Photo" accept="image/*" style="display: none;" onchange="previewPhoto(this, 'photoPreviewInline')">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('studentPhotoInline').click()">
                        <i class="fas fa-upload"></i>
                        Upload Photo
                    </button>
                    <p style="font-size: 12px; color: #6c757d; margin-top: 10px;">
                        Recommended: 120x150 pixels, JPG/PNG format
                    </p>
                </div>
            </div>
            
            <!-- Form Actions -->
            <div class="form-actions" style="justify-content:flex-start;">
                <button type="submit" class="btn btn-success" onclick="saveStudentInline(); return false;">
                    <i class="fas fa-paper-plane"></i>
                    Create Student & Generate QR
                </button>
            </div>
        </form>
    </div>

    <!-- Tabs -->
    <div class="tabs-container">
        <div class="tab-buttons">
            <button class="tab-btn active" onclick="showTab('students')">
                <i class="fas fa-user-graduate"></i>
                Students & Members
            </button>
            <button class="tab-btn" onclick="showTab('membership')">
                <i class="fas fa-id-card"></i>
                Membership Management
            </button>
            <button class="tab-btn" onclick="showTab('verification')">
                <i class="fas fa-check-circle"></i>
                Verification & QR Codes
            </button>
            <button class="tab-btn" onclick="showTab('reports')">
                <i class="fas fa-chart-pie"></i>
                Reports & Analytics
            </button>
        </div>

        <!-- Students & Members Tab -->
        <div id="students" class="tab-content active">
            <div class="search-filters">
                <div class="search-row">
                    <div class="form-group">
                        <label for="searchName">Student Name</label>
                        <input type="text" id="searchName" class="form-control" placeholder="Search by name...">
                    </div>
                    <div class="form-group">
                        <label for="searchPRN">PRN</label>
                        <input type="text" id="searchPRN" class="form-control" placeholder="Search by PRN...">
                    </div>
                    <div class="form-group">
                        <label for="searchBranch">Branch</label>
                        <select id="searchBranch" class="form-control">
                            <option value="">All Branches</option>
                            <option value="Computer Engineering">Computer Engineering</option>
                            <option value="Mechanical Engineering">Mechanical Engineering</option>
                            <option value="Electronics Engineering">Electronics Engineering</option>
                            <option value="Civil Engineering">Civil Engineering</option>
                            <option value="Information Technology">Information Technology</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="searchStatus">Status</label>
                        <select id="searchStatus" class="form-control">
                            <option value="">All Status</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Suspended">Suspended</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-primary" onclick="searchStudents()">
                            <i class="fas fa-search"></i>
                            Search
                        </button>
                    </div>
                </div>
            </div>

            <div id="studentsTableContainer">
                <!-- Students table will be loaded here -->
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i>
                    <p style="margin-top: 10px;">Loading students...</p>
                </div>
            </div>
        </div>

        <!-- Membership Management Tab -->
        <div id="membership" class="tab-content">
            <div id="membershipContent">
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-id-card" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <h3>Membership Management</h3>
                    <p>Manage membership types, validity periods, and member privileges.</p>
                </div>
            </div>
        </div>

        <!-- Verification & QR Codes Tab -->
        <div id="verification" class="tab-content">
            <div id="verificationContent">
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-check-circle" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <h3>Verification & QR Codes</h3>
                    <p>Manage student verification status and regenerate QR codes.</p>
                </div>
            </div>
        </div>

        <!-- Reports Tab -->
        <div id="reports" class="tab-content">
            <div id="reportsContent">
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-chart-pie" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <h3>Student Reports</h3>
                    <p>Generate comprehensive reports on student data and activity.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div id="addStudentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add New Student</h3>
                <button class="close" onclick="closeModal('addStudentModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addStudentForm">
                    <!-- Member Information Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-user"></i>
                            Member Information
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="memberName">Full Name <span class="required">*</span></label>
                                <input type="text" id="memberName" name="MemberName" required>
                            </div>
                            <div class="form-group-modal">
                                <label for="memberGroup">Member Group <span class="required">*</span></label>
                                <select id="memberGroup" name="Group" required>
                                    <option value="">Select Group</option>
                                    <option value="Student">Student</option>
                                    <option value="Faculty">Faculty</option>
                                    <option value="Staff">Staff</option>
                                    <option value="Guest">Guest</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="memberPhone">Phone Number</label>
                                <input type="tel" id="memberPhone" name="Phone">
                            </div>
                            <div class="form-group-modal">
                                <label for="memberEmail">Email Address</label>
                                <input type="email" id="memberEmail" name="Email">
                            </div>
                            <div class="form-group-modal">
                                <label for="memberDesignation">Designation</label>
                                <input type="text" id="memberDesignation" name="Designation">
                            </div>
                        </div>
                    </div>

                    <!-- Student Details Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-graduation-cap"></i>
                            Student Details
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="studentName">Student Name <span class="required">*</span></label>
                                <input type="text" id="studentName" name="Name" required>
                            </div>
                            <div class="form-group-modal">
                                <label for="studentPRN">PRN <span class="required">*</span></label>
                                <input type="text" id="studentPRN" name="PRN" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="studentDOB">Date of Birth</label>
                                <input type="date" id="studentDOB" name="DOB">
                            </div>
                            <div class="form-group-modal">
                                <label for="studentBranch">Branch <span class="required">*</span></label>
                                <select id="studentBranch" name="Branch" required>
                                    <option value="">Select Branch</option>
                                    <option value="Computer Engineering">Computer Engineering</option>
                                    <option value="Mechanical Engineering">Mechanical Engineering</option>
                                    <option value="Electronics Engineering">Electronics Engineering</option>
                                    <option value="Civil Engineering">Civil Engineering</option>
                                    <option value="Information Technology">Information Technology</option>
                                </select>
                            </div>
                            <div class="form-group-modal">
                                <label for="studentBloodGroup">Blood Group</label>
                                <select id="studentBloodGroup" name="BloodGroup">
                                    <option value="">Select Blood Group</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="studentMobile">Mobile Number</label>
                                <input type="tel" id="studentMobile" name="Mobile">
                            </div>
                            <div class="form-group-modal">
                                <label for="studentAadhaar">Aadhaar Number</label>
                                <input type="text" id="studentAadhaar" name="Aadhaar" maxlength="12">
                            </div>
                            <div class="form-group-modal">
                                <label for="validTill">Valid Till</label>
                                <input type="date" id="validTill" name="ValidTill">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="studentAddress">Address</label>
                                <textarea id="studentAddress" name="Address" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Photo Upload Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-camera"></i>
                            Student Photo
                        </div>
                        <div class="photo-upload">
                            <div class="photo-preview" id="photoPreview">
                                <i class="fas fa-user" style="font-size: 48px; color: #ccc;"></i>
                            </div>
                            <input type="file" id="studentPhoto" name="Photo" accept="image/*" style="display: none;" onchange="previewPhoto(this)">
                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('studentPhoto').click()">
                                <i class="fas fa-upload"></i>
                                Upload Photo
                            </button>
                            <p style="font-size: 12px; color: #6c757d; margin-top: 10px;">
                                Recommended: 120x150 pixels, JPG/PNG format
                            </p>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('addStudentModal')">Cancel</button>
                        <button type="submit" class="btn btn-success" onclick="saveStudent()">
                            <i class="fas fa-save"></i>
                            Create Student & Generate QR
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Sample data matching database schema
        const sampleStudents = [{
                StudentID: 1,
                MemberNo: 2024001,
                Name: "Arjun Sharma",
                PRN: "PRN2024001",
                Branch: "Computer Engineering",
                DOB: "2003-05-15",
                BloodGroup: "B+",
                Mobile: "9876543210",
                Email: "arjun.sharma@student.wiet.edu",
                ValidTill: "2027-06-30",
                Status: "Active",
                BooksIssued: 2,
                MemberName: "Arjun Sharma",
                Group: "Student",
                Phone: "9876543210",
                Designation: "Student"
            },
            {
                StudentID: 2,
                MemberNo: 2024002,
                Name: "Priya Patel",
                PRN: "PRN2024002",
                Branch: "Electronics Engineering",
                DOB: "2003-08-22",
                BloodGroup: "A+",
                Mobile: "9876543211",
                Email: "priya.patel@student.wiet.edu",
                ValidTill: "2027-06-30",
                Status: "Active",
                BooksIssued: 1,
                MemberName: "Priya Patel",
                Group: "Student",
                Phone: "9876543211",
                Designation: "Student"
            },
            {
                StudentID: 3,
                MemberNo: 2024003,
                Name: "Rahul Kumar",
                PRN: "PRN2024003",
                Branch: "Mechanical Engineering",
                DOB: "2003-12-10",
                BloodGroup: "O+",
                Mobile: "9876543212",
                Email: "rahul.kumar@student.wiet.edu",
                ValidTill: "2027-06-30",
                Status: "Active",
                BooksIssued: 0,
                MemberName: "Rahul Kumar",
                Group: "Student",
                Phone: "9876543212",
                Designation: "Student"
            },
            {
                StudentID: 4,
                MemberNo: 2024004,
                Name: "Sneha Singh",
                PRN: "PRN2024004",
                Branch: "Information Technology",
                DOB: "2003-03-18",
                BloodGroup: "AB+",
                Mobile: "9876543213",
                Email: "sneha.singh@student.wiet.edu",
                ValidTill: "2027-06-30",
                Status: "Inactive",
                BooksIssued: 3,
                MemberName: "Sneha Singh",
                Group: "Student",
                Phone: "9876543213",
                Designation: "Student"
            }
        ];

        // Tab functionality
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });

            // Remove active class from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tabName).classList.add('active');

            // Add active class to clicked button
            event.target.classList.add('active');

            // Load content based on tab
            loadTabContent(tabName);
        }

        function loadTabContent(tabName) {
            switch (tabName) {
                case 'students':
                    loadStudentsTable();
                    break;
                case 'membership':
                    loadMembershipContent();
                    break;
                case 'verification':
                    loadVerificationContent();
                    break;
                case 'reports':
                    loadReportsContent();
                    break;
            }
        }

        function loadStudentsTable(searchParams = {}) {
            let filteredStudents = sampleStudents;

            // Apply search filters
            if (searchParams.name) {
                filteredStudents = filteredStudents.filter(student =>
                    student.Name.toLowerCase().includes(searchParams.name.toLowerCase())
                );
            }
            if (searchParams.prn) {
                filteredStudents = filteredStudents.filter(student =>
                    student.PRN.toLowerCase().includes(searchParams.prn.toLowerCase())
                );
            }
            if (searchParams.branch) {
                filteredStudents = filteredStudents.filter(student =>
                    student.Branch === searchParams.branch
                );
            }
            if (searchParams.status) {
                filteredStudents = filteredStudents.filter(student =>
                    student.Status === searchParams.status
                );
            }

            let tableHTML = `
                <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                    <label style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #263c79;">
                        <input type="checkbox" id="selectAllStudents" onchange="selectAllStudents()">
                        Select All Students
                    </label>
                    <div id="bulkActions" style="display: none;">
                        <span style="color: #6c757d; margin-right: 10px;">
                            <span id="selectedCount">0</span> selected
                        </span>
                        <button class="btn btn-warning" onclick="document.getElementById('bulkOperationsModal').style.display='block'">
                            <i class="fas fa-tasks"></i>
                            Bulk Actions
                        </button>
                    </div>
                </div>
                
                <table class="students-table">
                    <thead>
                        <tr>
                            <th>Select</th>
                            <th>Member No.</th>
                            <th>Student Details</th>
                            <th>PRN</th>
                            <th>Branch</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Books Issued</th>
                            <th>Valid Till</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            if (filteredStudents.length === 0) {
                tableHTML += `
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 40px; color: #6c757d;">
                            <i class="fas fa-search" style="font-size: 24px; margin-bottom: 10px;"></i>
                            <p>No students found matching your search criteria.</p>
                        </td>
                    </tr>
                `;
            } else {
                filteredStudents.forEach(student => {
                    const statusClass = {
                        'Active': 'status-active',
                        'Inactive': 'status-inactive',
                        'Suspended': 'status-suspended'
                    } [student.Status] || 'status-active';

                    tableHTML += `
                        <tr>
                            <td>
                                <input type="checkbox" class="student-checkbox" value="${student.StudentID}" onchange="updateBulkActionButtons()">
                            </td>
                            <td><strong>${student.MemberNo}</strong></td>
                            <td>
                                <strong>${student.Name}</strong><br>
                                <small style="color: #6c757d;">${student.Email}</small>
                            </td>
                            <td>${student.PRN}</td>
                            <td><span style="background: rgba(38,60,121,0.1); color: #263c79; padding: 2px 6px; border-radius: 3px; font-size: 12px;">${student.Branch}</span></td>
                            <td>${student.Mobile}</td>
                            <td><span class="status-badge ${statusClass}">${student.Status}</span></td>
                            <td><span style="color: ${student.BooksIssued > 0 ? '#dc3545' : '#28a745'}; font-weight: 600;">${student.BooksIssued}</span></td>
                            <td>${new Date(student.ValidTill).toLocaleDateString('en-IN')}</td>
                            <td class="action-links">
                                <a href="#" class="btn-view" onclick="viewStudent(${student.StudentID})">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="#" class="btn-edit" onclick="editStudent(${student.StudentID})">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="#" class="btn-delete" onclick="deleteStudent(${student.StudentID})">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    `;
                });
            }

            tableHTML += `
                    </tbody>
                </table>
                <div class="pagination">
                    <a href="#" class="page-link">Previous</a>
                    <a href="#" class="page-link active">1</a>
                    <a href="#" class="page-link">2</a>
                    <a href="#" class="page-link">3</a>
                    <a href="#" class="page-link">Next</a>
                </div>
            `;

            document.getElementById('studentsTableContainer').innerHTML = tableHTML;
        }

        function searchStudents() {
            const searchParams = {
                name: document.getElementById('searchName').value.trim(),
                prn: document.getElementById('searchPRN').value.trim(),
                branch: document.getElementById('searchBranch').value,
                status: document.getElementById('searchStatus').value
            };

            loadStudentsTable(searchParams);
        }

        function loadMembershipContent() {
            // Implementation for membership management
            console.log('Loading membership content...');
        }

        function loadVerificationContent() {
            // Implementation for verification and QR codes
            console.log('Loading verification content...');
        }

        function loadReportsContent() {
            // Implementation for reports
            console.log('Loading reports content...');
        }

        // Modal functions
        function openAddStudentModal() {
            document.getElementById('addStudentModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';

            // Reset form
            if (modalId === 'addStudentModal') {
                document.getElementById('addStudentForm').reset();
                document.getElementById('photoPreview').innerHTML = '<i class="fas fa-user" style="font-size: 48px; color: #ccc;"></i>';
            }
        }

        function previewPhoto(input, previewId = 'photoPreview') {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById(previewId).innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function saveStudent() {
            const formData = new FormData(document.getElementById('addStudentForm'));
            const studentData = Object.fromEntries(formData);

            // Generate member number and student ID
            const newMemberNo = Math.max(...sampleStudents.map(s => s.MemberNo)) + 1;
            const newStudentID = Math.max(...sampleStudents.map(s => s.StudentID)) + 1;

            console.log('Creating new student:', {
                ...studentData,
                MemberNo: newMemberNo,
                StudentID: newStudentID
            });

            // Show success message
            alert(`Student created successfully!\nMember No: ${newMemberNo}\nQR Code generated and ready for printing.`);
            closeModal('addStudentModal');

            // Refresh the students table
            loadStudentsTable();
        }

        function saveStudentInline() {
            const formData = new FormData(document.getElementById('addStudentInlineForm'));
            const studentData = Object.fromEntries(formData);

            // Generate member number and student ID
            const newMemberNo = Math.max(...sampleStudents.map(s => s.MemberNo)) + 1;
            const newStudentID = Math.max(...sampleStudents.map(s => s.StudentID)) + 1;

            console.log('Creating new student:', {
                ...studentData,
                MemberNo: newMemberNo,
                StudentID: newStudentID
            });

            // Show success message
            alert(`Student created successfully!\nMember No: ${newMemberNo}\nQR Code generated and ready for printing.`);
            
            // Clear the form
            document.getElementById('addStudentInlineForm').reset();
            
            // Clear photo preview
            const photoPreview = document.getElementById('photoPreviewInline');
            photoPreview.innerHTML = '<i class="fas fa-user" style="font-size: 48px; color: #ccc;"></i>';

            // Refresh the students table
            loadStudentsTable();
        }

        // Student actions
        function viewStudent(studentId) {
            console.log('Viewing student:', studentId);
            alert(`Opening detailed view for Student ID: ${studentId}`);
        }

        function editStudent(studentId) {
            console.log('Editing student:', studentId);
            alert(`Opening edit form for Student ID: ${studentId}`);
        }

        function deleteStudent(studentId) {
            if (confirm(`Are you sure you want to delete Student ID: ${studentId}?`)) {
                console.log('Deleting student:', studentId);
                alert('Student deleted successfully!');
                loadStudentsTable();
            }
        }

        function generateReports() {
            console.log('Generating reports...');
            alert('Opening report generation interface...');
        }

        function bulkOperations() {
            document.getElementById('bulkOperationsModal').style.display = 'block';
        }

        function closeBulkModal() {
            document.getElementById('bulkOperationsModal').style.display = 'none';
            // Reset selections
            document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = false);
            updateBulkActionButtons();
        }

        function selectAllStudents() {
            const selectAll = document.getElementById('selectAllStudents');
            const checkboxes = document.querySelectorAll('.student-checkbox');

            checkboxes.forEach(cb => {
                cb.checked = selectAll.checked;
            });

            updateBulkActionButtons();
        }

        function updateBulkActionButtons() {
            const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
            const bulkActions = document.getElementById('bulkActions');
            const selectedCount = document.getElementById('selectedCount');

            if (checkedBoxes.length > 0) {
                bulkActions.style.display = 'block';
                selectedCount.textContent = checkedBoxes.length;
            } else {
                bulkActions.style.display = 'none';
            }
        }

        function performBulkAction(action) {
            const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
            const selectedIds = Array.from(checkedBoxes).map(cb => cb.value);

            if (selectedIds.length === 0) {
                alert('Please select at least one student.');
                return;
            }

            let confirmMessage = '';
            switch (action) {
                case 'activate':
                    confirmMessage = `Activate ${selectedIds.length} selected student(s)?`;
                    break;
                case 'deactivate':
                    confirmMessage = `Deactivate ${selectedIds.length} selected student(s)?`;
                    break;
                case 'suspend':
                    confirmMessage = `Suspend ${selectedIds.length} selected student(s)?`;
                    break;
                case 'extend':
                    confirmMessage = `Extend membership validity for ${selectedIds.length} selected student(s)?`;
                    break;
                case 'regenerate-qr':
                    confirmMessage = `Regenerate QR codes for ${selectedIds.length} selected student(s)?`;
                    break;
                case 'export':
                    confirmMessage = `Export data for ${selectedIds.length} selected student(s)?`;
                    break;
                case 'send-notification':
                    confirmMessage = `Send notification to ${selectedIds.length} selected student(s)?`;
                    break;
                case 'delete':
                    confirmMessage = `Are you sure you want to DELETE ${selectedIds.length} selected student(s)? This action cannot be undone.`;
                    break;
                default:
                    confirmMessage = `Perform action on ${selectedIds.length} selected student(s)?`;
            }

            if (confirm(confirmMessage)) {
                console.log(`Performing ${action} on students:`, selectedIds);

                // Simulate processing
                let successMessage = '';
                switch (action) {
                    case 'activate':
                        successMessage = `${selectedIds.length} student(s) activated successfully.`;
                        break;
                    case 'deactivate':
                        successMessage = `${selectedIds.length} student(s) deactivated successfully.`;
                        break;
                    case 'suspend':
                        successMessage = `${selectedIds.length} student(s) suspended successfully.`;
                        break;
                    case 'extend':
                        successMessage = `Membership extended for ${selectedIds.length} student(s).`;
                        break;
                    case 'regenerate-qr':
                        successMessage = `QR codes regenerated for ${selectedIds.length} student(s).`;
                        break;
                    case 'export':
                        successMessage = `Data exported for ${selectedIds.length} student(s). Download will start shortly.`;
                        break;
                    case 'send-notification':
                        successMessage = `Notifications sent to ${selectedIds.length} student(s).`;
                        break;
                    case 'delete':
                        successMessage = `${selectedIds.length} student(s) deleted successfully.`;
                        break;
                }

                alert(successMessage);
                closeBulkModal();
                loadStudentsTable(); // Refresh the table
            }
        }

        // Load statistics with calculated data
        function loadStatistics() {
            const totalStudents = sampleStudents.length;
            const activeMembers = sampleStudents.filter(s => s.Status === 'Active').length;
            const totalBooksIssued = sampleStudents.reduce((sum, s) => sum + s.BooksIssued, 0);
            const overdueBooks = Math.floor(totalBooksIssued * 0.1); // Simulate 10% overdue

            document.getElementById('totalStudents').textContent = totalStudents.toLocaleString();
            document.getElementById('activeMembers').textContent = activeMembers.toLocaleString();
            document.getElementById('booksIssued').textContent = totalBooksIssued.toLocaleString();
            document.getElementById('overdueBooks').textContent = overdueBooks.toLocaleString();
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        };

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadStatistics();
            loadStudentsTable();
        });
    </script>

    <!-- Bulk Operations Modal -->
    <div id="bulkOperationsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-tasks"></i>
                    Bulk Operations
                </h3>
                <button class="close" onclick="closeBulkModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div style="margin-bottom: 20px;">
                    <p style="color: #6c757d; margin-bottom: 15px;">
                        Select an action to perform on multiple students at once. You can select students from the table and then choose one of the operations below.
                    </p>
                </div>

                <!-- Status Operations -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-user-check"></i>
                        Status Management
                    </div>
                    <div class="form-row">
                        <button class="btn btn-success" onclick="performBulkAction('activate')" style="flex: 1;">
                            <i class="fas fa-user-check"></i>
                            Activate Members
                        </button>
                        <button class="btn btn-secondary" onclick="performBulkAction('deactivate')" style="flex: 1;">
                            <i class="fas fa-user-times"></i>
                            Deactivate Members
                        </button>
                        <button class="btn btn-warning" onclick="performBulkAction('suspend')" style="flex: 1;">
                            <i class="fas fa-user-slash"></i>
                            Suspend Members
                        </button>
                    </div>
                </div>

                <!-- Membership Operations -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-calendar-alt"></i>
                        Membership Operations
                    </div>
                    <div class="form-row">
                        <button class="btn btn-info" onclick="performBulkAction('extend')" style="flex: 1;">
                            <i class="fas fa-calendar-plus"></i>
                            Extend Validity
                        </button>
                        <button class="btn btn-primary" onclick="performBulkAction('regenerate-qr')" style="flex: 1;">
                            <i class="fas fa-qrcode"></i>
                            Regenerate QR Codes
                        </button>
                    </div>
                </div>

                <!-- Communication Operations -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-envelope"></i>
                        Communication
                    </div>
                    <div class="form-row">
                        <button class="btn btn-primary" onclick="performBulkAction('send-notification')" style="flex: 1;">
                            <i class="fas fa-bell"></i>
                            Send Notification
                        </button>
                        <button class="btn btn-info" onclick="performBulkAction('send-reminder')" style="flex: 1;">
                            <i class="fas fa-clock"></i>
                            Send Due Reminders
                        </button>
                    </div>
                </div>

                <!-- Data Operations -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-database"></i>
                        Data Operations
                    </div>
                    <div class="form-row">
                        <button class="btn btn-success" onclick="performBulkAction('export')" style="flex: 1;">
                            <i class="fas fa-download"></i>
                            Export Selected Data
                        </button>
                        <button class="btn btn-warning" onclick="performBulkAction('backup')" style="flex: 1;">
                            <i class="fas fa-archive"></i>
                            Backup Records
                        </button>
                    </div>
                </div>

                <!-- Dangerous Operations -->
                <div class="form-section" style="border-color: #dc3545; background-color: #fff5f5;">
                    <div class="section-title" style="color: #dc3545; border-color: #dc3545;">
                        <i class="fas fa-exclamation-triangle"></i>
                        Dangerous Operations
                    </div>
                    <div class="form-row">
                        <button class="btn" onclick="performBulkAction('delete')"
                            style="flex: 1; background-color: #dc3545; color: white;">
                            <i class="fas fa-trash-alt"></i>
                            Delete Selected Students
                        </button>
                    </div>
                    <p style="color: #dc3545; font-size: 12px; margin-top: 10px; margin-bottom: 0;">
                        <i class="fas fa-warning"></i>
                        Warning: Deletion is permanent and cannot be undone. Use with extreme caution.
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeBulkModal()">
                    <i class="fas fa-times"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
</body>

</html>