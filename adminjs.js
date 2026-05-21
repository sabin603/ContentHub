// Data arrays (start empty)
var users = [];
var categories = [];
var videos = [];
var requests = [];
var reports = [];

// Navigation
var navLinks = document.querySelectorAll('.nav-link');
var pages = document.querySelectorAll('.page');
var pageHeading = document.getElementById('page-heading');

var pageTitles = {
  dashboard:  'Dashboard',
  users:      'New Users',
  categories: 'Categories',
  videos:     'Videos',
  requests:   'Requests',
  reports:    'Reports'
};

navLinks.forEach(function(link) {
  link.addEventListener('click', function(e) {
    e.preventDefault();
    var target = this.getAttribute('data-page');

    navLinks.forEach(function(l) { l.classList.remove('active'); });
    pages.forEach(function(p) { p.classList.remove('active'); });

    this.classList.add('active');
    document.getElementById('page-' + target).classList.add('active');
    pageHeading.textContent = pageTitles[target];
  });
});

// Update dashboard counts
function updateCounts() {
  document.getElementById('count-users').textContent = users.length;
  document.getElementById('count-videos').textContent = videos.length;
  document.getElementById('count-categories').textContent = categories.length;
  document.getElementById('count-requests').textContent = requests.filter(function(r) { return r.status === 'Pending'; }).length;
  document.getElementById('count-reports').textContent = reports.filter(function(r) { return r.status === 'Open'; }).length;
}

// Render users table
function renderUsers() {
  var tbody = document.getElementById('users-body');
  if (users.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6" class="empty">No users found.</td></tr>';
    return;
  }
  tbody.innerHTML = '';
  users.forEach(function(u, i) {
    tbody.innerHTML += '<tr>' +
      '<td>' + (i + 1) + '</td>' +
      '<td>' + u.name + '</td>' +
      '<td>' + u.email + '</td>' +
      '<td>' + u.joined + '</td>' +
      '<td>' + u.status + '</td>' +
      '<td><button class="action-btn btn-reject" onclick="removeUser(' + i + ')">Remove</button></td>' +
    '</tr>';
  });
}

function removeUser(index) {
  if (confirm('Remove this user?')) {
    users.splice(index, 1);
    renderUsers();
    updateCounts();
  }
}

// Categories
function addCategory() {
  var name = document.getElementById('cat-name').value.trim();
  var desc = document.getElementById('cat-desc').value.trim();
  var msg  = document.getElementById('cat-msg');

  if (!name) {
    msg.textContent = 'Please enter a category name.';
    return;
  }

  var today = new Date().toISOString().slice(0, 10);
  categories.push({ name: name, desc: desc || '-', added: today });

  document.getElementById('cat-name').value = '';
  document.getElementById('cat-desc').value = '';
  msg.textContent = 'Category added successfully.';
  setTimeout(function() { msg.textContent = ''; }, 2500);

  renderCategories();
  updateCounts();
}

function renderCategories() {
  var tbody = document.getElementById('categories-body');
  if (categories.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5" class="empty">No categories added yet.</td></tr>';
    return;
  }
  tbody.innerHTML = '';
  categories.forEach(function(c, i) {
    tbody.innerHTML += '<tr>' +
      '<td>' + (i + 1) + '</td>' +
      '<td>' + c.name + '</td>' +
      '<td>' + c.desc + '</td>' +
      '<td>' + c.added + '</td>' +
      '<td><button class="action-btn btn-reject" onclick="deleteCategory(' + i + ')">Delete</button></td>' +
    '</tr>';
  });
}

function deleteCategory(index) {
  if (confirm('Delete this category?')) {
    categories.splice(index, 1);
    renderCategories();
    updateCounts();
  }
}

// Videos
function renderVideos() {
  var tbody = document.getElementById('videos-body');
  if (videos.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7" class="empty">No videos uploaded yet.</td></tr>';
    return;
  }
  tbody.innerHTML = '';
  videos.forEach(function(v, i) {
    tbody.innerHTML += '<tr>' +
      '<td>' + (i + 1) + '</td>' +
      '<td>' + v.title + '</td>' +
      '<td>' + v.user + '</td>' +
      '<td>' + v.category + '</td>' +
      '<td>' + v.duration + '</td>' +
      '<td>' + v.uploaded + '</td>' +
      '<td>' + v.status + '</td>' +
    '</tr>';
  });
}

// Requests
function renderRequests() {
  var tbody = document.getElementById('requests-body');
  if (requests.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7" class="empty">No requests submitted yet.</td></tr>';
    return;
  }
  tbody.innerHTML = '';
  requests.forEach(function(r, i) {
    var actionBtn = '';
    if (r.status === 'Pending') {
      actionBtn =
        '<button class="action-btn btn-approve" onclick="approveRequest(' + i + ')">Approve</button>' +
        '<button class="action-btn btn-reject" onclick="rejectRequest(' + i + ')">Reject</button>';
    }
    tbody.innerHTML += '<tr>' +
      '<td>' + (i + 1) + '</td>' +
      '<td>' + r.user + '</td>' +
      '<td>' + r.title + '</td>' +
      '<td>' + r.category + '</td>' +
      '<td>' + r.submitted + '</td>' +
      '<td>' + r.status + '</td>' +
      '<td>' + actionBtn + '</td>' +
    '</tr>';
  });
}

function approveRequest(index) {
  requests[index].status = 'Approved';
  renderRequests();
  updateCounts();
}

function rejectRequest(index) {
  requests[index].status = 'Rejected';
  renderRequests();
  updateCounts();
}

// Reports
function renderReports() {
  var tbody = document.getElementById('reports-body');
  if (reports.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7" class="empty">No reports submitted yet.</td></tr>';
    return;
  }
  tbody.innerHTML = '';
  reports.forEach(function(r, i) {
    var actionBtn = '';
    if (r.status === 'Open') {
      actionBtn = '<button class="action-btn btn-resolve" onclick="resolveReport(' + i + ')">Resolve</button>';
    }
    tbody.innerHTML += '<tr>' +
      '<td>' + (i + 1) + '</td>' +
      '<td>' + r.reporter + '</td>' +
      '<td>' + r.type + '</td>' +
      '<td>' + r.desc + '</td>' +
      '<td>' + r.submitted + '</td>' +
      '<td>' + r.status + '</td>' +
      '<td>' + actionBtn + '</td>' +
    '</tr>';
  });
}

function resolveReport(index) {
  reports[index].status = 'Resolved';
  renderReports();
  updateCounts();
}

// Init
updateCounts();