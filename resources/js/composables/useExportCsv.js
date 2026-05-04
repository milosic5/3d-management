export function useExportCsv() {
    const exportCsv = (data, filename) => {
        if (!data || !data.length) return;
        
        const headers = Object.keys(data[0]);
        const headerRow = headers.join(',');
        
        const rows = data.map(row => {
            return headers.map(header => {
                let val = row[header];
                if (val === null || val === undefined) val = '';
                // Escape quotes
                val = String(val).replace(/"/g, '""');
                return `"${val}"`;
            }).join(',');
        });
        
        const csvContent = [headerRow, ...rows].join('\n');
        
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        link.setAttribute('href', url);
        link.setAttribute('download', `${filename}.csv`);
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    return { exportCsv };
}
