/**
 * PDF Export utility functions for RentWheels reports
 */

function generatePDF(tableId, filters) {
    // Basic checks
    if (!window.jspdf || !window.jspdf.jsPDF) {
        console.error('jsPDF not loaded. Using basic download instead.');
        alert('PDF library not loaded. Please try again later.');
        return false;
    }

    // Get the table
    const table = document.getElementById(tableId);
    if (!table) {
        alert('Table not found');
        return false;
    }

    try {
        // Create new jsPDF instance
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l', 'mm', 'a4');

        // Add title
        doc.setFontSize(18);
        doc.setTextColor(0, 0, 0);
        doc.text('RentWheels - Vehicle Rental Report', 150, 20, { align: 'center' });
        
        // Add date
        doc.setFontSize(12);
        doc.text('Generated on: ' + new Date().toLocaleDateString(), 150, 28, { align: 'center' });
        
        // Set starting position for table - skip the filters section completely
        let y = 35;
        
        // Get data from table
        const headers = [];
        const data = [];
        
        // Get headers
        const headerRow = table.querySelector('thead tr');
        if (headerRow) {
            headerRow.querySelectorAll('th').forEach(th => {
                headers.push(th.textContent.trim());
            });
        }
        
        // Get data rows
        const rows = table.querySelectorAll('tbody tr');
        let hasData = false;
        
        rows.forEach(row => {
            // Skip rows with colspan (like "no data" messages)
            if (row.querySelector('td[colspan]')) return;
            
            const rowData = [];
            let colIndex = 0;
            
            row.querySelectorAll('td').forEach(cell => {
                let cellText = cell.textContent.trim();
                
                // Fix for PHP peso sign - Replace ₱ with "PHP " for the Total Amount column (index 5)
                if (colIndex === 5 && cellText.includes('₱')) {
                    cellText = "PHP " + cellText.replace('₱', '');
                }
                
                rowData.push(cellText);
                colIndex++;
            });
            
            if (rowData.length > 0) {
                hasData = true;
                data.push(rowData);
            }
        });
        
        // Add empty row if no data
        if (!hasData) {
            data.push(Array(headers.length).fill('No data'));
        }
        
        // Calculate total amount for summary
        let totalAmount = 0;
        if (hasData) {
            data.forEach(row => {
                // Process the 6th column (index 5) which is the amount
                if (row.length > 5) {
                    // Extract numeric part from "PHP 1,234.56" format
                    const amountStr = row[5].replace(/[^0-9.]/g, '');
                    const amount = parseFloat(amountStr);
                    if (!isNaN(amount)) {
                        totalAmount += amount;
                    }
                }
            });
        }
        
        // Add the table with autoTable
        if (typeof doc.autoTable === 'function') {
            doc.autoTable({
                head: [headers],
                body: data,
                startY: y,
                theme: 'grid',
                styles: {
                    fontSize: 9,
                    cellPadding: 3
                },
                headStyles: {
                    fillColor: [52, 152, 219],
                    textColor: [255, 255, 255],
                    fontStyle: 'bold'
                },
                alternateRowStyles: {
                    fillColor: [240, 248, 255]
                },
                columnStyles: {
                    5: { halign: 'right' } // Right align the amount column
                }
            });
            
            // Add summary section with total if we have data
            if (hasData && doc.lastAutoTable) {
                const finalY = doc.lastAutoTable.finalY + 10;
                doc.setFont('helvetica', 'bold');
                doc.text('Total Records: ' + data.length, 14, finalY);
                // Use "PHP" instead of peso sign for better compatibility
                doc.text('Total Revenue: PHP ' + totalAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','), 14, finalY + 7);
            }
        } else {
            // Fallback if autoTable isn't available
            y += 10;
            doc.setFontSize(10);
            doc.text("Table data couldn't be automatically formatted. Please install the autoTable plugin.", 14, y);
        }
        
        // Save the document
        doc.save('RentWheels_Report.pdf');
        return true;
    } catch (e) {
        console.error('PDF generation error:', e);
        alert('Error generating PDF: ' + e.message);
        return false;
    }
}
