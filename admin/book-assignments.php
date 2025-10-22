<?php
session_start();

// No database connection needed for frontend development
// Sample data will be used to demonstrate functionality

$admin_id = $_SESSION['admin_id'] ?? 1;
$admin_name = $_SESSION['admin_name'] ?? 'Admin User';

// Sample data matching database schema and academic requirements
$sampleAssignments = [
    [
        'AssignmentID' => 1,
        'CourseCode' => 'CS101',
        'CourseName' => 'Introduction to Programming',
        'Branch' => 'Computer Engineering',
        'Semester' => 1,
        'Year' => 2024,
        'Faculty' => 'Dr. Amit Sharma',
        'CatNo' => 1001,
        'Title' => 'Programming Fundamentals with C++',
        'Author' => 'Deitel & Deitel',
        'AssignmentType' => 'Textbook',
        'Priority' => 'High',
        'RequiredCopies' => 50,
        'AvailableCopies' => 35,
        'Status' => 'Active',
        'DateAssigned' => '2024-08-15',
        'ValidTill' => '2024-12-30'
    ],
    [
        'AssignmentID' => 2,
        'CourseCode' => 'ME201',
        'CourseName' => 'Thermodynamics',
        'Branch' => 'Mechanical Engineering',
        'Semester' => 3,
        'Year' => 2024,
        'Faculty' => 'Prof. Rajesh Kumar',
        'CatNo' => 1045,
        'Title' => 'Engineering Thermodynamics',
        'Author' => 'P.K. Nag',
        'AssignmentType' => 'Reference',
        'Priority' => 'Medium',
        'RequiredCopies' => 25,
        'AvailableCopies' => 18,
        'Status' => 'Active',
        'DateAssigned' => '2024-08-20',
        'ValidTill' => '2024-12-30'
    ],
    [
        'AssignmentID' => 3,
        'CourseCode' => 'EE301',
        'CourseName' => 'Digital Signal Processing',
        'Branch' => 'Electronics Engineering',
        'Semester' => 5,
        'Year' => 2024,
        'Faculty' => 'Dr. Priya Patel',
        'CatNo' => 1078,
        'Title' => 'Digital Signal Processing: Principles and Applications',
        'Author' => 'Proakis & Manolakis',
        'AssignmentType' => 'Textbook',
        'Priority' => 'High',
        'RequiredCopies' => 30,
        'AvailableCopies' => 2,
        'Status' => 'Shortage',
        'DateAssigned' => '2024-08-10',
        'ValidTill' => '2024-12-30'
    ]
];

$sampleCourses = [
    ['CourseCode' => 'CS101', 'CourseName' => 'Introduction to Programming', 'Branch' => 'Computer Engineering', 'Semester' => 1],
    ['CourseCode' => 'CS201', 'CourseName' => 'Data Structures', 'Branch' => 'Computer Engineering', 'Semester' => 3],
    ['CourseCode' => 'ME201', 'CourseName' => 'Thermodynamics', 'Branch' => 'Mechanical Engineering', 'Semester' => 3],
    ['CourseCode' => 'EE301', 'CourseName' => 'Digital Signal Processing', 'Branch' => 'Electronics Engineering', 'Semester' => 5],
    ['CourseCode' => 'CE401', 'CourseName' => 'Structural Analysis', 'Branch' => 'Civil Engineering', 'Semester' => 7],
];

$sampleBooks = [
    ['CatNo' => 1001, 'Title' => 'Programming Fundamentals with C++', 'Author1' => 'Deitel & Deitel', 'Subject' => 'Computer Science', 'AvailableCopies' => 35],
    ['CatNo' => 1045, 'Title' => 'Engineering Thermodynamics', 'Author1' => 'P.K. Nag', 'Subject' => 'Mechanical Engineering', 'AvailableCopies' => 18],
    ['CatNo' => 1078, 'Title' => 'Digital Signal Processing: Principles and Applications', 'Author1' => 'Proakis & Manolakis', 'Subject' => 'Electronics Engineering', 'AvailableCopies' => 2],
];

$assignmentTypes = ['Textbook', 'Reference', 'Lab Manual', 'Supplementary', 'Project Guide'];
$priorities = ['High', 'Medium', 'Low'];
$branches = ['Computer Engineering', 'Mechanical Engineering', 'Electronics Engineering', 'Civil Engineering', 'Information Technology'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Assignments Management</title>
    <style>
        .assignments-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #cfac69;
        }

        .assignments-title {
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
            .assignments-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .assignments-title {
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

        .stat-card.shortage {
            border-left-color: #dc3545;
        }

        .stat-card.active {
            border-left-color: #28a745;
        }

        .stat-card.pending {
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

        .assignments-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .assignments-table th {
            background-color: #263c79;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .assignments-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
        }

        .assignments-table tr:hover {
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

        .status-shortage {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .priority-badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .priority-high {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .priority-medium {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        .priority-low {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .type-badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
        }

        .type-textbook {
            background-color: rgba(38, 60, 121, 0.1);
            color: #263c79;
        }

        .type-reference {
            background-color: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
        }

        .type-lab {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
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

        .shortage-alert {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
        }

        .shortage-alert h4 {
            color: #856404;
            margin-bottom: 10px;
        }

        .shortage-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .shortage-item {
            padding: 8px 0;
            border-bottom: 1px solid #f0c674;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .shortage-item:last-child {
            border-bottom: none;
        }

        .reading-list-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .course-info h3 {
            color: #263c79;
            margin: 0 0 5px 0;
        }

        .course-details {
            color: #6c757d;
            font-size: 14px;
        }

        .book-list {
            margin-top: 15px;
        }

        .book-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f8f9fa;
        }

        .book-item:last-child {
            border-bottom: none;
        }

        .book-info {
            flex: 1;
        }

        .book-title {
            font-weight: 600;
            color: #263c79;
            margin-bottom: 3px;
        }

        .book-author {
            color: #6c757d;
            font-size: 13px;
        }

        .book-stats {
            display: flex;
            gap: 15px;
            align-items: center;
            font-size: 13px;
        }

        .copies-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .copies-available {
            font-weight: 600;
            font-size: 16px;
        }

        .copies-required {
            color: #6c757d;
            font-size: 12px;
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

            .assignments-table {
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

            .course-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .book-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }

        @media (max-width: 480px) {
            .modal-content {
                margin-top: 135px;
                width: 99%;
                max-height: 75vh;
            }
        }

        .form-actions {
            padding: 20px 0 0 0;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="assignments-header">
        <h1 class="assignments-title">
            <i class="fas fa-clipboard-list"></i>
            Book Assignments Management
        </h1>
        <div class="action-buttons">
            <button class="btn btn-info" onclick="generateReadingLists()">
                <i class="fas fa-list-alt"></i>
                Generate Lists
            </button>
            <button class="btn btn-warning" onclick="checkShortages()">
                <i class="fas fa-exclamation-triangle"></i>
                Check Shortages
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number" id="totalAssignments">-</div>
            <div class="stat-label">Total Assignments</div>
        </div>
        <div class="stat-card active">
            <div class="stat-number" id="activeAssignments">-</div>
            <div class="stat-label">Active Assignments</div>
        </div>
        <div class="stat-card shortage">
            <div class="stat-number" id="shortageItems">-</div>
            <div class="stat-label">Books in Shortage</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" id="totalCourses">-</div>
            <div class="stat-label">Courses Covered</div>
        </div>
    </div>

        <!-- Add New Assignment Section -->
        <div class="add-assignment-section">
            <h3 class="section-title">
                Create Book Assignment
            </h3>
            <form id="addAssignmentInlineForm">
                <!-- Course Information Section -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-graduation-cap"></i>
                        Course Information
                    </div>
                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="courseCodeInline">Course Code <span class="required">*</span></label>
                            <select id="courseCodeInline" name="CourseCode" required onchange="loadCourseDetails()">
                                <option value="">Select Course</option>
                                <option value="CS101">CS101 - Introduction to Programming</option>
                                <option value="CS201">CS201 - Data Structures</option>
                                <option value="ME201">ME201 - Thermodynamics</option>
                                <option value="EE301">EE301 - Digital Signal Processing</option>
                                <option value="CE401">CE401 - Structural Analysis</option>
                            </select>
                        </div>
                        <div class="form-group-modal">
                            <label for="facultyInline">Faculty <span class="required">*</span></label>
                            <input type="text" id="facultyInline" name="Faculty" placeholder="Faculty name" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="semesterInline">Semester <span class="required">*</span></label>
                            <select id="semesterInline" name="Semester" required>
                                <option value="">Select Semester</option>
                                <option value="1">1st Semester</option>
                                <option value="2">2nd Semester</option>
                                <option value="3">3rd Semester</option>
                                <option value="4">4th Semester</option>
                                <option value="5">5th Semester</option>
                                <option value="6">6th Semester</option>
                                <option value="7">7th Semester</option>
                                <option value="8">8th Semester</option>
                            </select>
                        </div>
                        <div class="form-group-modal">
                            <label for="academicYearInline">Academic Year <span class="required">*</span></label>
                            <select id="academicYearInline" name="Year" required>
                                <option value="">Select Year</option>
                                <option value="2024">2024-25</option>
                                <option value="2025">2025-26</option>
                                <option value="2026">2026-27</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Book Selection Section -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-book"></i>
                        Book Selection
                    </div>
                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="bookSearchInline">Search Book <span class="required">*</span></label>
                            <input type="text" id="bookSearchInline" placeholder="Search by title, author, or ISBN..." onkeyup="searchBooks()">
                            <div id="bookSearchResultsInline" style="display: none; border: 1px solid #ddd; border-top: none; max-height: 200px; overflow-y: auto; background: white;"></div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="selectedBookInline">Selected Book</label>
                            <input type="text" id="selectedBookInline" name="SelectedBook" readonly placeholder="No book selected">
                            <input type="hidden" id="selectedCatNoInline" name="CatNo">
                        </div>
                    </div>
                </div>

                <!-- Assignment Details Section -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-cog"></i>
                        Assignment Details
                    </div>
                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="assignmentTypeInline">Assignment Type <span class="required">*</span></label>
                            <select id="assignmentTypeInline" name="AssignmentType" required>
                                <option value="">Select Type</option>
                                <option value="Textbook">Textbook</option>
                                <option value="Reference">Reference Book</option>
                                <option value="Lab Manual">Lab Manual</option>
                                <option value="Supplementary">Supplementary Reading</option>
                                <option value="Project Guide">Project Guide</option>
                            </select>
                        </div>
                        <div class="form-group-modal">
                            <label for="priorityInline">Priority <span class="required">*</span></label>
                            <select id="priorityInline" name="Priority" required>
                                <option value="">Select Priority</option>
                                <option value="High">High (Essential)</option>
                                <option value="Medium">Medium (Recommended)</option>
                                <option value="Low">Low (Optional)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="requiredCopiesInline">Required Copies <span class="required">*</span></label>
                            <input type="number" id="requiredCopiesInline" name="RequiredCopies" min="1" max="200" required>
                        </div>
                        <div class="form-group-modal">
                            <label for="validTillInline">Valid Till <span class="required">*</span></label>
                            <input type="date" id="validTillInline" name="ValidTill" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group-modal">
                            <label for="assignmentNotesInline">Notes/Remarks</label>
                            <textarea id="assignmentNotesInline" name="Notes" rows="3" placeholder="Any special instructions or remarks..."></textarea>
                        </div>
                    </div>
                    <!-- Form Actions -->
                    <div class="form-actions" style="justify-content:flex-start;">
                        <button type="submit" class="btn btn-success" onclick="saveAssignmentInline(); return false;">
                            <i class="fas fa-paper-plane"></i>
                            Create Assignment
                        </button>
                    </div>
                </div>
            </form>
        </div>

    <!-- Tabs -->
    <div class="tabs-container">
        <div class="tab-buttons">
            <button class="tab-btn active" onclick="showTab('all-assignments')">
                <i class="fas fa-clipboard-list"></i>
                All Assignments
            </button>
            <button class="tab-btn" onclick="showTab('reading-lists')">
                <i class="fas fa-list-alt"></i>
                Reading Lists
            </button>
            <button class="tab-btn" onclick="showTab('shortages')">
                <i class="fas fa-exclamation-triangle"></i>
                Shortages & Alerts
            </button>
            <button class="tab-btn" onclick="showTab('reports')">
                <i class="fas fa-chart-pie"></i>
                Reports
            </button>
        </div>

        <!-- All Assignments Tab -->
        <div id="all-assignments" class="tab-content active">
            <div class="search-filters">
                <div class="search-row">
                    <div class="form-group">
                        <label for="searchCourse">Course</label>
                        <input type="text" id="searchCourse" class="form-control" placeholder="Search by course code or name...">
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
                        <label for="searchType">Assignment Type</label>
                        <select id="searchType" class="form-control">
                            <option value="">All Types</option>
                            <option value="Textbook">Textbook</option>
                            <option value="Reference">Reference</option>
                            <option value="Lab Manual">Lab Manual</option>
                            <option value="Supplementary">Supplementary</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="searchStatus">Status</label>
                        <select id="searchStatus" class="form-control">
                            <option value="">All Status</option>
                            <option value="Active">Active</option>
                            <option value="Shortage">Shortage</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-primary" onclick="searchAssignments()">
                            <i class="fas fa-search"></i>
                            Search
                        </button>
                    </div>
                </div>
            </div>

            <div id="assignmentsTableContainer">
                <!-- Assignments table will be loaded here -->
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i>
                    <p style="margin-top: 10px;">Loading assignments...</p>
                </div>
            </div>
        </div>

        <!-- Reading Lists Tab -->
        <div id="reading-lists" class="tab-content">
            <div id="readingListsContent">
                <!-- Reading lists will be loaded here -->
            </div>
        </div>

        <!-- Shortages Tab -->
        <div id="shortages" class="tab-content">
            <div id="shortagesContent">
                <!-- Shortage analysis will be loaded here -->
            </div>
        </div>

        <!-- Reports Tab -->
        <div id="reports" class="tab-content">
            <div id="reportsContent">
                <div style="text-align: center; padding: 40px; color: #6c757d;">
                    <i class="fas fa-chart-pie" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <h3>Assignment Reports</h3>
                    <p>Generate comprehensive reports on book assignments and usage.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Assignment Modal -->
    <div id="addAssignmentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Create Book Assignment</h3>
                <button class="close" onclick="closeModal('addAssignmentModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="addAssignmentForm">
                    <!-- Course Information Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-graduation-cap"></i>
                            Course Information
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="courseCode">Course Code <span class="required">*</span></label>
                                <select id="courseCode" name="CourseCode" required onchange="loadCourseDetails()">
                                    <option value="">Select Course</option>
                                    <option value="CS101">CS101 - Introduction to Programming</option>
                                    <option value="CS201">CS201 - Data Structures</option>
                                    <option value="ME201">ME201 - Thermodynamics</option>
                                    <option value="EE301">EE301 - Digital Signal Processing</option>
                                    <option value="CE401">CE401 - Structural Analysis</option>
                                </select>
                            </div>
                            <div class="form-group-modal">
                                <label for="faculty">Faculty <span class="required">*</span></label>
                                <input type="text" id="faculty" name="Faculty" placeholder="Faculty name" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="semester">Semester <span class="required">*</span></label>
                                <select id="semester" name="Semester" required>
                                    <option value="">Select Semester</option>
                                    <option value="1">1st Semester</option>
                                    <option value="2">2nd Semester</option>
                                    <option value="3">3rd Semester</option>
                                    <option value="4">4th Semester</option>
                                    <option value="5">5th Semester</option>
                                    <option value="6">6th Semester</option>
                                    <option value="7">7th Semester</option>
                                    <option value="8">8th Semester</option>
                                </select>
                            </div>
                            <div class="form-group-modal">
                                <label for="academicYear">Academic Year <span class="required">*</span></label>
                                <select id="academicYear" name="Year" required>
                                    <option value="">Select Year</option>
                                    <option value="2024">2024-25</option>
                                    <option value="2025">2025-26</option>
                                    <option value="2026">2026-27</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Book Selection Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-book"></i>
                            Book Selection
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="bookSearch">Search Book <span class="required">*</span></label>
                                <input type="text" id="bookSearch" placeholder="Search by title, author, or ISBN..." onkeyup="searchBooks()">
                                <div id="bookSearchResults" style="display: none; border: 1px solid #ddd; border-top: none; max-height: 200px; overflow-y: auto; background: white;"></div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="selectedBook">Selected Book</label>
                                <input type="text" id="selectedBook" name="SelectedBook" readonly placeholder="No book selected">
                                <input type="hidden" id="selectedCatNo" name="CatNo">
                            </div>
                        </div>
                    </div>

                    <!-- Assignment Details Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-cog"></i>
                            Assignment Details
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="assignmentType">Assignment Type <span class="required">*</span></label>
                                <select id="assignmentType" name="AssignmentType" required>
                                    <option value="">Select Type</option>
                                    <option value="Textbook">Textbook</option>
                                    <option value="Reference">Reference Book</option>
                                    <option value="Lab Manual">Lab Manual</option>
                                    <option value="Supplementary">Supplementary Reading</option>
                                    <option value="Project Guide">Project Guide</option>
                                </select>
                            </div>
                            <div class="form-group-modal">
                                <label for="priority">Priority <span class="required">*</span></label>
                                <select id="priority" name="Priority" required>
                                    <option value="">Select Priority</option>
                                    <option value="High">High (Essential)</option>
                                    <option value="Medium">Medium (Recommended)</option>
                                    <option value="Low">Low (Optional)</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="requiredCopies">Required Copies <span class="required">*</span></label>
                                <input type="number" id="requiredCopies" name="RequiredCopies" min="1" max="200" required>
                            </div>
                            <div class="form-group-modal">
                                <label for="validTill">Valid Till <span class="required">*</span></label>
                                <input type="date" id="validTill" name="ValidTill" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group-modal">
                                <label for="assignmentNotes">Notes/Remarks</label>
                                <textarea id="assignmentNotes" name="Notes" rows="3" placeholder="Any special instructions or remarks..."></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addAssignmentModal')">Cancel</button>
                <button type="button" class="btn btn-success" onclick="saveAssignment()">
                    <i class="fas fa-save"></i>
                    Create Assignment
                </button>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        const sampleAssignments = <?php echo json_encode($sampleAssignments); ?>;
        const sampleCourses = <?php echo json_encode($sampleCourses); ?>;
        const sampleBooks = <?php echo json_encode($sampleBooks); ?>;

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
                case 'all-assignments':
                    loadAssignmentsTable();
                    break;
                case 'reading-lists':
                    loadReadingLists();
                    break;
                case 'shortages':
                    loadShortages();
                    break;
                case 'reports':
                    loadReports();
                    break;
            }
        }

        function loadAssignmentsTable(searchParams = {}) {
            let filteredAssignments = sampleAssignments;

            // Apply search filters
            if (searchParams.course) {
                filteredAssignments = filteredAssignments.filter(assignment =>
                    assignment.CourseCode.toLowerCase().includes(searchParams.course.toLowerCase()) ||
                    assignment.CourseName.toLowerCase().includes(searchParams.course.toLowerCase())
                );
            }
            if (searchParams.branch) {
                filteredAssignments = filteredAssignments.filter(assignment =>
                    assignment.Branch === searchParams.branch
                );
            }
            if (searchParams.type) {
                filteredAssignments = filteredAssignments.filter(assignment =>
                    assignment.AssignmentType === searchParams.type
                );
            }
            if (searchParams.status) {
                filteredAssignments = filteredAssignments.filter(assignment =>
                    assignment.Status === searchParams.status
                );
            }

            let tableHTML = `
                <table class="assignments-table">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Book Details</th>
                            <th>Type</th>
                            <th>Priority</th>
                            <th>Faculty</th>
                            <th>Required/Available</th>
                            <th>Status</th>
                            <th>Valid Till</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            if (filteredAssignments.length === 0) {
                tableHTML += `
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px; color: #6c757d;">
                            <i class="fas fa-search" style="font-size: 24px; margin-bottom: 10px;"></i>
                            <p>No assignments found matching your search criteria.</p>
                        </td>
                    </tr>
                `;
            } else {
                filteredAssignments.forEach(assignment => {
                    const statusClass = {
                        'Active': 'status-active',
                        'Shortage': 'status-shortage',
                        'Pending': 'status-pending'
                    } [assignment.Status] || 'status-active';

                    const priorityClass = {
                        'High': 'priority-high',
                        'Medium': 'priority-medium',
                        'Low': 'priority-low'
                    } [assignment.Priority] || 'priority-medium';

                    const typeClass = {
                        'Textbook': 'type-textbook',
                        'Reference': 'type-reference',
                        'Lab Manual': 'type-lab'
                    } [assignment.AssignmentType] || 'type-textbook';

                    const availabilityColor = assignment.AvailableCopies < assignment.RequiredCopies * 0.5 ? '#dc3545' :
                        assignment.AvailableCopies < assignment.RequiredCopies * 0.8 ? '#ffc107' : '#28a745';

                    tableHTML += `
                        <tr>
                            <td>
                                <strong>${assignment.CourseCode}</strong><br>
                                <small style="color: #6c757d;">${assignment.CourseName}</small><br>
                                <small style="color: #6c757d;">${assignment.Branch} - Sem ${assignment.Semester}</small>
                            </td>
                            <td>
                                <strong>${assignment.Title}</strong><br>
                                <small style="color: #6c757d;">by ${assignment.Author}</small>
                            </td>
                            <td><span class="type-badge ${typeClass}">${assignment.AssignmentType}</span></td>
                            <td><span class="priority-badge ${priorityClass}">${assignment.Priority}</span></td>
                            <td>${assignment.Faculty}</td>
                            <td>
                                <div style="text-align: center;">
                                    <div style="color: ${availabilityColor}; font-weight: 600; font-size: 16px;">
                                        ${assignment.AvailableCopies}/${assignment.RequiredCopies}
                                    </div>
                                    <small style="color: #6c757d;">Available/Required</small>
                                </div>
                            </td>
                            <td><span class="status-badge ${statusClass}">${assignment.Status}</span></td>
                            <td>${new Date(assignment.ValidTill).toLocaleDateString('en-IN')}</td>
                            <td class="action-links">
                                <a href="#" class="btn-view" onclick="viewAssignment(${assignment.AssignmentID})">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="#" class="btn-edit" onclick="editAssignment(${assignment.AssignmentID})">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn-delete" onclick="deleteAssignment(${assignment.AssignmentID})">
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

            document.getElementById('assignmentsTableContainer').innerHTML = tableHTML;
        }

        function loadReadingLists() {
            // Group assignments by course
            const courseAssignments = {};
            sampleAssignments.forEach(assignment => {
                if (!courseAssignments[assignment.CourseCode]) {
                    courseAssignments[assignment.CourseCode] = {
                        course: assignment,
                        books: []
                    };
                }
                courseAssignments[assignment.CourseCode].books.push(assignment);
            });

            let readingListsHTML = `
                <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="color: #263c79; margin: 0;">Reading Lists by Course</h3>
                    <button class="btn btn-primary" onclick="generateAllReadingLists()">
                        <i class="fas fa-file-pdf"></i>
                        Generate PDF Lists
                    </button>
                </div>
            `;

            Object.values(courseAssignments).forEach(courseData => {
                const course = courseData.course;
                const books = courseData.books;

                readingListsHTML += `
                    <div class="reading-list-card">
                        <div class="course-header">
                            <div class="course-info">
                                <h3>${course.CourseCode} - ${course.CourseName}</h3>
                                <div class="course-details">
                                    ${course.Branch} | Semester ${course.Semester} | Faculty: ${course.Faculty}
                                </div>
                            </div>
                            <div class="action-links">
                                <button class="btn btn-primary" onclick="generateCourseList('${course.CourseCode}')">
                                    <i class="fas fa-download"></i>
                                    Download PDF
                                </button>
                                <button class="btn btn-secondary" onclick="emailCourseList('${course.CourseCode}')">
                                    <i class="fas fa-envelope"></i>
                                    Email List
                                </button>
                            </div>
                        </div>
                        <div class="book-list">
                `;

                books.forEach(book => {
                    const availabilityColor = book.AvailableCopies < book.RequiredCopies * 0.5 ? '#dc3545' :
                        book.AvailableCopies < book.RequiredCopies * 0.8 ? '#ffc107' : '#28a745';

                    readingListsHTML += `
                        <div class="book-item">
                            <div class="book-info">
                                <div class="book-title">${book.Title}</div>
                                <div class="book-author">by ${book.Author}</div>
                            </div>
                            <div class="book-stats">
                                <span class="type-badge ${book.AssignmentType.toLowerCase()}">${book.AssignmentType}</span>
                                <span class="priority-badge priority-${book.Priority.toLowerCase()}">${book.Priority}</span>
                                <div class="copies-info">
                                    <div class="copies-available" style="color: ${availabilityColor};">
                                        ${book.AvailableCopies}/${book.RequiredCopies}
                                    </div>
                                    <div class="copies-required">Available/Required</div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                readingListsHTML += `
                        </div>
                    </div>
                `;
            });

            document.getElementById('readingListsContent').innerHTML = readingListsHTML;
        }

        function loadShortages() {
            const shortages = sampleAssignments.filter(assignment =>
                assignment.AvailableCopies < assignment.RequiredCopies
            );

            let shortagesHTML = `
                <div style="margin-bottom: 20px;">
                    <h3 style="color: #263c79; margin-bottom: 15px;">
                        <i class="fas fa-exclamation-triangle" style="color: #dc3545; margin-right: 8px;"></i>
                        Book Shortages Analysis
                    </h3>
                </div>
            `;

            if (shortages.length === 0) {
                shortagesHTML += `
                    <div style="text-align: center; padding: 40px; color: #28a745; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                        <i class="fas fa-check-circle" style="font-size: 48px; margin-bottom: 15px;"></i>
                        <h3 style="color: #28a745; margin-bottom: 10px;">No Shortages Found</h3>
                        <p>All book assignments have adequate copies available.</p>
                    </div>
                `;
            } else {
                shortagesHTML += `
                    <div class="shortage-alert">
                        <h4><i class="fas fa-exclamation-triangle"></i> Critical Shortages Detected</h4>
                        <p>The following book assignments require immediate attention due to insufficient copies:</p>
                        <ul class="shortage-list">
                `;

                shortages.forEach(shortage => {
                    const deficit = shortage.RequiredCopies - shortage.AvailableCopies;
                    const percentageShort = Math.round((deficit / shortage.RequiredCopies) * 100);

                    shortagesHTML += `
                        <li class="shortage-item">
                            <div>
                                <strong>${shortage.Title}</strong><br>
                                <small>${shortage.CourseCode} - ${shortage.CourseName}</small>
                            </div>
                            <div style="text-align: right;">
                                <div style="color: #dc3545; font-weight: 600;">
                                    ${deficit} copies short (${percentageShort}%)
                                </div>
                                <div style="font-size: 12px; color: #6c757d;">
                                    Need: ${shortage.RequiredCopies} | Have: ${shortage.AvailableCopies}
                                </div>
                            </div>
                        </li>
                    `;
                });

                shortagesHTML += `
                        </ul>
                        <div style="margin-top: 15px; text-align: center;">
                            <button class="btn btn-warning" onclick="generatePurchaseOrder()">
                                <i class="fas fa-shopping-cart"></i>
                                Generate Purchase Order
                            </button>
                            <button class="btn btn-info" onclick="notifyAcquisition()">
                                <i class="fas fa-bell"></i>
                                Notify Acquisition Team
                            </button>
                        </div>
                    </div>
                `;
            }

            document.getElementById('shortagesContent').innerHTML = shortagesHTML;
        }

        function loadReports() {
            const reportsHTML = `
                <div style="margin-bottom: 20px;">
                    <h3 style="color: #263c79; margin-bottom: 15px;">Assignment Reports & Analytics</h3>
                    <p style="color: #6c757d;">Generate detailed reports on book assignments and course requirements.</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div onclick="generateReport('assignment-summary')" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-clipboard-list" style="font-size: 24px; color: #263c79; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">Assignment Summary</h4>
                        <p style="color: #6c757d; font-size: 14px;">Complete overview of all book assignments</p>
                    </div>
                    
                    <div onclick="generateReport('shortage-analysis')" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-exclamation-triangle" style="font-size: 24px; color: #dc3545; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">Shortage Analysis</h4>
                        <p style="color: #6c757d; font-size: 14px;">Books with insufficient copies</p>
                    </div>
                    
                    <div onclick="generateReport('course-wise')" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-graduation-cap" style="font-size: 24px; color: #28a745; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">Course-wise Report</h4>
                        <p style="color: #6c757d; font-size: 14px;">Assignments grouped by courses</p>
                    </div>
                    
                    <div onclick="generateReport('faculty-wise')" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-chalkboard-teacher" style="font-size: 24px; color: #17a2b8; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">Faculty-wise Report</h4>
                        <p style="color: #6c757d; font-size: 14px;">Assignments by faculty members</p>
                    </div>
                </div>
            `;

            document.getElementById('reportsContent').innerHTML = reportsHTML;
        }

        function searchAssignments() {
            const searchParams = {
                course: document.getElementById('searchCourse').value.trim(),
                branch: document.getElementById('searchBranch').value,
                type: document.getElementById('searchType').value,
                status: document.getElementById('searchStatus').value
            };

            loadAssignmentsTable(searchParams);
        }

        // Modal functions
        function openAddAssignmentModal() {
            document.getElementById('addAssignmentModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';

            if (modalId === 'addAssignmentModal') {
                document.getElementById('addAssignmentForm').reset();
                document.getElementById('bookSearchResults').style.display = 'none';
                document.getElementById('selectedBook').value = '';
                document.getElementById('selectedCatNo').value = '';
            }
        }

        function loadCourseDetails() {
            const courseCode = document.getElementById('courseCode').value;
            const course = sampleCourses.find(c => c.CourseCode === courseCode);

            if (course) {
                document.getElementById('semester').value = course.Semester;
                // Set current academic year as default
                document.getElementById('academicYear').value = '2024';
            }
        }

        function searchBooks() {
            const searchTerm = document.getElementById('bookSearch').value.trim();
            const resultsDiv = document.getElementById('bookSearchResults');

            if (searchTerm.length < 2) {
                resultsDiv.style.display = 'none';
                return;
            }

            const matchingBooks = sampleBooks.filter(book =>
                book.Title.toLowerCase().includes(searchTerm.toLowerCase()) ||
                book.Author1.toLowerCase().includes(searchTerm.toLowerCase())
            );

            if (matchingBooks.length > 0) {
                let resultsHTML = '';
                matchingBooks.forEach(book => {
                    resultsHTML += `
                        <div onclick="selectBook(${book.CatNo}, '${book.Title}', '${book.Author1}')" 
                             style="padding: 10px; border-bottom: 1px solid #f0f0f0; cursor: pointer; hover:background-color: #f8f9fa;">
                            <strong>${book.Title}</strong><br>
                            <small style="color: #6c757d;">by ${book.Author1} | Available: ${book.AvailableCopies} copies</small>
                        </div>
                    `;
                });
                resultsDiv.innerHTML = resultsHTML;
                resultsDiv.style.display = 'block';
            } else {
                resultsDiv.innerHTML = '<div style="padding: 10px; color: #6c757d;">No books found</div>';
                resultsDiv.style.display = 'block';
            }
        }

        function selectBook(catNo, title, author) {
            document.getElementById('selectedBook').value = `${title} by ${author}`;
            document.getElementById('selectedCatNo').value = catNo;
            document.getElementById('bookSearchResults').style.display = 'none';
            document.getElementById('bookSearch').value = title;
        }

        function saveAssignment() {
            const formData = new FormData(document.getElementById('addAssignmentForm'));
            const assignmentData = Object.fromEntries(formData);

            if (!assignmentData.CatNo) {
                alert('Please select a book for the assignment.');
                return;
            }

            console.log('Creating assignment:', assignmentData);
            alert('Assignment created successfully!');
            closeModal('addAssignmentModal');
            loadAssignmentsTable();
        }

            function saveAssignmentInline() {
                const formData = new FormData(document.getElementById('addAssignmentInlineForm'));
                const assignmentData = Object.fromEntries(formData);

                if (!assignmentData.CatNo) {
                    alert('Please select a book for the assignment.');
                    return;
                }

                console.log('Creating assignment:', assignmentData);
                alert('Assignment created successfully!');
                document.getElementById('addAssignmentInlineForm').reset();
                loadAssignmentsTable();
            }

        // Assignment actions
        function viewAssignment(assignmentId) {
            console.log('Viewing assignment:', assignmentId);
            alert(`Opening detailed view for Assignment ID: ${assignmentId}`);
        }

        function editAssignment(assignmentId) {
            console.log('Editing assignment:', assignmentId);
            alert(`Opening edit form for Assignment ID: ${assignmentId}`);
        }

        function deleteAssignment(assignmentId) {
            if (confirm(`Are you sure you want to delete Assignment ID: ${assignmentId}?`)) {
                console.log('Deleting assignment:', assignmentId);
                alert('Assignment deleted successfully!');
                loadAssignmentsTable();
            }
        }

        // Other functions
        function generateReadingLists() {
            console.log('Generating reading lists...');
            alert('Generating reading lists for all courses...');
        }

        function checkShortages() {
            console.log('Checking shortages...');
            showTab('shortages');
        }

        function generateAllReadingLists() {
            console.log('Generating all reading lists as PDF...');
            alert('Generating PDF reading lists for all courses...');
        }

        function generateCourseList(courseCode) {
            console.log('Generating course list:', courseCode);
            alert(`Generating PDF reading list for course: ${courseCode}`);
        }

        function emailCourseList(courseCode) {
            console.log('Emailing course list:', courseCode);
            alert(`Sending reading list via email for course: ${courseCode}`);
        }

        function generatePurchaseOrder() {
            console.log('Generating purchase order...');
            alert('Generating purchase order for shortage items...');
        }

        function notifyAcquisition() {
            console.log('Notifying acquisition team...');
            alert('Sending shortage notification to acquisition team...');
        }

        function generateReport(reportType) {
            console.log('Generating report:', reportType);
            alert(`Generating ${reportType} report...`);
        }

        // Load statistics
        function loadStatistics() {
            const totalAssignments = sampleAssignments.length;
            const activeAssignments = sampleAssignments.filter(a => a.Status === 'Active').length;
            const shortageItems = sampleAssignments.filter(a => a.Status === 'Shortage').length;
            const totalCourses = new Set(sampleAssignments.map(a => a.CourseCode)).size;

            document.getElementById('totalAssignments').textContent = totalAssignments;
            document.getElementById('activeAssignments').textContent = activeAssignments;
            document.getElementById('shortageItems').textContent = shortageItems;
            document.getElementById('totalCourses').textContent = totalCourses;
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
            loadAssignmentsTable();
        });
    </script>
</body>

</html>