
        // Helper function to get correct API path (works both in direct access and when loaded via layout.php)
        function getApiPath(apiFile) {
            const currentPath = window.location.pathname;
            if (currentPath.includes('/admin/layout.php') || currentPath.includes('/admin/layout2.php')) {
                return '/wiet_lib/admin/' + apiFile;
            } else if (currentPath.includes('/admin/')) {
                return apiFile;
            } else {
                return '/wiet_lib/admin/' + apiFile;
            }
        }
        
        // Global variables
        const memberEntitlements = {"Standard":{"max_books":3,"issue_period":15,"fine_per_day":2},"Faculty":{"max_books":10,"issue_period":30,"fine_per_day":1},"Staff":{"max_books":5,"issue_period":20,"fine_per_day":2},"Guest":{"max_books":2,"issue_period":7,"fine_per_day":5}};
        let selectedMembers = [];
        let csrfToken = null;

        async function fetchCSRFToken() {
            try {
                const response = await fetch(getApiPath('api/members.php?action=get-csrf-token'));
                const result = await response.json();
                if (result.success) {
                    csrfToken = result.token;
                }
            } catch (error) {
                console.error('Failed to load CSRF token:', error);
            }
        }

        async function postMembersApi(payload) {
            if (!csrfToken) {
                await fetchCSRFToken();
            }

            const response = await fetch(getApiPath('api/members.php'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    ...payload,
                    csrf_token: csrfToken
                })
            });

            return response.json();
        }

        function resetMemberInlineForm() {
            const form = document.getElementById('addMemberInlineForm');
            const submitBtn = document.getElementById('memberInlineSubmitBtn');
            form.reset();
            delete form.dataset.memberNo;
            submitBtn.innerHTML = '<i class="fas fa-plus"></i> Create Member';
            submitBtn.classList.remove('btn-warning');
            submitBtn.classList.add('btn-success');
        }

        // Inline Add Member handler
        async function saveMemberInline() {
            const form = document.getElementById('addMemberInlineForm');
            const formData = new FormData(form);
            const memberNo = parseInt(form.dataset.memberNo || '0', 10);
            const isUpdate = Number.isInteger(memberNo) && memberNo > 0;
            
            const memberData = {
                action: isUpdate ? 'update' : 'add',
                ...(isUpdate ? { MemberNo: memberNo } : {}),
                MemberName: formData.get('MemberName'),
                Group: formData.get('Group'),
                Designation: formData.get('Designation'),
                Entitlement: formData.get('Entitlement'),
                Phone: formData.get('Phone'),
                Email: formData.get('Email'),
                FinePerDay: formData.get('FinePerDay'),
                AdmissionDate: formData.get('AdmissionDate'),
                ClosingDate: formData.get('ClosingDate') || null,
                Status: formData.get('Status') || 'Active'
            };

            try {
                const result = await postMembersApi(memberData);

                if (result.success) {
                    if (isUpdate) {
                        alert('Member updated successfully.');
                    } else {
                        alert(`Member created successfully!\nMember No: ${result.memberNo}`);
                    }
                    resetMemberInlineForm();
                    loadMembersTable();
                    loadStatistics();
                } else {
                    alert('Error saving member: ' + (result.message || 'Unknown error'));
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
            const tabButton = Array.from(document.querySelectorAll('.tab-btn')).find(btn => {
                const clickHandler = btn.getAttribute('onclick') || '';
                return clickHandler.includes(`'${tabName}'`);
            });
            if (tabButton) {
                tabButton.classList.add('active');
            }

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
                <div style="margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
                    <label style="display: flex; align-items: center; gap: 8px; color: #263c79; font-weight: 600;">
                        <input type="checkbox" id="selectAllMembers" onchange="selectAllMembers()">
                        Select All Members
                    </label>
                </div>
                <table class="members-table">
                    <thead>
                        <tr>
                            <th>Select</th>
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
                        <td colspan="10" style="text-align: center; padding: 40px; color: #6c757d;">
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
                            <td>
                                <input type="checkbox" class="member-checkbox" value="${member.MemberNo}">
                            </td>
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
                                <span style="color: #263c79; font-weight: 500;">Gé¦${details.fine_per_day}</span>
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
                    document.getElementById('defaultFine').textContent = 'Gé¦' + memberEntitlements[entitlement].fine_per_day;

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
                const result = await postMembersApi(memberData);

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
                        circulationInfo = '\n\nActive Book Issues:\n';
                        circulations.forEach(circ => {
                            circulationInfo += `- ${circ.Title} (Due: ${new Date(circ.DueDate).toLocaleDateString()})\n`;
                        });
                    }
                    
                    alert(`Member Details:\n\nMember No: ${member.MemberNo}\nName: ${member.MemberName}\nGroup: ${member.Group}\nDesignation: ${member.Designation || 'N/A'}\nPhone: ${member.Phone || 'N/A'}\nEmail: ${member.Email || 'N/A'}\nBooks Issued: ${member.BooksIssued}\nStatus: ${member.Status}\nAdmission Date: ${new Date(member.AdmissionDate).toLocaleDateString()}${circulationInfo}`);
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

                    const submitBtn = document.getElementById('memberInlineSubmitBtn');
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Update Member';
                    submitBtn.classList.remove('btn-success');
                    submitBtn.classList.add('btn-warning');
                    
                    // Scroll to form
                    document.getElementById('addMemberInlineForm').scrollIntoView({ behavior: 'smooth' });
                    alert('Member data loaded. Update the fields and click "Update Member".');
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
                const result = await postMembersApi({
                    action: 'delete',
                    MemberNo: memberNo
                });

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
                .map(cb => Number(cb.value));
            
            if (selectedMembers.length === 0) {
                alert('Please select at least one member to perform bulk operations.');
                return;
            }
            
            const action = prompt(`Selected ${selectedMembers.length} member(s).\n\nChoose action:\n1. Generate Cards\n2. Send Notifications\n3. Change Status\n4. Export to Excel\n\nEnter option number (1-4):`);
            
            switch(action) {
                case '1':
                    await generateSelectedCards();
                    break;
                case '2':
                    alert(`Notification list prepared for ${selectedMembers.length} selected member(s).`);
                    break;
                case '3':
                    const newStatusInput = prompt('Enter new status (Active/Inactive/Suspended):', 'Active');
                    const newStatus = (newStatusInput || '').trim();
                    if (['Active', 'Inactive', 'Suspended'].includes(newStatus)) {
                        const updateResult = await postMembersApi({
                            action: 'bulk_member_status',
                            memberNos: selectedMembers,
                            status: newStatus
                        });
                        if (!updateResult.success) {
                            throw new Error(updateResult.message || 'Failed to update status');
                        }
                        alert(`Status updated to ${newStatus} for ${updateResult.updated || selectedMembers.length} member(s).`);
                        loadMembersTable();
                        loadStatistics();
                    } else if (newStatusInput) {
                        alert('Invalid status. Use Active, Inactive, or Suspended.');
                    }
                    break;
                case '4':
                    {
                        const response = await fetch(getApiPath('api/members.php?action=list'));
                        const result = await response.json();
                        if (!result.success) {
                            throw new Error(result.message || 'Unable to fetch members');
                        }
                        const exportRows = (result.data || []).filter(m => selectedMembers.includes(Number(m.MemberNo)));
                        downloadMembersCsv(exportRows, 'selected-members.csv');
                    }
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
            const newFine = prompt(`Current Fine Per Day: Gé¦${details.fine_per_day}\nEnter new value:`, details.fine_per_day);
            
            if (newMaxBooks && newPeriod && newFine) {
                memberEntitlements[entitlement] = {
                    max_books: Number(newMaxBooks),
                    issue_period: Number(newPeriod),
                    fine_per_day: Number(newFine)
                };
                alert('Entitlement values updated for this session.');
                loadEntitlementsContent();
            }
        }

        function buildPrintableCards(members) {
            return members.map(member => `
                <div style="border: 1px solid #d9d9d9; border-radius: 8px; padding: 12px; width: 290px; margin: 8px; display: inline-block; vertical-align: top;">
                    <h4 style="margin: 0 0 8px 0; color: #263c79;">${member.MemberName}</h4>
                    <div><strong>Member No:</strong> ${member.MemberNo}</div>
                    <div><strong>Group:</strong> ${member.Group || '-'}</div>
                    <div><strong>Status:</strong> ${member.Status || '-'}</div>
                    <div><strong>Phone:</strong> ${member.Phone || '-'}</div>
                </div>
            `).join('');
        }

        function downloadMembersCsv(members, fileName = 'members-report.csv') {
            const headers = ['MemberNo', 'MemberName', 'Group', 'Designation', 'Phone', 'Email', 'Status', 'BooksIssued', 'AdmissionDate'];
            const rows = members.map(m => headers.map(h => {
                const value = m[h] ?? '';
                return `"${String(value).replace(/"/g, '""')}"`;
            }).join(','));

            const csv = [headers.join(','), ...rows].join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        async function generateAllCards() {
            if (!confirm('Generate ID cards for ALL active members?')) {
                return;
            }

            const response = await fetch(getApiPath('api/members.php?action=list&status=Active'));
            const result = await response.json();
            if (!result.success || !result.data?.length) {
                alert('No active members found.');
                return;
            }

            const printWindow = window.open('', '_blank');
            printWindow.document.write(`<html><head><title>Member Cards</title></head><body>${buildPrintableCards(result.data)}</body></html>`);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
        }

        async function generateSelectedCards() {
            const selectedMembers = Array.from(document.querySelectorAll('.member-checkbox:checked'))
                .map(cb => Number(cb.value));
            
            if (selectedMembers.length === 0) {
                alert('Please select members from the "All Members" tab first.');
                showTab('all-members');
                return;
            }

            const response = await fetch(getApiPath('api/members.php?action=list'));
            const result = await response.json();
            if (!result.success) {
                alert(result.message || 'Could not load members');
                return;
            }

            const members = (result.data || []).filter(m => selectedMembers.includes(Number(m.MemberNo)));
            if (!members.length) {
                alert('No matching members found for selected rows.');
                return;
            }

            const printWindow = window.open('', '_blank');
            printWindow.document.write(`<html><head><title>Selected Member Cards</title></head><body>${buildPrintableCards(members)}</body></html>`);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
        }

        function printExistingCards() {
            generateSelectedCards();
        }

        function cardTemplateSettings() {
            alert('Card template settings are currently using default library branding and dimensions.');
        }

        async function generateReport(reportType) {
            const response = await fetch(getApiPath('api/members.php?action=list'));
            const result = await response.json();
            if (!result.success) {
                alert(result.message || 'Unable to generate report');
                return;
            }

            const members = result.data || [];
            let reportRows = members;
            let fileName = 'member-report.csv';

            switch(reportType) {
                case 'member-summary':
                    fileName = 'member-summary.csv';
                    break;
                case 'active-members':
                    reportRows = members.filter(m => m.Status === 'Active');
                    fileName = 'active-members.csv';
                    break;
                case 'member-activity':
                    reportRows = members.filter(m => Number(m.BooksIssued || 0) > 0);
                    fileName = 'member-activity.csv';
                    break;
                case 'group-wise':
                    {
                        const grouped = members.reduce((acc, m) => {
                            const key = m.Group || 'Unknown';
                            acc[key] = (acc[key] || 0) + 1;
                            return acc;
                        }, {});
                        reportRows = Object.entries(grouped).map(([Group, Count]) => ({ Group, Count }));
                        fileName = 'group-wise-members.csv';
                    }
                    break;
                default:
                    break;
            }

            if (!reportRows.length) {
                alert('No data available for this report.');
                return;
            }

            if (reportType === 'group-wise') {
                const headers = ['Group', 'Count'];
                const rows = reportRows.map(row => `"${row.Group}","${row.Count}"`);
                const csv = [headers.join(','), ...rows].join('\n');
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                return;
            }

            downloadMembersCsv(reportRows, fileName);
        }

        // Load statistics
        async function loadStatistics() {
            // Try to load from cache first for instant display
            const cachedStats = sessionStorage.getItem('members_stats');
            if (cachedStats) {
                try {
                    const stats = JSON.parse(cachedStats);
                    const age = Date.now() - stats.timestamp;
                    
                    // If cache is less than 2 minutes old, use it immediately
                    if (age < 120000) {
                        document.getElementById('totalMembers').textContent = stats.data.totalMembers;
                        document.getElementById('activeMembers').textContent = stats.data.activeMembers;
                        document.getElementById('facultyMembers').textContent = stats.data.facultyMembers;
                        document.getElementById('staffMembers').textContent = stats.data.staffMembers;
                        document.getElementById('studentMembers').textContent = stats.data.studentMembers;
                        document.getElementById('inactiveMembers').textContent = stats.data.inactiveMembers;
                    }
                } catch (e) {
                    console.warn('Failed to parse cached stats:', e);
                }
            }
            
            try {
                const response = await fetch(getApiPath('api/members.php?action=list'));
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
                    
                    // Cache the results
                    sessionStorage.setItem('members_stats', JSON.stringify({
                        data: {
                            totalMembers,
                            activeMembers,
                            facultyMembers,
                            staffMembers,
                            studentMembers,
                            inactiveMembers
                        },
                        timestamp: Date.now()
                    }));
                } else {
                    // Show 0 if failed
                    document.getElementById('totalMembers').textContent = '0';
                    document.getElementById('activeMembers').textContent = '0';
                    document.getElementById('facultyMembers').textContent = '0';
                    document.getElementById('staffMembers').textContent = '0';
                    document.getElementById('studentMembers').textContent = '0';
                    document.getElementById('inactiveMembers').textContent = '0';
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
        function initMembersPage() {
            fetchCSRFToken();
            resetMemberInlineForm();
            loadStatistics();
            loadMembersTable();
        }
        
        // Run on DOMContentLoaded OR immediately if already loaded (for AJAX)
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMembersPage);
        } else {
            initMembersPage();
        }
    
