function openModal() {
    document.getElementById("modal").style.display = "block";
}

function closeModal() {
    document.getElementById("modal").style.display = "none";
    document.getElementById("modalSuccess").innerText = "";
}

function openBulkModal() {
    document.getElementById("bulkModal").style.display = "block";
    loadBulkTypes();
}

function closeBulkModal() {
    document.getElementById("bulkModal").style.display = "none";
    document.getElementById("bulkSuccess").innerText = "";
    document.getElementById("bulkError").innerText = "";
}

async function loadBulkTypes() {
    const container = document.getElementById('bulkItemsContainer');
    container.innerHTML = '<div class="loading">در حال بارگذاری انواع کارت‌ریج...</div>';

    try {
        const res = await fetch("get_options.php");
        const data = await res.json();

        if (!data.types || data.types.length === 0) {
            container.innerHTML = '<div class="error">هیچ نوع کارت‌ریجی وجود ندارد.</div>';
            return;
        }

        let html = '';
        data.types.forEach(type => {
            html += `
                <div style="margin: 10px 0; display: flex; align-items: center; gap: 10px;">
                    <span style="flex: 1; font-weight: bold;">${type}</span>
                    <input type="number" name="count_${type}" min="0" value="0" placeholder="تعداد" style="width: 80px; padding: 6px; border: 1px solid #ddd; border-radius: 4px;" required>
                </div>
            `;
        });

        container.innerHTML = html;

    } catch (err) {
        container.innerHTML = `<div class="error">خطا در بارگذاری انواع: ${err.message}</div>`;
    }
}

document.getElementById("cartridgeForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch("add.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        const successMsg = document.getElementById("successMsg");
        const errorMsg = document.getElementById("errorMsg");

        if (data.includes("❌")) {
            errorMsg.innerText = data;
            successMsg.innerText = "";
        } else {
            successMsg.innerText = data;
            errorMsg.innerText = "";
            this.reset();
            document.querySelector('input[name="replaced_date"]').value = new Date().toISOString().split('T')[0];
            loadRecent();
        }
    });
});

document.getElementById("addDeptForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append("action", "add_department");

    fetch("manage_ajax.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        document.getElementById("modalSuccess").innerText = data;
        this.reset();
        updateDropdowns();
    });
});

document.getElementById("addStationForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append("action", "add_station");

    fetch("manage_ajax.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        document.getElementById("modalSuccess").innerText = data;
        this.reset();
        updateDropdowns();
    });
});

document.getElementById("addTypeForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append("action", "add_type");

    fetch("manage_ajax.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        document.getElementById("modalSuccess").innerText = data;
        this.reset();
        updateDropdowns();
    });
});

document.getElementById("bulkForm").addEventListener("submit", async function(e) {
    e.preventDefault();

    const status = document.querySelector('select[name="status"]').value;
    const replacedDate = document.querySelector('input[name="replaced_date"]').value;

    if (!replacedDate) {
        document.getElementById("bulkError").innerText = "تاریخ بازگشت کارت‌ریج الزامی است.";
        return;
    }

    const formData = new FormData();
    formData.append("action", "bulk_add");
    formData.append("status", "Full");
    formData.append("replaced_date", replacedDate);

    const inputs = document.querySelectorAll('[name^="count_"]');
    let count = 0;
    inputs.forEach(input => {
        const type = input.name.replace('count_', '');
        const qty = parseInt(input.value) || 0;
        if (qty > 0) {
            formData.append(`types[${type}]`, qty);
            count += qty;
        }
    });

    if (count === 0) {
        document.getElementById("bulkError").innerText = "حداقل یک نوع کارت‌ریج را با تعداد بیشتر از صفر انتخاب کنید.";
        return;
    }

    document.getElementById("bulkSuccess").innerText = "";
    document.getElementById("bulkError").innerText = "در حال ثبت...";

    fetch("add_bulk.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        if (data.includes("❌")) {
            document.getElementById("bulkError").innerText = data;
        } else {
            document.getElementById("bulkSuccess").innerText = data;
            document.getElementById("bulkError").innerText = "";
            document.getElementById("bulkForm").reset();
            loadBulkTypes();
            setTimeout(() => {
                closeBulkModal();
            }, 2000);
            loadRecent();
            updateDropdowns();
        }
    })
    .catch(err => {
        document.getElementById("bulkError").innerText = "خطا در اتصال به سرور: " + err.message;
    });
});

function loadRecent() {
    fetch("recent.php")
    .then(res => res.json())
    .then(data => {
        const tbody = document.querySelector("#recentTable tbody");
        tbody.innerHTML = "";
        data.forEach(row => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${row.printer_id}</td>
                <td>${row.department}</td>
                <td>${row.station}</td>
                <td>${row.type}</td>
                <td>${row.status}</td>
                <td>${row.replaced_date}</td>
                <td class="source-tag">${row.source || ''}</td>
            `;
            tbody.appendChild(tr);
        });
    });
}

function updateDropdowns() {
    fetch("get_options.php")
    .then(res => res.json())
    .then(data => {
        const deptSelect = document.getElementById("departmentSelect");
        const stationSelect = document.getElementById("stationSelect");
        const typeSelect = document.getElementById("typeSelect");

        deptSelect.innerHTML = '<option value="">-- انتخاب بخش --</option>';
        data.departments.forEach(name => {
            deptSelect.innerHTML += `<option>${name}</option>`;
        });

        stationSelect.innerHTML = '<option value="">-- انتخاب ایستگاه --</option>';
        data.stations.forEach(name => {
            stationSelect.innerHTML += `<option>${name}</option>`;
        });

        typeSelect.innerHTML = '<option value="">-- انتخاب نوع --</option>';
        data.types.forEach(name => {
            typeSelect.innerHTML += `<option>${name}</option>`;
        });
    });
}

// گزارش‌گیری
let statusChart, monthlyChart, typeChart;
let isChartsVisible = false;

document.addEventListener('DOMContentLoaded', () => {
    updateDropdowns();
    loadRecent();
});

async function applyFilters() {
    document.querySelector('#reportTable tbody').innerHTML = '<tr><td colspan="7" class="loading">در حال بارگیری داده‌ها...</td></tr>';

    const params = new URLSearchParams({
        department: document.getElementById('filterDepartment').value,
        station: document.getElementById('filterStation').value,
        type: document.getElementById('filterType').value,
        status: document.getElementById('filterStatus').value,
        source: document.getElementById('filterSource').value,
        date_from: document.getElementById('filterDateFrom').value,
        date_to: document.getElementById('filterDateTo').value
    });

    const res = await fetch(`get_report_data.php?${params}`);
    const data = await res.json();

    if (data.error) {
        document.querySelector('#reportTable tbody').innerHTML = `<tr><td colspan="7" class="error">${data.error}</td></tr>`;
        return;
    }

    const tbody = document.querySelector('#reportTable tbody');
    tbody.innerHTML = '';
    data.records.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${row.printer_id}</td>
            <td>${row.department}</td>
            <td>${row.station}</td>
            <td>${row.type}</td>
            <td>${row.status}</td>
            <td>${row.replaced_date}</td>
            <td class="source-tag">${row.source || ''}</td>
        `;
        tbody.appendChild(tr);
    });

    if (isChartsVisible) {
        renderCharts(data);
    }
}

function resetFilters() {
    document.getElementById('filterDepartment').value = '';
    document.getElementById('filterStation').value = '';
    document.getElementById('filterType').value = '';
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterSource').value = '';
    document.getElementById('filterDateFrom').value = '<?php echo date("Y-m-01"); ?>';
    document.getElementById('filterDateTo').value = '<?php echo date("Y-m-d"); ?>';
    applyFilters();
}

function toggleCharts() {
    const section = document.getElementById('chartSection');
    const button = document.querySelector('.toggle-charts-btn');

    if (isChartsVisible) {
        section.style.display = 'none';
        button.textContent = '🎨 نمودارها را نمایش بده';
        isChartsVisible = false;
    } else {
        section.style.display = 'block';
        button.textContent = '🙈 نمودارها را پنهان کن';
        isChartsVisible = true;
        applyFilters();
    }
}

function renderCharts(data) {
    if (statusChart) statusChart.destroy();
    if (monthlyChart) monthlyChart.destroy();
    if (typeChart) typeChart.destroy();

    const statusLabels = ['Full', 'Empty'];
    const statusData = [data.stats.full_count || 0, data.stats.empty_count || 0];
    const statusEl = document.getElementById('statusChart');
    statusChart = new Chart(statusEl, {
        type: 'pie',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusData,
                backgroundColor: ['#28a745', '#dc3545'],
                borderColor: ['#218838', '#c82333'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { callbacks: { label: (context) => `${context.label}: ${context.raw}` } }
            }
        }
    });

    const months = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
    const monthlyData = Array(12).fill(0);
    data.monthly.forEach(item => {
        const monthIndex = parseInt(item.month) - 1;
        monthlyData[monthIndex] = item.count;
    });
    const monthlyEl = document.getElementById('monthlyChart');
    monthlyChart = new Chart(monthlyEl, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: 'تعداد تعویض‌ها',
                data: monthlyData,
                backgroundColor: '#17a2b8',
                borderColor: '#138496',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'تعداد' } },
                x: { title: { display: true, text: 'ماه' } }
            },
            plugins: { legend: { display: false } }
        }
    });

    const typeLabels = Object.keys(data.types || {});
    const typeValues = Object.values(data.types || {});
    const typeEl = document.getElementById('typeChart');
    typeChart = new Chart(typeEl, {
        type: 'doughnut',
        data: {
            labels: typeLabels,
            datasets: [{
                data: typeValues,
                backgroundColor: ['#ffc107', '#28a745', '#007bff', '#dc3545', '#6f42c1', '#fd7e14'],
                borderColor: ['#e0a800', '#218838', '#0069d9', '#c82333', '#5a3294', '#e67e22'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { callbacks: { label: (context) => `${context.label}: ${context.raw}` } }
            }
        }
    });
}

function exportToExcel() {
    const params = new URLSearchParams({
        department: document.getElementById('filterDepartment').value,
        station: document.getElementById('filterStation').value,
        type: document.getElementById('filterType').value,
        status: document.getElementById('filterStatus').value,
        source: document.getElementById('filterSource').value,
        date_from: document.getElementById('filterDateFrom').value,
        date_to: document.getElementById('filterDateTo').value
    });

    fetch(`get_report_data.php?${params}`)
        .then(res => res.json())
        .then(data => {
            if (!data.records || data.records.length === 0) {
                alert("هیچ داده‌ای برای صادرات وجود ندارد.");
                return;
            }

            const worksheet = XLSX.utils.json_to_sheet(data.records.map(row => ({
                "شناسه": row.printer_id,
                "بخش": row.department,
                "ایستگاه": row.station,
                "نوع کارت‌ریج": row.type,
                "وضعیت": row.status,
                "تاریخ تعویض": row.replaced_date,
                "منبع": row.source || ''
            })));

            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, "گزارش کارت‌ریج");

            const excelBuffer = XLSX.write(workbook, { bookType: 'xlsx', type: 'array' });
            const blob = new Blob([excelBuffer], { type: 'application/octet-stream' });
            saveAs(blob, `گزارش_کارت_ریج_${new Date().toISOString().split('T')[0]}.xlsx`);
        });
}

function goToCharts() {
    const params = new URLSearchParams({
        department: document.getElementById('filterDepartment').value,
        station: document.getElementById('filterStation').value,
        type: document.getElementById('filterType').value,
        status: document.getElementById('filterStatus').value,
        source: document.getElementById('filterSource').value,
        date_from: document.getElementById('filterDateFrom').value,
        date_to: document.getElementById('filterDateTo').value
    });

    window.location.href = `charts.php?${params.toString()}`;
}

