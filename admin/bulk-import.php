<?php
require_once 'ajax-handler.php';
require_once 'session_check.php';

$admin_name = $current_admin['name'] ?? ($_SESSION['admin_name'] ?? 'Admin');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Import</title>
    <style>
        .bulk-import-root {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        .bulk-import-root * {
            box-sizing: border-box;
        }

        .bulk-import-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #cfac69;
        }

        .bulk-import-title {
            color: #263c79;
            font-size: 28px;
            font-weight: 700;
            margin: 0;
        }

        .subtitle {
            color: #6c757d;
            margin-top: 8px;
        }

        .grid {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(340px, 1fr);
            gap: 20px;
            align-items: start;
        }

        .grid > div {
            min-width: 0;
        }

        .card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            padding: 18px;
            margin-bottom: 20px;
        }

        .card h3 {
            margin: 0 0 14px 0;
            color: #263c79;
            font-size: 18px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 14px;
        }

        .form-row label {
            font-weight: 600;
            color: #343a40;
            margin-bottom: 4px;
            display: block;
        }

        .input,
        .select {
            width: 100%;
            border: 1px solid #ced4da;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 14px;
            outline: none;
            box-sizing: border-box;
        }

        .input:focus,
        .select:focus {
            border-color: #263c79;
            box-shadow: 0 0 0 3px rgba(38, 60, 121, 0.12);
        }

        .btn-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        .btn {
            border: 0;
            border-radius: 6px;
            padding: 10px 16px;
            font-weight: 600;
            cursor: pointer;
            color: #fff;
        }

        .btn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }

        .btn-primary {
            background: #263c79;
        }

        .btn-success {
            background: #2f9e44;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .hint {
            color: #6c757d;
            font-size: 13px;
            margin-top: 6px;
        }

        .required-star {
            color: #dc3545;
            font-weight: 700;
        }

        .pill {
            display: inline-block;
            font-size: 12px;
            border-radius: 999px;
            padding: 4px 10px;
            margin-right: 8px;
        }

        .pill-required {
            background: rgba(220, 53, 69, 0.12);
            color: #dc3545;
        }

        .pill-optional {
            background: rgba(38, 60, 121, 0.1);
            color: #263c79;
        }

        .table-wrap {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            min-width: 560px;
        }

        th,
        td {
            border-bottom: 1px solid #eceff2;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f8f9fb;
            color: #263c79;
            font-weight: 700;
            position: sticky;
            top: 0;
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(120px, 1fr));
            gap: 10px;
            margin-bottom: 12px;
        }

        .status-box {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            background: #fafbfd;
        }

        .status-num {
            font-size: 22px;
            font-weight: 700;
            color: #263c79;
        }

        .status-label {
            color: #6c757d;
            font-size: 12px;
        }

        .tag-ok,
        .tag-upd,
        .tag-skip,
        .tag-err {
            display: inline-block;
            border-radius: 999px;
            padding: 2px 8px;
            font-size: 12px;
            font-weight: 700;
        }

        .tag-ok {
            background: rgba(40, 167, 69, 0.14);
            color: #1f7a34;
        }

        .tag-upd {
            background: rgba(23, 162, 184, 0.18);
            color: #0b6f7d;
        }

        .tag-skip {
            background: rgba(255, 193, 7, 0.18);
            color: #8a6d00;
        }

        .tag-err {
            background: rgba(220, 53, 69, 0.14);
            color: #b02a37;
        }

        .muted {
            color: #6c757d;
            font-size: 12px;
        }

        @media (max-width: 1200px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 980px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .status-grid {
                grid-template-columns: repeat(2, minmax(120px, 1fr));
            }
        }
    </style>
</head>

<body>
    <div class="bulk-import-root">
    <div class="bulk-import-header">
        <h1 class="bulk-import-title">Bulk Import Management</h1>
        <div class="subtitle">Upload CSV/XLSX, map file columns to DB fields, import valid rows, skip duplicates, and review detailed results.</div>
    </div>

    <div class="grid">
        <div>
            <div class="card">
                <h3>1) Upload and Preview</h3>
                <div class="form-row">
                    <div>
                        <label for="importType">Import Type</label>
                        <select id="importType" class="select">
                            <option value="">Select import type</option>
                        </select>
                    </div>
                    <div>
                        <label for="fileInput">File (.csv or .xlsx)</label>
                        <input id="fileInput" type="file" class="input" accept=".csv,.xlsx">
                        <div class="hint">Signed in as <?php echo htmlspecialchars($admin_name); ?></div>
                    </div>
                </div>

                <div id="fileMeta" class="muted"></div>

                <div class="btn-row">
                    <button id="previewBtn" class="btn btn-primary">Preview and Load Mapping</button>
                    <button id="importBtn" class="btn btn-success" disabled>Push to Database</button>
                    <button id="resetBtn" class="btn btn-secondary">Reset</button>
                </div>
            </div>

            <div class="card">
                <h3>2) Field Mapping</h3>
                <div id="fieldLegend"></div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>DB Field</th>
                                <th>Required</th>
                                <th>Map To File Column</th>
                            </tr>
                        </thead>
                        <tbody id="mappingBody">
                            <tr>
                                <td colspan="3" class="muted">Select import type first.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h3>3) File Preview</h3>
                <div id="previewMeta" class="muted" style="margin-bottom: 10px;">No preview loaded yet.</div>
                <div class="table-wrap">
                    <table id="previewTable"></table>
                </div>
            </div>
        </div>

        <div>
            <div class="card" id="resultCard" style="display:none;">
                <h3>Import Result</h3>
                <div class="status-grid">
                    <div class="status-box">
                        <div id="sumTotal" class="status-num">0</div>
                        <div class="status-label">Rows Read</div>
                    </div>
                    <div class="status-box">
                        <div id="sumAdded" class="status-num">0</div>
                        <div class="status-label">Added</div>
                    </div>
                    <div class="status-box">
                        <div id="sumSkipped" class="status-num">0</div>
                        <div class="status-label">Skipped</div>
                    </div>
                    <div class="status-box">
                        <div id="sumErrors" class="status-num">0</div>
                        <div class="status-label">Errors</div>
                    </div>
                </div>

                <div class="table-wrap" style="margin-bottom:12px;">
                    <table>
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Row</th>
                                <th>Identifier</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody id="resultRows"></tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h3>Recent Import History</h3>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>File</th>
                                <th>Rows</th>
                                <th>Added</th>
                                <th>Skipped</th>
                                <th>Errors</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="historyBody">
                            <tr>
                                <td colspan="7" class="muted">No imports yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>

    <script>
        let importConfigs = {};
        let selectedHeaders = [];
        let selectedFile = null;
        let currentImportType = '';
        let suggestedMapping = {};

        const importTypeEl = document.getElementById('importType');
        const fileInputEl = document.getElementById('fileInput');
        const previewBtn = document.getElementById('previewBtn');
        const importBtn = document.getElementById('importBtn');
        const resetBtn = document.getElementById('resetBtn');
        const mappingBody = document.getElementById('mappingBody');
        const fieldLegend = document.getElementById('fieldLegend');
        const fileMeta = document.getElementById('fileMeta');
        const previewMeta = document.getElementById('previewMeta');
        const previewTable = document.getElementById('previewTable');
        const historyBody = document.getElementById('historyBody');

        document.addEventListener('DOMContentLoaded', () => {
            wireEvents();
            loadMetadata();
            loadHistory();
        });

        function wireEvents() {
            importTypeEl.addEventListener('change', () => {
                currentImportType = importTypeEl.value;
                selectedHeaders = [];
                suggestedMapping = {};
                renderFieldMapping();
                importBtn.disabled = true;
                previewMeta.textContent = 'No preview loaded yet.';
                previewTable.innerHTML = '';
            });

            fileInputEl.addEventListener('change', () => {
                selectedFile = fileInputEl.files[0] || null;
                if (!selectedFile) {
                    fileMeta.textContent = '';
                    return;
                }

                fileMeta.textContent = `Selected: ${selectedFile.name} (${formatSize(selectedFile.size)})`;
            });

            previewBtn.addEventListener('click', previewFile);
            importBtn.addEventListener('click', pushImport);
            resetBtn.addEventListener('click', resetAll);
        }

        async function loadMetadata() {
            try {
                const res = await fetch('api/bulk-import.php?action=metadata');
                const payload = await res.json();
                if (!payload.success) {
                    alert(payload.message || 'Failed to load import metadata.');
                    return;
                }

                importConfigs = payload.data || {};
                importTypeEl.innerHTML = '<option value="">Select import type</option>';
                Object.values(importConfigs).forEach(cfg => {
                    const option = document.createElement('option');
                    option.value = cfg.type;
                    option.textContent = `${cfg.title}`;
                    importTypeEl.appendChild(option);
                });
            } catch (error) {
                alert('Failed to load metadata: ' + error.message);
            }
        }

        async function loadHistory() {
            try {
                const res = await fetch('api/bulk-import.php?action=history');
                const payload = await res.json();

                if (!payload.success) {
                    return;
                }

                const history = payload.data || [];
                if (history.length === 0) {
                    historyBody.innerHTML = '<tr><td colspan="7" class="muted">No imports yet.</td></tr>';
                    return;
                }

                historyBody.innerHTML = history.map(item => {
                    return `
                        <tr>
                            <td>${escapeHtml(item.importType || '')}</td>
                            <td>${escapeHtml(item.fileName || '')}</td>
                            <td>${num(item.totalRows)}</td>
                            <td>${num(item.added)}</td>
                            <td>${num(item.skipped)}</td>
                            <td>${num(item.errors)}</td>
                            <td>${escapeHtml(item.importDate || '')}</td>
                        </tr>
                    `;
                }).join('');
            } catch (error) {
                console.error(error);
            }
        }

        function renderFieldMapping() {
            const cfg = importConfigs[currentImportType];
            if (!cfg) {
                mappingBody.innerHTML = '<tr><td colspan="3" class="muted">Select import type first.</td></tr>';
                fieldLegend.innerHTML = '';
                return;
            }

            const requiredCount = cfg.fields.filter(f => f.required).length;
            const optionalCount = cfg.fields.length - requiredCount;

            fieldLegend.innerHTML = `
                <div style="margin-bottom: 12px;">
                    <span class="pill pill-required">Required: ${requiredCount}</span>
                    <span class="pill pill-optional">Optional: ${optionalCount}</span>
                </div>
                <div class="hint">All database fields are listed below. Required fields must be mapped before import.</div>
            `;

            mappingBody.innerHTML = cfg.fields.map(field => {
                const selectId = `map_${safeId(field.key)}`;
                const options = ['<option value="">-- Ignore this field --</option>']
                    .concat(selectedHeaders.map(h => `<option value="${escapeAttr(h)}">${escapeHtml(h)}</option>`))
                    .join('');

                const requiredText = field.required
                    ? '<span class="required-star">Yes *</span>'
                    : 'No';

                return `
                    <tr>
                        <td>
                            <strong>${escapeHtml(field.label)}</strong>
                            <div class="muted">Table: ${escapeHtml(field.table)} | Column: ${escapeHtml(field.column)}</div>
                        </td>
                        <td>${requiredText}</td>
                        <td>
                            <select class="select map-select" data-key="${escapeAttr(field.key)}" id="${selectId}">
                                ${options}
                            </select>
                        </td>
                    </tr>
                `;
            }).join('');

            applySuggestedMapping();
        }

        function applySuggestedMapping() {
            Object.entries(suggestedMapping).forEach(([fieldKey, header]) => {
                if (!header) {
                    return;
                }
                const select = document.querySelector(`.map-select[data-key="${cssEscape(fieldKey)}"]`);
                if (select) {
                    const hasOption = Array.from(select.options).some(opt => opt.value === header);
                    if (hasOption) {
                        select.value = header;
                    }
                }
            });
        }

        async function previewFile() {
            if (!currentImportType) {
                alert('Please select import type first.');
                return;
            }
            if (!selectedFile) {
                alert('Please choose a file first.');
                return;
            }

            const ext = selectedFile.name.split('.').pop().toLowerCase();
            if (!['csv', 'xlsx'].includes(ext)) {
                alert('Only .csv and .xlsx files are supported.');
                return;
            }

            previewBtn.disabled = true;
            previewBtn.textContent = 'Previewing...';

            try {
                const fd = new FormData();
                fd.append('import_type', currentImportType);
                fd.append('file', selectedFile);

                const res = await fetch('api/bulk-import.php?action=preview', {
                    method: 'POST',
                    body: fd
                });
                const payload = await res.json();

                if (!payload.success) {
                    alert(payload.message || 'Preview failed.');
                    return;
                }

                const data = payload.data;
                selectedHeaders = data.headers || [];
                suggestedMapping = data.suggested_mapping || {};

                renderFieldMapping();
                renderPreviewTable(data.headers || [], data.sample_rows || []);
                previewMeta.textContent = `Rows detected: ${num(data.total_rows)} | Showing sample: ${Math.min((data.sample_rows || []).length, 5)}`;

                importBtn.disabled = false;
            } catch (error) {
                alert('Preview error: ' + error.message);
            } finally {
                previewBtn.disabled = false;
                previewBtn.textContent = 'Preview and Load Mapping';
            }
        }

        function renderPreviewTable(headers, rows) {
            if (!headers.length) {
                previewTable.innerHTML = '';
                return;
            }

            const thead = `
                <thead>
                    <tr>${headers.map(h => `<th>${escapeHtml(h)}</th>`).join('')}</tr>
                </thead>
            `;

            const bodyRows = rows.length
                ? rows.map(r => `<tr>${headers.map(h => `<td>${escapeHtml(r[h] ?? '')}</td>`).join('')}</tr>`).join('')
                : `<tr><td colspan="${headers.length}" class="muted">No data rows found.</td></tr>`;

            const tbody = `<tbody>${bodyRows}</tbody>`;
            previewTable.innerHTML = thead + tbody;
        }

        function collectMapping() {
            const mapping = {};
            document.querySelectorAll('.map-select').forEach(select => {
                mapping[select.dataset.key] = select.value || '';
            });
            return mapping;
        }

        function validateRequiredMapping(mapping) {
            const cfg = importConfigs[currentImportType];
            if (!cfg) {
                return ['Invalid import type.'];
            }

            const missing = cfg.fields
                .filter(f => f.required)
                .filter(f => !(mapping[f.key] || '').trim())
                .map(f => f.label);

            return missing;
        }

        async function pushImport() {
            if (!currentImportType || !selectedFile) {
                alert('Please select import type and file first.');
                return;
            }

            const mapping = collectMapping();
            const missing = validateRequiredMapping(mapping);
            if (missing.length) {
                alert('Please map required fields:\n\n' + missing.join('\n'));
                return;
            }

            importBtn.disabled = true;
            importBtn.textContent = 'Importing...';

            try {
                const fd = new FormData();
                fd.append('import_type', currentImportType);
                fd.append('file', selectedFile);
                fd.append('mapping', JSON.stringify(mapping));

                const res = await fetch('api/bulk-import.php?action=import', {
                    method: 'POST',
                    body: fd
                });
                const payload = await res.json();

                if (!payload.success) {
                    const message = payload.message || 'Import failed.';
                    alert(message);
                    return;
                }

                renderResult(payload.data);
                loadHistory();
            } catch (error) {
                alert('Import failed: ' + error.message);
            } finally {
                importBtn.disabled = false;
                importBtn.textContent = 'Push to Database';
            }
        }

        function renderResult(result) {
            const summary = result.summary || {};
            document.getElementById('sumTotal').textContent = num(summary.total_rows || 0);
            document.getElementById('sumAdded').textContent = num(summary.added_count || 0);
            document.getElementById('sumSkipped').textContent = num(summary.skipped_count || 0);
            document.getElementById('sumErrors').textContent = num(summary.error_count || 0);

            const rows = [];
            (result.added || []).forEach(item => {
                const isUpdated = String(item.action || '').toLowerCase() === 'updated';
                rows.push({
                    status: isUpdated ? '<span class="tag-upd">Updated</span>' : '<span class="tag-ok">Added</span>',
                    row: item.row,
                    identifier: item.identifier || '',
                    detail: isUpdated ? 'Existing member updated' : 'Inserted successfully'
                });
            });
            (result.skipped || []).forEach(item => {
                rows.push({
                    status: '<span class="tag-skip">Skipped</span>',
                    row: item.row,
                    identifier: item.identifier || '',
                    detail: item.reason || 'Skipped'
                });
            });
            (result.errors || []).forEach(item => {
                rows.push({
                    status: '<span class="tag-err">Error</span>',
                    row: item.row,
                    identifier: item.identifier || '',
                    detail: item.reason || 'Error'
                });
            });

            rows.sort((a, b) => Number(a.row) - Number(b.row));

            const resultRows = document.getElementById('resultRows');
            if (!rows.length) {
                resultRows.innerHTML = '<tr><td colspan="4" class="muted">No row-level details available.</td></tr>';
            } else {
                resultRows.innerHTML = rows.map(r => `
                    <tr>
                        <td>${r.status}</td>
                        <td>${escapeHtml(String(r.row || ''))}</td>
                        <td>${escapeHtml(String(r.identifier || ''))}</td>
                        <td>${escapeHtml(String(r.detail || ''))}</td>
                    </tr>
                `).join('');
            }

            const resultCard = document.getElementById('resultCard');
            resultCard.style.display = 'block';
            resultCard.scrollIntoView({ behavior: 'smooth', block: 'start', inline: 'nearest' });

            const mainContentHost = document.getElementById('main-content');
            if (mainContentHost) {
                mainContentHost.scrollLeft = 0;
            }
        }

        function resetAll() {
            selectedFile = null;
            selectedHeaders = [];
            suggestedMapping = {};
            importBtn.disabled = true;

            fileInputEl.value = '';
            fileMeta.textContent = '';
            previewMeta.textContent = 'No preview loaded yet.';
            previewTable.innerHTML = '';

            document.getElementById('resultCard').style.display = 'none';
            renderFieldMapping();
        }

        function safeId(value) {
            return value.replace(/[^a-zA-Z0-9_-]/g, '_');
        }

        function cssEscape(value) {
            if (window.CSS && window.CSS.escape) {
                return window.CSS.escape(value);
            }
            return value.replace(/([ #;?%&,.+*~\':"!^$\[\]()=>|\/])/g, '\\$1');
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function escapeAttr(value) {
            return escapeHtml(value).replace(/`/g, '&#96;');
        }

        function num(value) {
            return Number(value || 0).toLocaleString();
        }

        function formatSize(bytes) {
            if (!bytes) return '0 B';
            const units = ['B', 'KB', 'MB', 'GB'];
            let size = bytes;
            let idx = 0;
            while (size >= 1024 && idx < units.length - 1) {
                size /= 1024;
                idx++;
            }
            return `${size.toFixed(size < 10 && idx > 0 ? 1 : 0)} ${units[idx]}`;
        }
    </script>
</body>

</html>
