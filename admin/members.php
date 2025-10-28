<?php
// Include AJAX handler FIRST (before session_start)
require_once 'ajax-handler.php';

session_start();

// Include database connection and functions
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

$admin_id = $_SESSION['admin_id'] ?? 1;
$admin_name = $_SESSION['admin_name'] ?? 'Admin User';

// Fetch real members from database
$stmt = $pdo->query("
    SELECT m.*, 
           CASE WHEN s.StudentID IS NOT NULL THEN 'Student' 
                WHEN f.FacultyID IS NOT NULL THEN 'Faculty' 
                ELSE m.`Group` END as MemberType
    FROM Member m
    LEFT JOIN Student s ON m.MemberNo = s.MemberNo
    LEFT JOIN Faculty f ON m.MemberNo = f.MemberNo
    ORDER BY m.MemberName
");
$members = $stmt->fetchAll();

// Sample data for fallback (if database is empty)
$sampleMembers = [
    [
        'MemberNo' => 2024001,
        'MemberName' => 'Rahul Sharma',
        'Group' => 'Student',
        'Designation' => 'B.Tech Final Year',
        'Entitlement' => 'Standard',
        'Phone' => '9876543210',
        'Email' => 'rahul.sharma@student.wiet.edu',
        'FinePerDay' => 2.00,
        'AdmissionDate' => '2021-08-15',
        'Override' => false,
        'BooksIssued' => 3,
        'ClosingDate' => null,
        'Status' => 'Active',
        'CreatedBy' => 1
    ],
    [
        'MemberNo' => 2024002,
        'MemberName' => 'Dr. Priya Patel',
        'Group' => 'Faculty',
        'Designation' => 'Assistant Professor',
        'Entitlement' => 'Faculty',
        'Phone' => '9876543211',
        'Email' => 'priya.patel@wiet.edu',
        'FinePerDay' => 1.00,
        'AdmissionDate' => '2020-06-01',
        'Override' => true,
        'BooksIssued' => 5,
        'ClosingDate' => null,
        'Status' => 'Active',
        'CreatedBy' => 1
    ],
    [
        'MemberNo' => 2024003,
        'MemberName' => 'Amit Kumar Singh',
        'Group' => 'Staff',
        'Designation' => 'Lab Assistant',
        'Entitlement' => 'Staff',
        'Phone' => '9876543212',
        'Email' => 'amit.singh@wiet.edu',
        'FinePerDay' => 2.00,
        'AdmissionDate' => '2022-01-10',
        'Override' => false,
        'BooksIssued' => 1,
        'ClosingDate' => null,
        'Status' => 'Active',
        'CreatedBy' => 1
    ],
    [
        'MemberNo' => 2024004,
        'MemberName' => 'Sneha Gupta',
        'Group' => 'Student',
        'Designation' => 'M.Tech Second Year',
        'Entitlement' => 'Standard',
        'Phone' => '9876543213',
        'Email' => 'sneha.gupta@student.wiet.edu',
        'FinePerDay' => 2.00,
        'AdmissionDate' => '2023-08-20',
        'Override' => false,
        'BooksIssued' => 0,
        'ClosingDate' => '2024-05-15',
        'Status' => 'Inactive',
        'CreatedBy' => 1
    ]
];

// Member entitlements configuration
$memberEntitlements = [
    'Standard' => ['max_books' => 3, 'issue_period' => 15, 'fine_per_day' => 2.00],
    'Faculty' => ['max_books' => 10, 'issue_period' => 30, 'fine_per_day' => 1.00],
    'Staff' => ['max_books' => 5, 'issue_period' => 20, 'fine_per_day' => 2.00],
    'Guest' => ['max_books' => 2, 'issue_period' => 7, 'fine_per_day' => 5.00]
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .members-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #cfac69;
        }

        .members-title {
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
            .members-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .members-title {
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

        .stat-card.faculty {
            border-left-color: #28a745;
        }

        .stat-card.staff {
            border-left-color: #17a2b8;
        }

        .stat-card.inactive {
            border-left-color: #dc3545;
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

        .members-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .members-table th {
            background-color: #263c79;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .members-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
        }

        .members-table tr:hover {
            background-color: rgba(207, 172, 105, 0.1);
        }

        .action-links {
            display: flex;
            gap: 8px;
        }

        .action-links a,
        .action-links button {
            padding: 4px 8px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            border: none;
            cursor: pointer;
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

        .group-badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .group-student {
            background-color: rgba(38, 60, 121, 0.1);
            color: #263c79;
        }

        .group-faculty {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .group-staff {
            background-color: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
        }

        .group-guest {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
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
            position: relative;
            z-index: 1001;
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
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 8px;
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

        .modal-footer {
            padding: 15px 25px;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .entitlement-info {
            background: #e7f3ff;
            border: 1px solid #b8daff;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
        }

        .entitlement-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .entitlement-item {
            font-size: 14px;
        }

        .entitlement-label {
            font-weight: 600;
            color: #495057;
        }

        .entitlement-value {
            color: #263c79;
            font-weight: 500;
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

        @media (max-width: 768px) {
            .search-row {
                flex-direction: column;
            }

            .form-group {
                min-width: 100%;
            }

            .members-table {
                font-size: 12px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .tab-buttons {
                overflow-x: auto;
                white-space: nowrap;
            }

            .form-row {
                flex-direction: column;
            }

            .form-group-modal {
                min-width: 100%;
            }

            .modal-content {
                margin-top: 150px;
                width: 98%;
                max-height: 80vh;
            }
        }

        @media (max-width: 480px) {
            .modal-content {
                margin-top: 135px;
                width: 99%;
                max-height: 75vh;
            }
        }
    </style>
</head>

<body>
    <div class="members-header">
        <h1 class="members-title">
            <i class="fas fa-users"></i>
            Members Management
        </h1>
        <div class="action-buttons">
        <!-- Inline Add Member Form will be placed below stats -->
            <button class="btn btn-info" onclick="generateMemberCards()">
                <i class="fas fa-id-card"></i>
                Generate Cards
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
            <div class="stat-number" id="totalMembers">-</div>
            <div class="stat-label">Total Members</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="activeMembers">-</div>
            <div class="stat-label">Active Members</div>
        </div>
        <div class="stat-card faculty">
            <div class="stat-number" id="facultyMembers">-</div>
            <div class="stat-label">Faculty Members</div>
        </div>
        <div class="stat-card staff">
            <div class="stat-number" id="staffMembers">-</div>
            <div class="stat-label">Staff Members</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="studentMembers">-</div>
            <div class="stat-label">Student Members</div>
        </div>
        <div class="stat-card inactive">
            <div class="stat-number" id="inactiveMembers">-</div>
            <div class="stat-label">Inactive Members</div>
        </div>
    </div>

    <!-- Inline Add Member Form -->
    <div class="form-section" style="margin-bottom:30px;">
        <form id="addMemberInlineForm" onsubmit="saveMemberInline(); return false;">
            <div class="section-title">Add New Member</div>
            <div class="form-row">
                <div class="form-group-modal">
                    <label for="memberNameInline">Full Name <span class="required">*</span></label>
                    <input type="text" id="memberNameInline" name="MemberName" required>
                </div>
                <div class="form-group-modal">
                    <label for="memberGroupInline">Group <span class="required">*</span></label>
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
                    <label for="memberDesignationInline">Designation</label>
                    <input type="text" id="memberDesignationInline" name="Designation" placeholder="e.g., Assistant Professor, B.Tech Final Year">
                </div>
                <div class="form-group-modal">
                    <label for="memberEntitlementInline">Entitlement</label>
                    <select id="memberEntitlementInline" name="Entitlement">
                        <option value="Standard">Standard</option>
                        <option value="Faculty">Faculty</option>
                        <option value="Staff">Staff</option>
                        <option value="Guest">Guest</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group-modal">
                    <label for="memberPhoneInline">Phone Number</label>
                    <input type="tel" id="memberPhoneInline" name="Phone" placeholder="10-digit mobile number">
                </div>
                <div class="form-group-modal">
                    <label for="memberEmailInline">Email Address</label>
                    <input type="email" id="memberEmailInline" name="Email" placeholder="member@example.com">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group-modal">
                    <label for="finePerDayInline">Fine Per Day (₹)</label>
                    <input type="number" id="finePerDayInline" name="FinePerDay" min="0" step="0.50" value="2.00">
                </div>
                <div class="form-group-modal">
                    <label for="admissionDateInline">Admission Date</label>
                    <input type="date" id="admissionDateInline" name="AdmissionDate" value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group-modal">
                    <label for="overrideInline">Override</label>
                    <select id="overrideInline" name="Override">
                        <option value="false">No</option>
                        <option value="true">Yes</option>
                    </select>
                </div>
                <div class="form-group-modal">
                    <label for="closingDateInline">Closing Date</label>
                    <input type="date" id="closingDateInline" name="ClosingDate">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group-modal">
                    <label for="statusInline">Status <span class="required">*</span></label>
                    <select id="statusInline" name="Status" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="form-actions" style="justify-content:center;">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-plus"></i>
                    Add Member
                </button>
            </div>
        </form>
    </div>

    <!-- Tabs -->
    <div class="tabs-container">
        <div class="tab-buttons">
            <button class="tab-btn active" onclick="showTab('all-members')">
                <i class="fas fa-users"></i>
                All Members
            </button>
            <button class="tab-btn" onclick="showTab('entitlements')">
                <i class="fas fa-certificate"></i>
                Entitlements
            </button>
            <button class="tab-btn" onclick="showTab('member-cards')">
                <i class="fas fa-id-card"></i>
                Member Cards
            </button>
            <button class="tab-btn" onclick="showTab('reports')">
                <i class="fas fa-chart-pie"></i>
                Reports
            </button>
        </div>

        <!-- All Members Tab -->
        <div id="all-members" class="tab-content active">
            <div class="search-filters">
                <div class="search-row">
                    <div class="form-group">
                        <label for="searchName">Member Name</label>
                        <input type="text" id="searchName" class="form-control" placeholder="Search by name...">
                    </div>
                    <div class="form-group">
                        <label for="searchMemberNo">Member Number</label>
                        <input type="text" id="searchMemberNo" class="form-control" placeholder="Search by member number...">
                    </div>
                    <div class="form-group">
                        <label for="searchGroup">Group</label>
                        <select id="searchGroup" class="form-control">
                            <option value="">All Groups</option>
                            <option value="Student">Student</option>
                            <option value="Faculty">Faculty</option>
                            <option value="Staff">Staff</option>
                            <option value="Guest">Guest</option>
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
                        <button type="button" class="btn btn-primary" onclick="searchMembers()">
                            <i class="fas fa-search"></i>
                            Search
                        </button>
                    </div>
                </div>
            </div>

            <div id="membersTableContainer">
                <!-- Members table will be loaded here -->
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i>
                    <p style="margin-top: 10px;">Loading members...</p>
                </div>
            </div>
        </div>

        <!-- Entitlements Tab -->
        <div id="entitlements" class="tab-content">
            <div id="entitlementsContent">
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-certificate" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <h3>Member Entitlements</h3>
                    <p>Configure member privileges and borrowing limits by group.</p>
                </div>
            </div>
        </div>

        <!-- Member Cards Tab -->
        <div id="member-cards" class="tab-content">
            <div id="memberCardsContent">
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-id-card" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <h3>Member ID Cards</h3>
                    <p>Generate and print member ID cards with QR codes.</p>
                </div>
            </div>
        </div>

        <!-- Reports Tab -->
        <div id="reports" class="tab-content">
            <div id="reportsContent">
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-chart-pie" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <h3>Member Reports</h3>
                    <p>Generate comprehensive reports on member data and activity.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Member Modal -->
    <div id="addMemberModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add New Member</h3>
                <button class="close" onclick="closeModal('addMemberModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addMemberForm">
                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-user"></i>
                            Basic Information
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="memberName">Full Name <span class="required">*</span></label>
                                <input type="text" id="memberName" name="MemberName" required>
                            </div>
                            <div class="form-group-modal">
                                <label for="memberGroup">Group <span class="required">*</span></label>
                                <select id="memberGroup" name="Group" required onchange="updateEntitlementInfo()">
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
                                <label for="memberDesignation">Designation</label>
                                <input type="text" id="memberDesignation" name="Designation" placeholder="e.g., Assistant Professor, B.Tech Final Year">
                            </div>
                            <div class="form-group-modal">
                                <label for="memberEntitlement">Entitlement</label>
                                <select id="memberEntitlement" name="Entitlement">
                                    <option value="Standard">Standard</option>
                                    <option value="Faculty">Faculty</option>
                                    <option value="Staff">Staff</option>
                                    <option value="Guest">Guest</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-address-book"></i>
                            Contact Information
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="memberPhone">Phone Number</label>
                                <input type="tel" id="memberPhone" name="Phone" placeholder="10-digit mobile number">
                            </div>
                            <div class="form-group-modal">
                                <label for="memberEmail">Email Address</label>
                                <input type="email" id="memberEmail" name="Email" placeholder="member@example.com">
                            </div>
                        </div>
                    </div>

                    <!-- Library Settings Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-cog"></i>
                            Library Settings
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="finePerDay">Fine Per Day (₹)</label>
                                <input type="number" id="finePerDay" name="FinePerDay" min="0" step="0.50" value="2.00">
                            </div>
                            <div class="form-group-modal">
                                <label for="admissionDate">Admission Date</label>
                                <input type="date" id="admissionDate" name="AdmissionDate" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label>
                                    <input type="checkbox" id="memberOverride" name="Override" value="1">
                                    Allow Override (Admin can bypass borrowing limits)
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Entitlement Information Display -->
                    <div id="entitlementInfo" class="entitlement-info" style="display: none;">
                        <h4 style="color: #263c79; margin-bottom: 10px;">
                            <i class="fas fa-info-circle"></i>
                            Entitlement Details
                        </h4>
                        <div class="entitlement-details">
                            <div class="entitlement-item">
                                <span class="entitlement-label">Max Books:</span>
                                <span class="entitlement-value" id="maxBooks">-</span>
                            </div>
                            <div class="entitlement-item">
                                <span class="entitlement-label">Issue Period:</span>
                                <span class="entitlement-value" id="issuePeriod">-</span>
                            </div>
                            <div class="entitlement-item">
                                <span class="entitlement-label">Default Fine:</span>
                                <span class="entitlement-value" id="defaultFine">-</span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addMemberModal')">Cancel</button>
                <button type="button" class="btn btn-success" onclick="saveMember()">
                    <i class="fas fa-save"></i>
                    Create Member
                </button>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        const memberEntitlements = <?php echo json_encode($memberEntitlements); ?>;
        let selectedMembers = [];

        // Inline Add Member handler
        async function saveMemberInline() {
            const form = document.getElementById('addMemberInlineForm');
            const formData = new FormData(form);
            
            // Convert to JSON for API
            const memberData = {
                action: 'add',
                MemberName: formData.get('MemberName'),
                Group: formData.get('Group'),
                Designation: formData.get('Designation'),
                Entitlement: formData.get('Entitlement'),
                Phone: formData.get('Phone'),
                Email: formData.get('Email'),
                FinePerDay: formData.get('FinePerDay'),
                AdmissionDate: formData.get('AdmissionDate'),
                Status: formData.get('Status') || 'Active'
            };

            try {
                const response = await fetch('api/members.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(memberData)
                });

                const result = await response.json();

                if (result.success) {
                    alert(`Member created successfully!\nMember No: ${result.memberNo}\nMember card will be generated automatically.`);
                    form.reset();
                    loadMembersTable();
                    loadStatistics();
                } else {
                    alert('Error creating member: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error saving member:', error);
                alert('Error saving member. Please try again.');
            }
        }

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

            loadTabContent(tabName);
        }

        function loadTabContent(tabName) {
            switch (tabName) {
                case 'all-members':
                    loadMembersTable();
                    break;
                case 'entitlements':
                    loadEntitlementsContent();
                    break;
                case 'member-cards':
                    loadMemberCardsContent();
                    break;
                case 'reports':
                    loadReportsContent();
                    break;
            }
        }

        async function loadMembersTable(searchParams = {}) {
            // Show loading indicator
            document.getElementById('membersTableContainer').innerHTML = `
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i>
                    <p style="margin-top: 10px;">Loading members...</p>
                </div>
            `;
            
            try {
                // Build query string
                const params = new URLSearchParams();
                if (searchParams.search) params.append('search', searchParams.search);
                if (searchParams.status) params.append('status', searchParams.status);
                if (searchParams.group) params.append('group', searchParams.group);
                
                // Fetch from API
                const response = await fetch(`api/members.php?action=list&${params.toString()}`);
                const result = await response.json();
                
                if (!result.success) {
                    throw new Error(result.message || 'Failed to load members');
                }
                
                let filteredMembers = result.data;
                
                // Apply additional client-side filters
                if (searchParams.name) {
                    filteredMembers = filteredMembers.filter(member =>
                        member.MemberName.toLowerCase().includes(searchParams.name.toLowerCase())
                    );
                }
                if (searchParams.memberNo) {
                    filteredMembers = filteredMembers.filter(member =>
                        member.MemberNo.toString().includes(searchParams.memberNo)
                    );
                }

            let tableHTML = `
                <table class="members-table">
                    <thead>
                        <tr>
                            <th>Member No.</th>
                            <th>Name</th>
                            <th>Group</th>
                            <th>Designation</th>
                            <th>Contact</th>
                            <th>Books Issued</th>
                            <th>Status</th>
                            <th>Admission Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            if (filteredMembers.length === 0) {
                tableHTML += `
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px; color: #6c757d;">
                            <i class="fas fa-search" style="font-size: 24px; margin-bottom: 10px;"></i>
                            <p>No members found matching your search criteria.</p>
                        </td>
                    </tr>
                `;
            } else {
                filteredMembers.forEach(member => {
                    const statusClass = {
                        'Active': 'status-active',
                        'Inactive': 'status-inactive',
                        'Suspended': 'status-suspended'
                    } [member.Status] || 'status-active';

                    const groupClass = {
                        'Student': 'group-student',
                        'Faculty': 'group-faculty',
                        'Staff': 'group-staff',
                        'Guest': 'group-guest'
                    } [member.Group] || 'group-student';

                    tableHTML += `
                        <tr>
                            <td><strong>${member.MemberNo}</strong></td>
                            <td>
                                <strong>${member.MemberName}</strong>
                                ${member.Override ? '<br><small style="color: #28a745;"><i class="fas fa-star"></i> Override Enabled</small>' : ''}
                            </td>
                            <td><span class="group-badge ${groupClass}">${member.Group}</span></td>
                            <td>${member.Designation || '-'}</td>
                            <td>
                                ${member.Phone ? `<div><i class="fas fa-phone"></i> ${member.Phone}</div>` : ''}
                                ${member.Email ? `<div><i class="fas fa-envelope"></i> ${member.Email}</div>` : ''}
                            </td>
                            <td>
                                <span style="color: ${member.BooksIssued > 0 ? '#dc3545' : '#28a745'}; font-weight: 600;">
                                    ${member.BooksIssued}
                                </span>
                            </td>
                            <td><span class="status-badge ${statusClass}">${member.Status}</span></td>
                            <td>${new Date(member.AdmissionDate).toLocaleDateString('en-IN')}</td>
                            <td class="action-links">
                                <a href="#" class="btn-view" onclick="viewMember(${member.MemberNo})">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="#" class="btn-edit" onclick="editMember(${member.MemberNo})">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn-delete" onclick="deleteMember(${member.MemberNo})">
                                    <i class="fas fa-trash"></i>
                                </button>
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

            document.getElementById('membersTableContainer').innerHTML = tableHTML;
            
            } catch (error) {
                console.error('Error loading members:', error);
                document.getElementById('membersTableContainer').innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #dc3545;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 24px; margin-bottom: 10px;"></i>
                        <p>Error loading members: ${error.message}</p>
                        <button onclick="loadMembersTable()" class="btn btn-primary" style="margin-top: 10px;">
                            <i class="fas fa-redo"></i> Retry
                        </button>
                    </div>
                `;
            }
        }

        function searchMembers() {
            const searchParams = {
                name: document.getElementById('searchName').value.trim(),
                memberNo: document.getElementById('searchMemberNo').value.trim(),
                group: document.getElementById('searchGroup').value,
                status: document.getElementById('searchStatus').value
            };

            loadMembersTable(searchParams);
        }

        function loadEntitlementsContent() {
            let entitlementsHTML = `
                <div style="margin-bottom: 20px;">
                    <h3 style="color: #263c79; margin-bottom: 15px;">Member Group Entitlements</h3>
                    <p style="color: #6c757d; margin-bottom: 20px;">Configure borrowing privileges and limits for different member groups.</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            `;

            Object.entries(memberEntitlements).forEach(([entitlement, details]) => {
                entitlementsHTML += `
                    <div style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <h4 style="color: #263c79; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-certificate"></i>
                            ${entitlement} Members
                        </h4>
                        <div style="margin-bottom: 15px;">
                            <div style="margin-bottom: 8px;">
                                <span style="font-weight: 600; color: #495057;">Maximum Books:</span>
                                <span style="color: #263c79; font-weight: 500;">${details.max_books}</span>
                            </div>
                            <div style="margin-bottom: 8px;">
                                <span style="font-weight: 600; color: #495057;">Issue Period:</span>
                                <span style="color: #263c79; font-weight: 500;">${details.issue_period} days</span>
                            </div>
                            <div style="margin-bottom: 8px;">
                                <span style="font-weight: 600; color: #495057;">Fine Per Day:</span>
                                <span style="color: #263c79; font-weight: 500;">₹${details.fine_per_day}</span>
                            </div>
                        </div>
                        <button class="btn btn-primary" onclick="editEntitlement('${entitlement}')" style="width: 100%;">
                            <i class="fas fa-edit"></i>
                            Edit Entitlement
                        </button>
                    </div>
                `;
            });

            entitlementsHTML += `</div>`;
            document.getElementById('entitlementsContent').innerHTML = entitlementsHTML;
        }

        function loadMemberCardsContent() {
            const memberCardsHTML = `
                <div style="margin-bottom: 20px;">
                    <h3 style="color: #263c79; margin-bottom: 15px;">Member ID Cards</h3>
                    <p style="color: #6c757d; margin-bottom: 20px;">Generate and print member ID cards with QR codes and barcodes.</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div onclick="generateAllCards()" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-id-card" style="font-size: 48px; color: #263c79; margin-bottom: 15px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 10px;">Generate All Cards</h4>
                        <p style="color: #6c757d; font-size: 14px;">Generate ID cards for all active members</p>
                    </div>
                    
                    <div onclick="generateSelectedCards()" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-check-square" style="font-size: 48px; color: #28a745; margin-bottom: 15px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 10px;">Generate Selected</h4>
                        <p style="color: #6c757d; font-size: 14px;">Generate cards for selected members only</p>
                    </div>
                    
                    <div onclick="printExistingCards()" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-print" style="font-size: 48px; color: #17a2b8; margin-bottom: 15px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 10px;">Print Existing</h4>
                        <p style="color: #6c757d; font-size: 14px;">Print previously generated ID cards</p>
                    </div>
                    
                    <div onclick="cardTemplateSettings()" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-cog" style="font-size: 48px; color: #ffc107; margin-bottom: 15px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 10px;">Card Settings</h4>
                        <p style="color: #6c757d; font-size: 14px;">Configure card template and design</p>
                    </div>
                </div>
            `;

            document.getElementById('memberCardsContent').innerHTML = memberCardsHTML;
        }

        function loadReportsContent() {
            const reportsHTML = `
                <div style="margin-bottom: 20px;">
                    <h3 style="color: #263c79; margin-bottom: 15px;">Member Reports</h3>
                    <p style="color: #6c757d; margin-bottom: 20px;">Generate comprehensive reports on member activities and statistics.</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div onclick="generateReport('member-summary')" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-users" style="font-size: 24px; color: #263c79; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">Member Summary</h4>
                        <p style="color: #6c757d; font-size: 14px;">Complete member statistics and overview</p>
                    </div>
                    
                    <div onclick="generateReport('active-members')" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-user-check" style="font-size: 24px; color: #28a745; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">Active Members</h4>
                        <p style="color: #6c757d; font-size: 14px;">List of all active library members</p>
                    </div>
                    
                    <div onclick="generateReport('member-activity')" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-chart-line" style="font-size: 24px; color: #17a2b8; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">Member Activity</h4>
                        <p style="color: #6c757d; font-size: 14px;">Member borrowing and return patterns</p>
                    </div>
                    
                    <div onclick="generateReport('group-wise')" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-layer-group" style="font-size: 24px; color: #ffc107; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">Group-wise Report</h4>
                        <p style="color: #6c757d; font-size: 14px;">Members categorized by groups</p>
                    </div>
                </div>
            `;

            document.getElementById('reportsContent').innerHTML = reportsHTML;
        }

        // Modal functions
        function openAddMemberModal() {
            document.getElementById('addMemberModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';

            if (modalId === 'addMemberModal') {
                document.getElementById('addMemberForm').reset();
                document.getElementById('entitlementInfo').style.display = 'none';
            }
        }

        function updateEntitlementInfo() {
            const group = document.getElementById('memberGroup').value;
            const entitlementSelect = document.getElementById('memberEntitlement');
            const entitlementInfo = document.getElementById('entitlementInfo');
            const finePerDayInput = document.getElementById('finePerDay');

            if (group) {
                // Set default entitlement based on group
                entitlementSelect.value = group === 'Student' ? 'Standard' : group;

                // Update fine per day based on group
                const entitlement = entitlementSelect.value;
                if (memberEntitlements[entitlement]) {
                    finePerDayInput.value = memberEntitlements[entitlement].fine_per_day;

                    // Show entitlement info
                    document.getElementById('maxBooks').textContent = memberEntitlements[entitlement].max_books;
                    document.getElementById('issuePeriod').textContent = memberEntitlements[entitlement].issue_period + ' days';
                    document.getElementById('defaultFine').textContent = '₹' + memberEntitlements[entitlement].fine_per_day;

                    entitlementInfo.style.display = 'block';
                }
            } else {
                entitlementInfo.style.display = 'none';
            }
        }

        async function saveMember() {
            const form = document.getElementById('addMemberForm');
            const formData = new FormData(form);
            
            // Convert to JSON for API
            const memberData = {
                action: 'add',
                MemberName: formData.get('MemberName'),
                Group: formData.get('Group'),
                Designation: formData.get('Designation'),
                Entitlement: formData.get('Entitlement'),
                Phone: formData.get('Phone'),
                Email: formData.get('Email'),
                FinePerDay: formData.get('FinePerDay'),
                AdmissionDate: formData.get('AdmissionDate'),
                Status: formData.get('Status') || 'Active'
            };

            try {
                const response = await fetch('api/members.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(memberData)
                });

                const result = await response.json();

                if (result.success) {
                    alert(`Member created successfully!\nMember No: ${result.memberNo}\nMember card will be generated automatically.`);
                    closeModal('addMemberModal');
                    loadMembersTable();
                    loadStatistics();
                } else {
                    alert('Error creating member: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error saving member:', error);
                alert('Error saving member. Please try again.');
            }
        }

        // Member actions
        async function viewMember(memberNo) {
            try {
                const response = await fetch(`api/members.php?action=get&memberNo=${memberNo}`);
                const result = await response.json();

                if (result.success) {
                    const member = result.data;
                    const circulations = member.activeCirculations || [];
                    
                    let circulationInfo = '';
                    if (circulations.length > 0) {
                        circulationInfo = '<h3>Active Book Issues:</h3><ul>';
                        circulations.forEach(circ => {
                            circulationInfo += `<li>${circ.Title} - Due: ${new Date(circ.DueDate).toLocaleDateString()}</li>`;
                        });
                        circulationInfo += '</ul>';
                    }
                    
                    alert(`Member Details:\n\nMember No: ${member.MemberNo}\nName: ${member.MemberName}\nGroup: ${member.Group}\nDesignation: ${member.Designation || 'N/A'}\nPhone: ${member.Phone || 'N/A'}\nEmail: ${member.Email || 'N/A'}\nBooks Issued: ${member.BooksIssued}\nStatus: ${member.Status}\nAdmission Date: ${new Date(member.AdmissionDate).toLocaleDateString()}`);
                    // TODO: Create a proper view modal with all details
                } else {
                    alert('Error loading member details: ' + result.message);
                }
            } catch (error) {
                console.error('Error viewing member:', error);
                alert('Error loading member details. Please try again.');
            }
        }

        async function editMember(memberNo) {
            try {
                const response = await fetch(`api/members.php?action=get&memberNo=${memberNo}`);
                const result = await response.json();

                if (result.success) {
                    const member = result.data;
                    
                    // Populate inline form
                    document.getElementById('addMemberInlineForm').querySelector('[name="MemberName"]').value = member.MemberName || '';
                    document.getElementById('addMemberInlineForm').querySelector('[name="Group"]').value = member.Group || '';
                    document.getElementById('addMemberInlineForm').querySelector('[name="Designation"]').value = member.Designation || '';
                    document.getElementById('addMemberInlineForm').querySelector('[name="Entitlement"]').value = member.Entitlement || '';
                    document.getElementById('addMemberInlineForm').querySelector('[name="Phone"]').value = member.Phone || '';
                    document.getElementById('addMemberInlineForm').querySelector('[name="Email"]').value = member.Email || '';
                    document.getElementById('addMemberInlineForm').querySelector('[name="FinePerDay"]').value = member.FinePerDay || '';
                    document.getElementById('addMemberInlineForm').querySelector('[name="AdmissionDate"]').value = member.AdmissionDate || '';
                    document.getElementById('addMemberInlineForm').querySelector('[name="Status"]').value = member.Status || '';
                    
                    // Store member number for update
                    document.getElementById('addMemberInlineForm').dataset.memberNo = memberNo;
                    
                    // Scroll to form
                    document.getElementById('addMemberInlineForm').scrollIntoView({ behavior: 'smooth' });
                    alert('Member data loaded in form. Update the fields and click Save.');
                    // TODO: Change button to "Update Member" and implement update API call
                } else {
                    alert('Error loading member details: ' + result.message);
                }
            } catch (error) {
                console.error('Error editing member:', error);
                alert('Error loading member details. Please try again.');
            }
        }

        async function deleteMember(memberNo) {
            if (!confirm(`Are you sure you want to delete Member No: ${memberNo}?\n\nThis action cannot be undone.`)) {
                return;
            }

            try {
                const response = await fetch('api/members.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'delete',
                        MemberNo: memberNo
                    })
                });

                const result = await response.json();

                if (result.success) {
                    alert('Member deactivated successfully!');
                    loadMembersTable();
                    loadStatistics();
                } else {
                    alert('Error deleting member: ' + result.message);
                }
            } catch (error) {
                console.error('Error deleting member:', error);
                alert('Error deleting member. Please try again.');
            }
        }

        // Other functions
        function generateMemberCards() {
            showTab('member-cards');
        }

        async function bulkOperations() {
            const selectedMembers = Array.from(document.querySelectorAll('.member-checkbox:checked'))
                .map(cb => cb.value);
            
            if (selectedMembers.length === 0) {
                alert('Please select at least one member to perform bulk operations.');
                return;
            }
            
            const action = prompt(`Selected ${selectedMembers.length} member(s).\n\nChoose action:\n1. Generate Cards\n2. Send Notifications\n3. Change Status\n4. Export to Excel\n\nEnter option number (1-4):`);
            
            switch(action) {
                case '1':
                    alert(`Generating ID cards for ${selectedMembers.length} selected members...`);
                    // TODO: Implement card generation
                    break;
                case '2':
                    alert(`Sending notifications to ${selectedMembers.length} selected members...`);
                    // TODO: Implement notification sending
                    break;
                case '3':
                    const newStatus = prompt('Enter new status (Active/Inactive/Suspended):');
                    if (newStatus) {
                        alert(`Updating status to ${newStatus} for ${selectedMembers.length} members...`);
                        // TODO: Implement bulk status update
                    }
                    break;
                case '4':
                    alert(`Exporting ${selectedMembers.length} members to Excel...`);
                    // TODO: Implement Excel export
                    break;
                default:
                    if (action) alert('Invalid option selected.');
            }
        }

        function selectAllMembers() {
            const selectAll = document.getElementById('selectAllMembers');
            const checkboxes = document.querySelectorAll('.member-checkbox');
            
            checkboxes.forEach(cb => {
                cb.checked = selectAll.checked;
            });
        }

        function editEntitlement(entitlement) {
            const details = memberEntitlements[entitlement];
            const newMaxBooks = prompt(`Edit ${entitlement} Entitlement\n\nCurrent Max Books: ${details.max_books}\nEnter new value:`, details.max_books);
            const newPeriod = prompt(`Current Issue Period: ${details.issue_period} days\nEnter new value:`, details.issue_period);
            const newFine = prompt(`Current Fine Per Day: ₹${details.fine_per_day}\nEnter new value:`, details.fine_per_day);
            
            if (newMaxBooks && newPeriod && newFine) {
                alert(`Entitlement updated successfully!\n\nNote: This is demo mode. In production, this would update the database.`);
                // TODO: Implement entitlement update API
            }
        }

        function generateAllCards() {
            if (confirm('Generate ID cards for ALL active members?\n\nThis may take some time for large member lists.')) {
                alert('Generating ID cards for all active members...\n\nCards will be available for download in the Downloads section.');
                // TODO: Implement bulk card generation
            }
        }

        async function generateSelectedCards() {
            const selectedMembers = Array.from(document.querySelectorAll('.member-checkbox:checked'))
                .map(cb => cb.value);
            
            if (selectedMembers.length === 0) {
                alert('Please select members from the "All Members" tab first.');
                showTab('all-members');
                return;
            }
            
            alert(`Generating ID cards for ${selectedMembers.length} selected member(s)...\n\nCards will be available for download shortly.`);
            // TODO: Implement selected card generation
        }

        function printExistingCards() {
            alert('Opening card printing interface...\n\nYou can select previously generated cards to print.');
            // TODO: Implement card printing interface
        }

        function cardTemplateSettings() {
            alert('Opening card template configuration...\n\nCustomize:\n- Card dimensions\n- Logo and branding\n- QR code position\n- Color scheme');
            // TODO: Implement template settings
        }

        async function generateReport(reportType) {
            alert(`Generating ${reportType} report...\n\nReport will be downloaded shortly.`);
            
            // TODO: Implement report generation
            switch(reportType) {
                case 'members-list':
                    // Export all members to Excel/PDF
                    break;
                case 'active-issues':
                    // Report of all active book issues
                    break;
                case 'overdue-fines':
                    // Report of overdue books and fines
                    break;
                case 'membership-stats':
                    // Statistical analysis
                    break;
            }
        }

        // Load statistics
        async function loadStatistics() {
            try {
                const response = await fetch('api/members.php?action=list');
                const result = await response.json();
                
                if (result.success) {
                    const members = result.data || [];
                    const totalMembers = members.length;
                    const activeMembers = members.filter(m => m.Status === 'Active').length;
                    const facultyMembers = members.filter(m => m.Group === 'Faculty').length;
                    const staffMembers = members.filter(m => m.Group === 'Staff').length;
                    const studentMembers = members.filter(m => m.Group === 'Student').length;
                    const inactiveMembers = members.filter(m => m.Status === 'Inactive' || m.Status === 'Suspended').length;

                    document.getElementById('totalMembers').textContent = totalMembers;
                    document.getElementById('activeMembers').textContent = activeMembers;
                    document.getElementById('facultyMembers').textContent = facultyMembers;
                    document.getElementById('staffMembers').textContent = staffMembers;
                    document.getElementById('studentMembers').textContent = studentMembers;
                    document.getElementById('inactiveMembers').textContent = inactiveMembers;
                } else {
                    // Show dash if failed
                    document.getElementById('totalMembers').textContent = '-';
                    document.getElementById('activeMembers').textContent = '-';
                    document.getElementById('facultyMembers').textContent = '-';
                    document.getElementById('staffMembers').textContent = '-';
                    document.getElementById('studentMembers').textContent = '-';
                    document.getElementById('inactiveMembers').textContent = '-';
                }
            } catch (error) {
                console.error('Error loading statistics:', error);
            }
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
            loadMembersTable();
        });
    </script>
</body>

</html>