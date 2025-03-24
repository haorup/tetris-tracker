// Execute when document is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    
    // Table sorting functionality
    const sortableTables = document.querySelectorAll('.sortable');
    
    sortableTables.forEach(table => {
        const headers = table.querySelectorAll('th');
        
        headers.forEach((header, index) => {
            if (header.classList.contains('sortable-header')) {
                header.addEventListener('click', function() {
                    sortTable(table, index);
                });
                
                // Add sort indicators and style
                header.style.cursor = 'pointer';
                header.innerHTML += ' <span class="sort-indicator">&#8645;</span>';
            }
        });
    });
    
    // Table sorting function
    function sortTable(table, column) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const headers = table.querySelectorAll('th');
        const header = headers[column];
        
        // Determine sort direction
        const ascending = header.classList.contains('sort-asc') ? false : true;
        
        // Reset all header sort classes
        headers.forEach(h => {
            h.classList.remove('sort-asc', 'sort-desc');
            const indicator = h.querySelector('.sort-indicator');
            if (indicator) indicator.innerHTML = '&#8645;';
        });
        
        // Set current header sort class and indicator
        header.classList.add(ascending ? 'sort-asc' : 'sort-desc');
        const indicator = header.querySelector('.sort-indicator');
        indicator.innerHTML = ascending ? '&#9650;' : '&#9660;';
        
        // Sort rows
        rows.sort((a, b) => {
            const cellA = a.querySelectorAll('td')[column].textContent.trim();
            const cellB = b.querySelectorAll('td')[column].textContent.trim();
            
            // Numeric sort
            if (!isNaN(cellA) && !isNaN(cellB)) {
                return ascending ? parseFloat(cellA) - parseFloat(cellB) : parseFloat(cellB) - parseFloat(cellA);
            }
            
            // Text sort
            return ascending ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
        });
        
        // Rearrange rows
        rows.forEach(row => tbody.appendChild(row));
    }
    
    // Filter functionality
    const filterForms = document.querySelectorAll('.filter-form');
    
    filterForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Collect filter conditions and submit form
            form.submit();
        });
    });
    
    // Delete confirmation
    const deleteButtons = document.querySelectorAll('.delete-btn');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
});