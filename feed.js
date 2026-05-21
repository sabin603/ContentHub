function toggleReport(postId) {
    var form = document.getElementById('report-form-' + postId);
    if (form.style.display === 'none') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}