document.getElementById('search').addEventListener('keyup', function() {
    const filter = this.value.toLowerCase();
    const rows = document.querySelectorAll('#studentsTable tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});

// AJAX for live student count
function updateStudentCount() {
    fetch('api.php?action=count')
        .then(response => response.json())
        .then(data => {
            document.getElementById('studentCount').textContent = data.count;
        })
        .catch(error => console.error('Error:', error));
}

// Update count every 30 seconds
setInterval(updateStudentCount, 30000);
updateStudentCount();