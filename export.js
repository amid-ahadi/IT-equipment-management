// export.js

async function exportToExcel() {
    const params = new URLSearchParams({
        department: document.getElementById('filterDepartment').value,
        station: document.getElementById('filterStation').value,
        type: document.getElementById('filterType').value,
        status: document.getElementById('filterStatus').value,
        date_from: document.getElementById('filterDateFrom').value,
        date_to: document.getElementById('filterDateTo').value
    });

    const res = await fetch(`get_report_data.php?${params}`);
    const data = await res.json();

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
        "تاریخ تعویض": row.replaced_date
    })));

    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "گزارش کارت‌ریج");

    const excelBuffer = XLSX.write(workbook, { bookType: 'xlsx', type: 'array' });
    const blob = new Blob([excelBuffer], { type: 'application/octet-stream' });
    saveAs(blob, `گزارش_کارت_ریج_${new Date().toISOString().split('T')[0]}.xlsx`);
}

function exportToPDF() {
    alert("💡 صادرات PDF در نسخه آزمایشی — با استفاده از jsPDF یا ابزارهای سروری انجام می‌شود.\n\nبرای نسخه حرفه‌ای، لطفاً از یک سرور PHP با TCPDF یا DomPDF استفاده کنید.");
}
