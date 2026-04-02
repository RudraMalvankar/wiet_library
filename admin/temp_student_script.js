
        // Global cache for students data
        let studentsCache = null;
        let cacheTimestamp = null;
        const CACHE_DURATION = 60000; // 1 minute
        let currentStudentReportData = [];
        let currentStudentReportTitle = '';

        function resetStudentInlineForm() {
            const form = document.getElementById('addStudentInlineForm');
            const submitBtn = document.getElementById('studentInlineSubmitBtn');
            form.reset();
            delete form.dataset.studentId;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Create Student & Generate QR';
            submitBtn.classList.remove('btn-warning');
            submitBtn.classList.add('btn-success');
            const photoPreview = document.getElementById('photoPreviewInline');
            if (photoPreview) {
                photoPreview.innerHTML = '<i class="fas fa-user" style="font-size: 48px; color: #ccc;"></i>';
            }
        }

        function downloadStudentCsv(rows, fileName = 'students-report.csv') {
            if (!rows.length) {
                alert('No records available for export.');
                return;
            }

            const headers = Object.keys(rows[0]);
            const csvRows = rows.map(row => headers.map(key => {
                const value = row[key] ?? '';
                return `"${String(value).replace(/"/g, '""')}"`;
            }).join(','));
            const csv = [headers.join(','), ...csvRows].join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

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

            // Add active class to clicked button - find the button that calls this tab
            const buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(btn => {
                if (btn.getAttribute('onclick') && btn.getAttribute('onclick').includes(`'${tabName}'`)) {
                    btn.classList.add('active');
                }
            });

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

        let csrfToken = null;

        // Fetch CSRF token on page load
        async function fetchCSRFToken() {
            try {
                const response = await fetch('api/members.php?action=get-csrf-token');
                const result = await response.json();
                if (result.success) {
                    csrfToken = result.token;
                    console.log('✅ CSRF token loaded');
                }
            } catch (error) {
                console.error('Failed to load CSRF token:', error);
            }
        }

        async function fetchStudentsData(forceRefresh = false) {
            // Check if cache is valid
            const now = Date.now();
            if (!forceRefresh && studentsCache && cacheTimestamp && (now - cacheTimestamp) < CACHE_DURATION) {
                return studentsCache;
            }

            // Fetch fresh data
            const response = await fetch('api/members.php?action=list_students');
            const result = await response.json();

            if (result.success) {
                studentsCache = result.data || [];
                cacheTimestamp = now;
                return studentsCache;
            } else {
                throw new Error(result.message || 'Failed to load students');
            }
        }

        async function loadStudentsTable(searchParams = {}) {
            // Show loading state
            document.getElementById('studentsTableContainer').innerHTML = `
                <div style="text-align: center; padding: 60px 20px; color: #6c757d;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 48px; margin-bottom: 20px; color: #263c79;"></i>
                    <h3 style="color: #263c79;">Loading Students...</h3>
                    <p>Fetching data from database...</p>
                </div>
            `;

            try {
                let students;
                
                // If search params provided, fetch fresh data
                if (Object.keys(searchParams).length > 0) {
                    const params = new URLSearchParams({ action: 'list_students' });
                    if (searchParams.name) params.append('name', searchParams.name);
                    if (searchParams.prn) params.append('prn', searchParams.prn);
                    if (searchParams.branch) params.append('branch', searchParams.branch);
                    if (searchParams.status) params.append('status', searchParams.status);

                    const response = await fetch(`api/members.php?${params.toString()}`);
                    const result = await response.json();
                    
                    if (!result.success) {
                        throw new Error(result.message || 'Failed to load students');
                    }
                    students = result.data || [];
                } else {
                    // Use cached data for initial load
                    students = await fetchStudentsData();
                }

                displayStudentsTable(students);
                
            } catch (error) {
                console.error('Error loading students:', error);
                document.getElementById('studentsTableContainer').innerHTML = `
                    <div style="text-align: center; padding: 60px 20px; color: #dc3545;">
                        <i class="fas fa-exclamation-circle" style="font-size: 48px; margin-bottom: 20px;"></i>
                        <h3>Error Loading Students</h3>
                        <p>${error.message}</p>
                        <button class="btn btn-primary" onclick="loadStudentsTable()">
                            <i class="fas fa-redo"></i>
                            Retry
                        </button>
                    </div>
                `;
            }
        }

        // Manual refresh function - clears cache and reloads
        async function refreshData() {
            studentsCache = null;
            cacheTimestamp = null;
            
            // Show loading indicator
            const container = document.getElementById('studentsTableContainer');
            container.innerHTML = `
                <div style="text-align: center; padding: 40px; color: navy;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i>
                    <p style="margin-top: 10px;">Refreshing data from server...</p>
                </div>
            `;
            
            // Force fresh data load
            await loadStudentsTable();
            loadStatistics(); // Also refresh statistics
        }

        function displayStudentsTable(students) {
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
                        <button class="btn btn-warning" onclick="bulkOperations(); return false;">
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

            if (students.length === 0) {
                tableHTML += `
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 40px; color: #6c757d;">
                            <i class="fas fa-search" style="font-size: 24px; margin-bottom: 10px;"></i>
                            <p>No students found matching your search criteria.</p>
                        </td>
                    </tr>
                `;
            } else {
                students.forEach(student => {
                    const statusClass = {
                        'Active': 'status-active',
                        'Inactive': 'status-inactive',
                        'Suspended': 'status-suspended'
                    }[student.Status] || 'status-active';
                    
                    // Handle different API response field names
                    const studentName = student.Name || student.MemberName || student.StudentName || 'N/A';
                    const studentEmail = student.Email || student.MemberEmail || '';
                    const studentMobile = student.Mobile || student.Contact || student.Phone || 'N/A';
                    const studentBranch = student.Branch || 'N/A';
                    const studentPRN = student.PRN || student.StudentID || 'N/A';
                    const booksIssued = student.BooksIssued || 0;
                    const validTill = student.ValidTill ? new Date(student.ValidTill).toLocaleDateString('en-IN') : 'N/A';

                    tableHTML += `
                        <tr>
                            <td>
                                <input type="checkbox" class="student-checkbox" value="${student.StudentID}" onchange="updateBulkActionButtons()">
                            </td>
                            <td><strong>${student.MemberNo}</strong></td>
                            <td>
                                <strong>${studentName}</strong><br>
                                <small style="color: #6c757d;">${studentEmail}</small>
                            </td>
                            <td>${studentPRN}</td>
                            <td><span style="background: rgba(38,60,121,0.1); color: #263c79; padding: 2px 6px; border-radius: 3px; font-size: 12px;">${studentBranch}</span></td>
                            <td>${studentMobile}</td>
                            <td><span class="status-badge ${statusClass}">${student.Status}</span></td>
                            <td><span style="color: ${booksIssued > 0 ? '#dc3545' : '#28a745'}; font-weight: 600;">${booksIssued}</span></td>
                            <td>${validTill}</td>
                            <td class="action-links">
                                <a href="#" class="btn-view" onclick="viewStudent(${student.StudentID}); return false;">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="#" class="btn-edit" onclick="editStudent(${student.StudentID}); return false;">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="#" class="btn-delete" onclick="deleteStudent(${student.StudentID}); return false;">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <a href="#" class="btn-view" onclick="viewQRCode(${student.StudentID}); return false;" title="View QR Code">
                                    <i class="fas fa-qrcode"></i>
                                </a>
                            </td>
                        </tr>
                    `;
                });
            }

            tableHTML += `
                    </tbody>
                </table>
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
            const membershipHTML = `
                <div style="margin-bottom: 20px;">
                    <h3 style="color: #263c79; margin-bottom: 15px;">Membership Management</h3>
                    <p style="color: #6c757d; margin-bottom: 20px;">Manage student membership validity, renewals, and status updates.</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                    <!-- Renew Memberships -->
                    <div onclick="renewMemberships()" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-sync-alt" style="font-size: 48px; color: #263c79; margin-bottom: 15px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 10px;">Renew Memberships</h4>
                        <p style="color: #6c757d; font-size: 14px;">Extend validity for expiring members</p>
                    </div>

                    <!-- Expired Members -->
                    <div onclick="viewExpiredMembers()" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-calendar-times" style="font-size: 48px; color: #dc3545; margin-bottom: 15px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 10px;">Expired Members</h4>
                        <p style="color: #6c757d; font-size: 14px;">View and renew expired memberships</p>
                    </div>

                    <!-- Expiring Soon -->
                    <div onclick="viewExpiringSoon()" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #ffc107; margin-bottom: 15px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 10px;">Expiring Soon</h4>
                        <p style="color: #6c757d; font-size: 14px;">Members expiring in next 30 days</p>
                    </div>

                    <!-- Membership Statistics -->
                    <div onclick="viewMembershipStats()" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-chart-pie" style="font-size: 48px; color: #17a2b8; margin-bottom: 15px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 10px;">Statistics</h4>
                        <p style="color: #6c757d; font-size: 14px;">Membership validity analytics</p>
                    </div>
                </div>

                <!-- Membership Summary Table -->
                <div style="margin-top: 30px; background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px;">
                    <h4 style="color: #263c79; margin-bottom: 15px;">Quick Summary</h4>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8f9fa;">
                                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #263c79;">Status</th>
                                <th style="padding: 12px; text-align: center; border-bottom: 2px solid #263c79;">Count</th>
                                <th style="padding: 12px; text-align: center; border-bottom: 2px solid #263c79;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding: 12px; border-bottom: 1px solid #e9ecef;">
                                    <i class="fas fa-check-circle" style="color: #28a745;"></i> Active Memberships
                                </td>
                                <td id="activeMembershipsCount" style="padding: 12px; text-align: center; border-bottom: 1px solid #e9ecef; font-weight: 600;">-</td>
                                <td style="padding: 12px; text-align: center; border-bottom: 1px solid #e9ecef;">
                                    <button class="btn btn-sm btn-primary" onclick="viewActiveMembers()">View</button>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 12px; border-bottom: 1px solid #e9ecef;">
                                    <i class="fas fa-clock" style="color: #ffc107;"></i> Expiring in 30 Days
                                </td>
                                <td id="expiringSoonCount" style="padding: 12px; text-align: center; border-bottom: 1px solid #e9ecef; font-weight: 600;">-</td>
                                <td style="padding: 12px; text-align: center; border-bottom: 1px solid #e9ecef;">
                                    <button class="btn btn-sm btn-warning" onclick="viewExpiringSoon()">View</button>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 12px; border-bottom: 1px solid #e9ecef;">
                                    <i class="fas fa-times-circle" style="color: #dc3545;"></i> Expired Memberships
                                </td>
                                <td id="expiredCount" style="padding: 12px; text-align: center; border-bottom: 1px solid #e9ecef; font-weight: 600;">-</td>
                                <td style="padding: 12px; text-align: center; border-bottom: 1px solid #e9ecef;">
                                    <button class="btn btn-sm btn-danger" onclick="viewExpiredMembers()">View</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            `;

            document.getElementById('membershipContent').innerHTML = membershipHTML;
            loadMembershipSummary();
        }

        async function loadMembershipSummary() {
            try {
                const response = await fetch('api/members.php?action=list_students');
                const result = await response.json();
                
                if (result.success) {
                    const students = result.data || [];
                    const today = new Date();
                    const thirtyDaysFromNow = new Date(today.getTime() + 30 * 24 * 60 * 60 * 1000);
                    
                    const active = students.filter(s => {
                        const validTill = new Date(s.ValidTill);
                        return s.Status === 'Active' && validTill > today;
                    }).length;
                    
                    const expiringSoon = students.filter(s => {
                        const validTill = new Date(s.ValidTill);
                        return s.Status === 'Active' && validTill > today && validTill <= thirtyDaysFromNow;
                    }).length;
                    
                    const expired = students.filter(s => {
                        const validTill = new Date(s.ValidTill);
                        return validTill <= today;
                    }).length;
                    
                    document.getElementById('activeMembershipsCount').textContent = active;
                    document.getElementById('expiringSoonCount').textContent = expiringSoon;
                    document.getElementById('expiredCount').textContent = expired;
                }
            } catch (error) {
                console.error('Error loading membership summary:', error);
            }
        }

        function loadVerificationContent() {
            const verificationHTML = `
                <div style="margin-bottom: 20px;">
                    <h3 style="color: #263c79; margin-bottom: 15px;">Student Verification & QR Codes</h3>
                    <p style="color: #6c757d; margin-bottom: 20px;">Manage student verification, digital IDs, and QR code generation.</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                    <!-- Generate QR Codes -->
                    <div onclick="generateQRCodes()" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-qrcode" style="font-size: 48px; color: #263c79; margin-bottom: 15px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 10px;">Generate QR Codes</h4>
                        <p style="color: #6c757d; font-size: 14px;">Create QR codes for all students</p>
                    </div>

                    <!-- Digital ID Cards -->
                    <div onclick="generateDigitalIDs()" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-id-card" style="font-size: 48px; color: #28a745; margin-bottom: 15px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 10px;">Digital ID Cards</h4>
                        <p style="color: #6c757d; font-size: 14px;">Generate printable student ID cards</p>
                    </div>

                    <!-- Verify Student -->
                    <div onclick="verifyStudent()" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-user-check" style="font-size: 48px; color: #17a2b8; margin-bottom: 15px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 10px;">Verify Student</h4>
                        <p style="color: #6c757d; font-size: 14px;">Scan QR code to verify identity</p>
                    </div>

                    <!-- Bulk QR Generation -->
                    <div onclick="bulkQRGeneration()" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-layer-group" style="font-size: 48px; color: #ffc107; margin-bottom: 15px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 10px;">Bulk Generate</h4>
                        <p style="color: #6c757d; font-size: 14px;">Generate QR codes in batch</p>
                    </div>
                </div>

                <!-- QR Code Scanner Section -->
                <div style="margin-top: 30px; background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px;">
                    <h4 style="color: #263c79; margin-bottom: 15px;">
                        <i class="fas fa-camera"></i> Quick QR Scanner
                    </h4>
                    <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                        <input type="text" id="qrCodeInput" placeholder="Enter PRN or scan QR code" style="flex: 1; min-width: 250px; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        <button class="btn btn-primary" onclick="verifyQRCode()">
                            <i class="fas fa-search"></i> Verify
                        </button>
                        <button class="btn btn-secondary" onclick="openQRScanner()">
                            <i class="fas fa-camera"></i> Scan QR
                        </button>
                    </div>
                    <div id="qrVerificationResult" style="margin-top: 15px;"></div>
                </div>
            `;

            document.getElementById('verificationContent').innerHTML = verificationHTML;
        }

        function loadReportsContent() {
            const reportsHTML = `
                <div style="margin-bottom: 20px;">
                    <h3 style="color: #263c79; margin-bottom: 15px;">Student Reports</h3>
                    <p style="color: #6c757d; margin-bottom: 20px;">Generate comprehensive reports on student data and activities.</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
                    <!-- All Students Report -->
                    <div onclick="generateReport('all-students')" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-users" style="font-size: 32px; color: #263c79; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">All Students</h4>
                        <p style="color: #6c757d; font-size: 14px;">Complete student database export</p>
                    </div>

                    <!-- Branch-wise Report -->
                    <div onclick="generateReport('branch-wise')" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-sitemap" style="font-size: 32px; color: #28a745; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">Branch-wise</h4>
                        <p style="color: #6c757d; font-size: 14px;">Students grouped by branch</p>
                    </div>

                    <!-- Books Issued Report -->
                    <div onclick="generateReport('books-issued')" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-book" style="font-size: 32px; color: #17a2b8; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">Books Issued</h4>
                        <p style="color: #6c757d; font-size: 14px;">Student borrowing statistics</p>
                    </div>

                    <!-- Expired Members Report -->
                    <div onclick="generateReport('expired-members')" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-calendar-times" style="font-size: 32px; color: #dc3545; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">Expired Members</h4>
                        <p style="color: #6c757d; font-size: 14px;">Students with expired validity</p>
                    </div>

                    <!-- Course-wise Report -->
                    <div onclick="generateReport('course-wise')" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-graduation-cap" style="font-size: 32px; color: #ffc107; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">Course-wise</h4>
                        <p style="color: #6c757d; font-size: 14px;">Students by course/program</p>
                    </div>

                    <!-- Custom Report -->
                    <div onclick="generateReport('custom')" style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onmouseover="this.style.borderColor='#cfac69'" onmouseout="this.style.borderColor='#e9ecef'">
                        <i class="fas fa-sliders-h" style="font-size: 32px; color: #6c757d; margin-bottom: 10px;"></i>
                        <h4 style="color: #263c79; margin-bottom: 5px;">Custom Report</h4>
                        <p style="color: #6c757d; font-size: 14px;">Build your own report</p>
                    </div>
                </div>

                <!-- Export Options -->
                <div style="background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px;">
                    <h4 style="color: #263c79; margin-bottom: 15px;">Export Format</h4>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <button class="btn btn-success" onclick="exportReport('excel')">
                            <i class="fas fa-file-excel"></i> Export to Excel
                        </button>
                        <button class="btn btn-danger" onclick="exportReport('pdf')">
                            <i class="fas fa-file-pdf"></i> Export to PDF
                        </button>
                        <button class="btn btn-info" onclick="exportReport('csv')">
                            <i class="fas fa-file-csv"></i> Export to CSV
                        </button>
                        <button class="btn btn-secondary" onclick="printReport()">
                            <i class="fas fa-print"></i> Print Report
                        </button>
                    </div>
                </div>
            `;

            document.getElementById('reportsContent').innerHTML = reportsHTML;
        }

        // Modal functions (for QR Code modal)
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('show');
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

        function normalizeNamePart(value) {
            return (value || '').trim().replace(/\s+/g, ' ');
        }

        function buildMemberNameFromForm(form) {
            if (!form) return '';

            const firstName = normalizeNamePart(form.querySelector('[name="FirstName"]')?.value || '');
            const middleName = normalizeNamePart(form.querySelector('[name="MiddleName"]')?.value || '');
            const surname = normalizeNamePart(form.querySelector('[name="Surname"]')?.value || form.querySelector('[name="LastName"]')?.value || '');
            const fullName = [firstName, middleName, surname].filter(Boolean).join(' ');

            const memberNameInput = form.querySelector('[name="MemberName"]');
            if (memberNameInput && fullName) {
                memberNameInput.value = fullName;
            }

            return fullName;
        }

        function attachMemberNameAutoCompose(formId) {
            const form = document.getElementById(formId);
            if (!form) return;

            ['[name="FirstName"]', '[name="MiddleName"]', '[name="Surname"]', '[name="LastName"]'].forEach(selector => {
                const field = form.querySelector(selector);
                if (field) {
                    field.addEventListener('input', () => buildMemberNameFromForm(form));
                    field.addEventListener('blur', () => buildMemberNameFromForm(form));
                }
            });

            buildMemberNameFromForm(form);
        }

        async function saveStudent() {
            const form = document.getElementById('addStudentForm');
            buildMemberNameFromForm(form);
            const formData = new FormData(form);
            
            // Add action parameter
            formData.append('action', 'add_student');
            formData.append('csrf_token', csrfToken);

            try {
                const response = await fetch('api/members.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert(`Student created successfully!\nMember No: ${result.memberNo}\nStudent ID: ${result.studentId}\nQR Code generated and ready for printing.`);
                    closeModal('addStudentModal');
                    studentsCache = null; // Clear cache
                    loadStudentsTable(); // Refresh table
                    loadStatistics(); // Refresh stats
                } else {
                    alert('Error creating student: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error saving student:', error);
                alert('Error saving student. Please try again.');
            }
        }

        async function saveStudentInline() {
            const form = document.getElementById('addStudentInlineForm');
            buildMemberNameFromForm(form);
            const formData = new FormData(form);
            const studentId = parseInt(form.dataset.studentId || '0', 10);
            const isUpdate = Number.isInteger(studentId) && studentId > 0;
            
            formData.append('action', isUpdate ? 'update_student' : 'add_student');
            if (isUpdate) {
                formData.append('StudentID', studentId);
            }
            formData.append('csrf_token', csrfToken);

            try {
                const response = await fetch('api/members.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    if (isUpdate) {
                        alert('Student updated successfully.');
                    } else {
                        alert(`Student created successfully!\nMember No: ${result.memberNo}\nStudent ID: ${result.studentId}\nQR Code generated and ready for printing.`);
                    }

                    resetStudentInlineForm();

                    studentsCache = null; // Clear cache
                    loadStudentsTable(); // Refresh table
                    loadStatistics(); // Refresh stats
                } else {
                    alert('Error creating student: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error saving student:', error);
                alert('Error saving student. Please try again.');
            }
        }

        // Student actions
        async function viewStudent(studentId) {
            try {
                const response = await fetch(`api/members.php?action=get_student&studentId=${studentId}`);
                const result = await response.json();

                if (result.success) {
                    const student = result.data;
                    alert(`Student Details:\n\nName: ${student.FirstName} ${student.MiddleName || ''} ${student.Surname || ''}\nPRN: ${student.PRN}\nBranch: ${student.Branch}\nMobile: ${student.Mobile}\nEmail: ${student.Email}\nStatus: ${student.Status}`);
                } else {
                    alert('Error loading student details: ' + result.message);
                }
            } catch (error) {
                console.error('Error viewing student:', error);
                alert('Error loading student details. Please try again.');
            }
        }

        async function editStudent(studentId) {
            try {
                const response = await fetch(`api/members.php?action=get_student&studentId=${studentId}`);
                const result = await response.json();

                if (result.success) {
                    const student = result.data;
                    // Populate the inline form with student data
                    document.getElementById('studentSurnameInline').value = student.Surname || '';
                    document.getElementById('studentMiddleNameInline').value = student.MiddleName || '';
                    document.getElementById('studentFirstNameInline').value = student.FirstName || '';
                    document.getElementById('studentPRNInline').value = student.PRN || '';
                    document.getElementById('studentBranchInline').value = student.Branch || '';
                    document.getElementById('studentCourseNameInline').value = student.CourseName || '';
                    document.getElementById('studentGenderInline').value = student.Gender || '';
                    document.getElementById('studentDOBInline').value = student.DOB || '';
                    document.getElementById('studentBloodGroupInline').value = student.BloodGroup || '';
                    document.getElementById('studentMobileInline').value = student.Mobile || '';
                    document.getElementById('studentEmailInline').value = student.Email || '';
                    document.getElementById('studentAddressInline').value = student.Address || '';
                    document.getElementById('validTillInline').value = student.ValidTill || '';
                    document.getElementById('studentCardColourInline').value = student.CardColour || '';
                    document.getElementById('memberNameInline').value = student.MemberName || '';
                    document.getElementById('memberGroupInline').value = student.Group || 'Student';
                    document.getElementById('memberPhoneInline').value = student.Phone || student.Mobile || '';
                    document.getElementById('memberEmailInline').value = student.Email || '';
                    document.getElementById('memberDesignationInline').value = student.Designation || '';
                    buildMemberNameFromForm(document.getElementById('addStudentInlineForm'));
                    
                    // Store student ID for update
                    document.getElementById('addStudentInlineForm').dataset.studentId = studentId;

                    const submitBtn = document.getElementById('studentInlineSubmitBtn');
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Student';
                    submitBtn.classList.remove('btn-success');
                    submitBtn.classList.add('btn-warning');
                    
                    // Scroll to form
                    document.getElementById('addStudentInlineForm').scrollIntoView({ behavior: 'smooth' });
                    
                    alert('Student data loaded. Update the fields and click "Update Student".');
                } else {
                    alert('Error loading student details: ' + result.message);
                }
            } catch (error) {
                console.error('Error editing student:', error);
                alert('Error loading student details. Please try again.');
            }
        }

        async function deleteStudent(studentId) {
            if (!confirm(`Are you sure you want to delete Student ID: ${studentId}?\n\nThis will also delete the associated member record. This action cannot be undone.`)) {
                return;
            }

            try {
                const response = await fetch('api/members.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'delete_student',
                        studentId: studentId,
                        csrf_token: csrfToken
                    })
                });

                const result = await response.json();

                if (result.success) {
                    alert('Student deleted successfully!');
                    studentsCache = null; // Clear cache
                    loadStudentsTable();
                    loadStatistics(); // Refresh stats
                } else {
                    alert('Error deleting student: ' + result.message);
                }
            } catch (error) {
                console.error('Error deleting student:', error);
                alert('Error deleting student. Please try again.');
            }
        }

        function generateReports() {
            showTab('reports');
        }

        // Membership management functions
        async function renewMemberships() {
            if (!confirm('Renew memberships for all expired and expiring members by 12 months?')) {
                return;
            }

            try {
                const response = await fetch('api/members.php?action=list_students');
                const result = await response.json();
                if (!result.success) {
                    throw new Error(result.message || 'Failed to load membership data');
                }

                const today = new Date();
                const thirtyDays = new Date(today.getTime() + 30 * 24 * 60 * 60 * 1000);
                const targetIds = (result.data || [])
                    .filter(s => {
                        const validTill = new Date(s.ValidTill);
                        return !Number.isNaN(validTill.getTime()) && validTill <= thirtyDays;
                    })
                    .map(s => Number(s.StudentID))
                    .filter(Boolean);

                if (!targetIds.length) {
                    alert('No expired or expiring memberships found to renew.');
                    return;
                }

                const updateResponse = await fetch('api/members.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'bulk_extend_membership',
                        studentIds: targetIds,
                        months: 12,
                        csrf_token: csrfToken
                    })
                });
                const updateResult = await updateResponse.json();
                if (!updateResult.success) {
                    throw new Error(updateResult.message || 'Renewal failed');
                }

                alert(`Membership renewed for ${updateResult.updated || targetIds.length} student(s).`);
                studentsCache = null;
                loadStudentsTable();
                loadStatistics();
                loadMembershipSummary();
            } catch (error) {
                console.error('Error renewing memberships:', error);
                alert('Failed to renew memberships: ' + error.message);
            }
        }

        async function viewExpiredMembers() {
            try {
                const response = await fetch('api/members.php?action=list_students');
                const result = await response.json();
                
                if (result.success) {
                    const students = result.data || [];
                    const today = new Date();
                    const expired = students.filter(s => new Date(s.ValidTill) <= today);
                    
                    if (expired.length === 0) {
                        alert('No expired memberships found. All members are active!');
                    } else {
                        alert(`Found ${expired.length} expired memberships. Showing filtered list.`);
                        showTab('students');
                        displayStudentsTable(expired);
                    }
                }
            } catch (error) {
                console.error('Error loading expired members:', error);
                alert('Error loading expired members data.');
            }
        }

        async function viewExpiringSoon() {
            try {
                const response = await fetch('api/members.php?action=list_students');
                const result = await response.json();
                
                if (result.success) {
                    const students = result.data || [];
                    const today = new Date();
                    const thirtyDaysFromNow = new Date(today.getTime() + 30 * 24 * 60 * 60 * 1000);
                    
                    const expiringSoon = students.filter(s => {
                        const validTill = new Date(s.ValidTill);
                        return s.Status === 'Active' && validTill > today && validTill <= thirtyDaysFromNow;
                    });
                    
                    if (expiringSoon.length === 0) {
                        alert('No memberships expiring in the next 30 days.');
                    } else {
                        alert(`Found ${expiringSoon.length} memberships expiring in next 30 days. Showing filtered list.`);
                        showTab('students');
                        displayStudentsTable(expiringSoon);
                    }
                }
            } catch (error) {
                console.error('Error loading expiring members:', error);
                alert('Error loading membership data.');
            }
        }

        async function viewMembershipStats() {
            try {
                const response = await fetch('api/members.php?action=list_students');
                const result = await response.json();
                if (!result.success) {
                    throw new Error(result.message || 'Failed to load data');
                }

                const students = result.data || [];
                const today = new Date();
                const active = students.filter(s => s.Status === 'Active').length;
                const expired = students.filter(s => new Date(s.ValidTill) <= today).length;
                const issued = students.filter(s => Number(s.BooksIssued || 0) > 0).length;

                alert(
                    `Membership Statistics\n\n` +
                    `Total Students: ${students.length}\n` +
                    `Active Members: ${active}\n` +
                    `Expired Memberships: ${expired}\n` +
                    `Students with Issued Books: ${issued}`
                );
            } catch (error) {
                console.error('Error loading membership stats:', error);
                alert('Could not load membership statistics.');
            }
        }

        function viewActiveMembers() {
            showTab('students');
            document.getElementById('searchStatus').value = 'Active';
            searchStudents();
        }

        // Verification functions
        async function generateQRCodes() {
            if (!confirm('Generate QR codes for all students without one?\nThey will be saved to the database.')) return;
            const btn = event && event.target ? event.target.closest('[onclick]') : null;
            const orig = btn ? btn.innerHTML : '';
            if (btn) btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            try {
                const res  = await fetch('api/qr-generator.php?type=member-all');
                const data = await res.json();
                if (data.success) {
                    alert('✅ ' + data.message + '\n' + (data.count || 0) + ' QR codes generated and saved.\n\nOpen QR Generator to download or print them.');
                    if (confirm('Open QR Generator to download / print the sheet?')) {
                        window.open('qr-generator.php', '_blank');
                    }
                } else {
                    alert('❌ Error: ' + data.message);
                }
            } catch (e) {
                alert('❌ Network error: ' + e.message);
            } finally {
                if (btn) btn.innerHTML = orig;
            }
        }

        function generateDigitalIDs() {
            if (confirm('Generate printable digital ID cards for currently visible students?')) {
                showTab('students');
                window.print();
            }
        }

        function verifyStudent() {
            const prn = prompt('Enter student PRN to verify:');
            if (prn) {
                verifyQRCode(prn);
            }
        }

        async function verifyQRCode(code) {
            const qrCode = code || document.getElementById('qrCodeInput').value.trim();
            
            if (!qrCode) {
                alert('Please enter a PRN or QR code to verify.');
                return;
            }
            
            const resultDiv = document.getElementById('qrVerificationResult');
            resultDiv.innerHTML = '<p style="color: #6c757d;">Verifying...</p>';
            
            try {
                const response = await fetch(`api/members.php?action=list_students&prn=${qrCode}`);
                const result = await response.json();
                
                if (result.success && result.data.length > 0) {
                    const student = result.data[0];
                    const validTill = new Date(student.ValidTill);
                    const isValid = validTill > new Date() && student.Status === 'Active';
                    
                    resultDiv.innerHTML = `
                        <div style="padding: 15px; border-radius: 6px; background: ${isValid ? '#d4edda' : '#f8d7da'}; border: 1px solid ${isValid ? '#c3e6cb' : '#f5c6cb'};">
                            <h4 style="color: ${isValid ? '#155724' : '#721c24'}; margin-bottom: 10px;">
                                <i class="fas fa-${isValid ? 'check-circle' : 'times-circle'}"></i>
                                ${isValid ? 'Valid Student' : 'Invalid/Expired'}
                            </h4>
                            <p style="margin: 5px 0;"><strong>Name:</strong> ${student.FirstName} ${student.MiddleName || ''} ${student.Surname || ''}</p>
                            <p style="margin: 5px 0;"><strong>PRN:</strong> ${student.PRN}</p>
                            <p style="margin: 5px 0;"><strong>Branch:</strong> ${student.Branch}</p>
                            <p style="margin: 5px 0;"><strong>Valid Till:</strong> ${new Date(student.ValidTill).toLocaleDateString()}</p>
                            <p style="margin: 5px 0;"><strong>Books Issued:</strong> ${student.BooksIssued || 0}</p>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div style="padding: 15px; border-radius: 6px; background: #f8d7da; border: 1px solid #f5c6cb;">
                            <p style="color: #721c24; margin: 0;">
                                <i class="fas fa-exclamation-triangle"></i>
                                Student not found. Please check the PRN/QR code.
                            </p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error verifying student:', error);
                resultDiv.innerHTML = `
                    <div style="padding: 15px; border-radius: 6px; background: #f8d7da; border: 1px solid #f5c6cb;">
                        <p style="color: #721c24; margin: 0;">Error verifying student. Please try again.</p>
                    </div>
                `;
            }
        }

        function openQRScanner() {
            const input = document.getElementById('qrCodeInput');
            if (input) {
                input.focus();
                input.select();
            }
            alert('Camera scanner is not configured in this deployment. Enter PRN/QR text in the input field for verification.');
        }

        async function bulkQRGeneration() {
            const selectedStudents = Array.from(document.querySelectorAll('.student-checkbox:checked'))
                .map(cb => cb.value);

            if (selectedStudents.length === 0) {
                alert('Please select students from the Students tab first, then return here to generate QR codes.');
                showTab('students');
                return;
            }

            if (!confirm(`Generate & save QR codes for ${selectedStudents.length} selected student(s)?`)) return;

            const btn = event && event.target ? event.target.closest('[onclick]') : null;
            const orig = btn ? btn.innerHTML : '';
            if (btn) btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';

            try {
                const res  = await fetch('api/qr-generator.php?type=bulk-students', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({studentIds: selectedStudents})
                });
                const data = await res.json();
                if (data.success) {
                    alert('✅ ' + data.message + '\n' + (data.count || 0) + ' QR codes generated and saved.');
                    if (confirm('Open QR Generator to download / print the sheet?')) {
                        window.open('qr-generator.php', '_blank');
                    }
                } else {
                    alert('❌ Error: ' + data.message);
                }
            } catch (e) {
                alert('❌ Network error: ' + e.message);
            } finally {
                if (btn) btn.innerHTML = orig;
            }
        }

        // Report generation functions
        async function generateReport(reportType) {
            try {
                const response = await fetch('api/members.php?action=list_students');
                const result = await response.json();
                
                if (!result.success) {
                    throw new Error('Failed to fetch student data');
                }
                
                const students = result.data || [];
                let reportData = [];
                let reportTitle = '';
                
                switch(reportType) {
                    case 'all-students':
                        reportData = students;
                        reportTitle = 'All Students Report';
                        break;
                    case 'branch-wise':
                        reportTitle = 'Branch-wise Report';
                        const branch = prompt('Enter branch name (e.g., Computer, Mechanical, Civil):');
                        if (branch) {
                            reportData = students.filter(s => s.Branch && s.Branch.toLowerCase().includes(branch.toLowerCase()));
                        }
                        break;
                    case 'books-issued':
                        reportData = students.filter(s => s.BooksIssued > 0);
                        reportTitle = 'Students with Books Issued';
                        break;
                    case 'expired-members':
                        const today = new Date();
                        reportData = students.filter(s => new Date(s.ValidTill) <= today);
                        reportTitle = 'Expired Memberships Report';
                        break;
                    case 'course-wise':
                        const course = prompt('Enter course name (e.g., B.Tech, M.Tech, Diploma):');
                        if (course) {
                            reportData = students.filter(s => s.CourseName && s.CourseName.toLowerCase().includes(course.toLowerCase()));
                        }
                        reportTitle = `${course} Students Report`;
                        break;
                    case 'custom':
                        {
                            const branchFilter = (prompt('Custom report: branch contains (leave blank for all):', '') || '').trim().toLowerCase();
                            const statusFilter = (prompt('Custom report: status (Active/Inactive/Suspended, leave blank for all):', '') || '').trim();
                            const withBooksOnly = confirm('Custom report: include only students with issued books?');

                            reportData = students.filter(s => {
                                const branchOk = !branchFilter || (s.Branch || '').toLowerCase().includes(branchFilter);
                                const statusOk = !statusFilter || (s.Status || '') === statusFilter;
                                const booksOk = !withBooksOnly || Number(s.BooksIssued || 0) > 0;
                                return branchOk && statusOk && booksOk;
                            });
                            reportTitle = 'Custom Students Report';
                        }
                        break;
                }
                
                if (reportData.length === 0) {
                    alert('No data found for the selected report criteria.');
                    return;
                }

                currentStudentReportData = reportData;
                currentStudentReportTitle = reportTitle;
                alert(`${reportTitle}\n\nTotal Records: ${reportData.length}\n\nUse Export or Print buttons below to generate output.`);
                
            } catch (error) {
                console.error('Error generating report:', error);
                alert('Error generating report. Please try again.');
            }
        }

        function exportReport(format) {
            if (!currentStudentReportData.length) {
                alert('Generate a report first, then export it.');
                return;
            }

            const normalizedRows = currentStudentReportData.map(s => ({
                StudentID: s.StudentID,
                MemberNo: s.MemberNo,
                Name: s.MemberName || [s.FirstName, s.MiddleName, s.Surname].filter(Boolean).join(' '),
                PRN: s.PRN,
                Branch: s.Branch,
                CourseName: s.CourseName,
                Mobile: s.Mobile,
                Email: s.Email || s.MemberEmail,
                Status: s.Status,
                BooksIssued: s.BooksIssued,
                ValidTill: s.ValidTill
            }));

            const extension = format === 'excel' ? 'csv' : format;
            downloadStudentCsv(normalizedRows, `${currentStudentReportTitle.replace(/\s+/g, '-').toLowerCase()}.${extension}`);
        }

        function printReport() {
            if (!currentStudentReportData.length) {
                alert('Generate a report first, then print it.');
                return;
            }

            const printWindow = window.open('', '_blank');
            const rows = currentStudentReportData.map(s => `
                <tr>
                    <td>${s.MemberNo || ''}</td>
                    <td>${s.MemberName || [s.FirstName, s.MiddleName, s.Surname].filter(Boolean).join(' ')}</td>
                    <td>${s.PRN || ''}</td>
                    <td>${s.Branch || ''}</td>
                    <td>${s.Status || ''}</td>
                    <td>${s.ValidTill ? new Date(s.ValidTill).toLocaleDateString('en-IN') : ''}</td>
                </tr>
            `).join('');
            printWindow.document.write(`
                <html><head><title>${currentStudentReportTitle}</title></head>
                <body>
                    <h2>${currentStudentReportTitle}</h2>
                    <table border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; width: 100%;">
                        <thead>
                            <tr><th>Member No</th><th>Name</th><th>PRN</th><th>Branch</th><th>Status</th><th>Valid Till</th></tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </body></html>
            `);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
        }

        function bulkOperations() {
            const modal = document.getElementById('bulkOperationsModal');
            if (modal) {
                modal.classList.add('show');
            } else {
                console.error('Bulk Operations Modal not found');
            }
        }

        function closeBulkModal() {
            const modal = document.getElementById('bulkOperationsModal');
            if (modal) {
                modal.classList.remove('show');
            }
            // Reset selections
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => cb.checked = false);
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

        async function performBulkAction(action) {
            const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
            const selectedIds = Array.from(checkedBoxes).map(cb => Number(cb.value));

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
                    confirmMessage = `Extend membership validity for ${selectedIds.length} selected student(s) by 1 year?`;
                    break;
                case 'regenerate-qr':
                    confirmMessage = `Regenerate QR codes for ${selectedIds.length} selected student(s)?`;
                    break;
                case 'export':
                    confirmMessage = `Export data for ${selectedIds.length} selected student(s) to Excel?`;
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

            if (!confirm(confirmMessage)) {
                return;
            }

            try {
                let successMessage = '';
                
                switch (action) {
                    case 'activate':
                    case 'deactivate':
                    case 'suspend':
                        {
                            const statusMap = {
                                activate: 'Active',
                                deactivate: 'Inactive',
                                suspend: 'Suspended'
                            };
                            const res = await fetch('api/members.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    action: 'bulk_student_status',
                                    studentIds: selectedIds,
                                    status: statusMap[action],
                                    csrf_token: csrfToken
                                })
                            });
                            const data = await res.json();
                            if (!data.success) {
                                throw new Error(data.message || 'Status update failed');
                            }
                            successMessage = `${data.updated || selectedIds.length} student(s) status updated successfully.`;
                        }
                        break;
                    case 'extend':
                        {
                            const res = await fetch('api/members.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    action: 'bulk_extend_membership',
                                    studentIds: selectedIds,
                                    months: 12,
                                    csrf_token: csrfToken
                                })
                            });
                            const data = await res.json();
                            if (!data.success) {
                                throw new Error(data.message || 'Membership extension failed');
                            }
                            successMessage = `Membership extended for ${data.updated || selectedIds.length} student(s).`;
                        }
                        break;
                    case 'regenerate-qr':
                        {
                            const res = await fetch('api/qr-generator.php?type=bulk-students', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ studentIds: selectedIds })
                            });
                            const data = await res.json();
                            if (!data.success) {
                                throw new Error(data.message || 'QR regeneration failed');
                            }
                            successMessage = `QR codes regenerated for ${data.count || selectedIds.length} student(s).`;
                        }
                        break;
                    case 'export':
                        {
                            const students = await fetchStudentsData(true);
                            const selectedRows = students.filter(s => selectedIds.includes(Number(s.StudentID))).map(s => ({
                                StudentID: s.StudentID,
                                MemberNo: s.MemberNo,
                                Name: s.MemberName || [s.FirstName, s.MiddleName, s.Surname].filter(Boolean).join(' '),
                                PRN: s.PRN,
                                Branch: s.Branch,
                                CourseName: s.CourseName,
                                Mobile: s.Mobile,
                                Email: s.Email || s.MemberEmail,
                                Status: s.Status,
                                BooksIssued: s.BooksIssued,
                                ValidTill: s.ValidTill
                            }));
                            downloadStudentCsv(selectedRows, 'selected-students.csv');
                            successMessage = `Export completed for ${selectedRows.length} student(s).`;
                        }
                        break;
                    case 'send-notification':
                        successMessage = `Notification queue prepared for ${selectedIds.length} student(s).`;
                        break;
                    case 'send-reminder':
                        successMessage = `Due reminder queue prepared for ${selectedIds.length} student(s).`;
                        break;
                    case 'backup':
                        successMessage = `Backup marker prepared for ${selectedIds.length} student record(s).`;
                        break;
                    case 'delete':
                        {
                            let deleted = 0;
                            for (const studentId of selectedIds) {
                                const res = await fetch('api/members.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({
                                        action: 'delete_student',
                                        studentId,
                                        csrf_token: csrfToken
                                    })
                                });
                                const data = await res.json();
                                if (data.success) {
                                    deleted += 1;
                                }
                            }
                            successMessage = `${deleted} of ${selectedIds.length} selected student(s) deleted.`;
                        }
                        break;
                }

                alert(successMessage);
                closeBulkModal();
                studentsCache = null;
                loadStudentsTable(); // Refresh the table
                loadStatistics();
                loadMembershipSummary();
                
            } catch (error) {
                console.error('Error performing bulk action:', error);
                alert('Error performing bulk action: ' + error.message);
            }
        }

        // Load statistics with calculated data
        // Load statistics from database
        async function loadStatistics() {
            try {
                const response = await fetch('api/members.php?action=list_students');
                const result = await response.json();
                
                if (result.success) {
                    const students = result.data || [];
                    const totalStudents = students.length;
                    const activeMembers = students.filter(s => s.Status === 'Active').length;
                    const totalBooksIssued = students.reduce((sum, s) => sum + (parseInt(s.BooksIssued) || 0), 0);
                    
                    // Calculate expired memberships
                    const today = new Date();
                    const expired = students.filter(s => new Date(s.ValidTill) <= today).length;

                    document.getElementById('totalStudents').textContent = totalStudents.toLocaleString();
                    document.getElementById('activeMembers').textContent = activeMembers.toLocaleString();
                    document.getElementById('booksIssued').textContent = totalBooksIssued.toLocaleString();
                    document.getElementById('overdueBooks').textContent = expired.toLocaleString();
                } else {
                    // Show dash if failed
                    document.getElementById('totalStudents').textContent = '-';
                    document.getElementById('activeMembers').textContent = '-';
                    document.getElementById('booksIssued').textContent = '-';
                    document.getElementById('overdueBooks').textContent = '-';
                }
            } catch (error) {
                console.error('Error loading statistics:', error);
                // Show dash on error
                document.getElementById('totalStudents').textContent = '-';
                document.getElementById('activeMembers').textContent = '-';
                document.getElementById('booksIssued').textContent = '-';
                document.getElementById('overdueBooks').textContent = '-';
            }
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
            }
        };
        
        // Global helper to close any modal by clicking the close button
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('close') || e.target.closest('.close')) {
                const modal = e.target.closest('.modal');
                if (modal) {
                    modal.classList.remove('show');
                }
            }
        });
        
        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const openModals = document.querySelectorAll('.modal.show');
                openModals.forEach(modal => {
                    modal.classList.remove('show');
                });
            }
        });

        // Attach showTab to the global window object
        window.showTab = showTab;

        // Initialize page
        function initStudentManagement() {
            console.log('Student Management: Initializing');
            fetchCSRFToken();
            attachMemberNameAutoCompose('addStudentInlineForm');
            attachMemberNameAutoCompose('addStudentForm');
            resetStudentInlineForm();
            loadStatistics();
            loadStudentsTable();
        }
        
        // Run on DOMContentLoaded (for standalone page loads)
        document.addEventListener('DOMContentLoaded', initStudentManagement);
        
        // Also run immediately if document is already ready (for AJAX loads)
        if (document.readyState === 'loading') {
            // Document still loading, wait for DOMContentLoaded
        } else {
            // Document already loaded, init immediately
            initStudentManagement();
        }

        async function viewQRCode(studentId) {
            try {
                const response = await fetch(`api/members.php?action=get_qr_code&studentId=${studentId}`);
                const result = await response.json();

                if (result.success) {
                    const qrModal = document.getElementById('qrCodeModal');
                    const qrImage = document.getElementById('qrCodeImage');
                    const qrTitle = qrModal.querySelector('.modal-title');
                    const qrInfo = document.getElementById('qrCodeInfo');
                    
                    // Set QR code image
                    qrImage.src = `data:image/png;base64,${result.qrCode}`;
                    qrImage.alt = `QR Code for ${result.studentName}`;
                    
                    // Store data for download
                    qrImage.dataset.studentName = result.studentName;
                    qrImage.dataset.prn = result.prn;
                    qrImage.dataset.qrData = result.qrData;
                    
                    // Update modal title and info
                    qrTitle.innerHTML = `<i class="fas fa-qrcode"></i> QR Code - ${result.studentName}`;
                    qrInfo.textContent = `PRN: ${result.prn} | Member No: ${result.memberNo}`;
                    
                    // Show modal
                    qrModal.classList.add('show');
                } else {
                    alert('Error fetching QR code: ' + result.message);
                }
            } catch (error) {
                console.error('Error fetching QR code:', error);
                alert('Error fetching QR code. Please try again.');
            }
        }

        function downloadQRCode() {
            const qrImage = document.getElementById('qrCodeImage');
            const studentName = qrImage.dataset.studentName || 'Student';
            const prn = qrImage.dataset.prn || 'Unknown';
            
            // Create a temporary link to download the image
            const link = document.createElement('a');
            link.href = qrImage.src;
            link.download = `QRCode_${prn}_${studentName.replace(/\s+/g, '_')}.png`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            console.log(`QR Code downloaded for ${studentName} (${prn})`);
        }
    
