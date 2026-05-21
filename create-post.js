/* =============================================
   CREATE POST PAGE – create-post.js
============================================= */

// ─────────────────────────────────────────────
// STATE
// ─────────────────────────────────────────────
const state = {
  currentStep: 1,
  thumbnail: null,       // File object
  thumbnailDataURL: null,// Base64 string for preview
  title: '',
  videoUrl: '',
  category: '',
  tags: '',
  description: '',
};

// ─────────────────────────────────────────────
// ELEMENT REFS
// ─────────────────────────────────────────────
const uploadZone      = document.getElementById('uploadZone');
const thumbnailInput  = document.getElementById('thumbnailInput');
const uploadPrompt    = document.getElementById('uploadPrompt');
const previewWrap     = document.getElementById('previewWrap');
const previewImg      = document.getElementById('previewImg');
const changeImageBtn  = document.getElementById('changeImageBtn');
const cropInfo        = document.getElementById('cropInfo');
const cropStatus      = document.getElementById('cropStatus');

const step1Next       = document.getElementById('step1Next');
const step2Back       = document.getElementById('step2Back');
const step2Next       = document.getElementById('step2Next');
const step3Back       = document.getElementById('step3Back');
const publishBtn      = document.getElementById('publishBtn');
const createAnother   = document.getElementById('createAnother');

const postTitle       = document.getElementById('postTitle');
const videoUrlInput   = document.getElementById('videoUrl');
const postCategory    = document.getElementById('postCategory');
const postTags        = document.getElementById('postTags');
const postDescription = document.getElementById('postDescription');

const titleCount      = document.getElementById('titleCount');
const descCount       = document.getElementById('descCount');
const urlPreview      = document.getElementById('urlPreview');
const urlVal          = document.getElementById('urlVal');

const notification    = document.getElementById('notification');

// ─────────────────────────────────────────────
// NOTIFICATION
// ─────────────────────────────────────────────
let notifTimer = null;

function showNotification(msg, type = 'info') {
  clearTimeout(notifTimer);
  notification.textContent = msg;
  notification.className = 'notification show' + (type === 'error' ? ' error' : '');
  notifTimer = setTimeout(() => {
    notification.classList.remove('show');
  }, 3200);
}

// ─────────────────────────────────────────────
// STEP MANAGEMENT
// ─────────────────────────────────────────────
function goToStep(n) {
  document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
  document.getElementById(`panel-step${n}`)?.classList.add('active');

  document.querySelectorAll('.step').forEach((s, i) => {
    s.classList.remove('active', 'done');
    if (i + 1 === n) s.classList.add('active');
    if (i + 1 < n)  s.classList.add('done');
  });

  state.currentStep = n;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function goToSuccess() {
  document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
  document.getElementById('panel-success').classList.add('active');
  document.querySelectorAll('.step').forEach(s => s.classList.add('done'));
}

// ─────────────────────────────────────────────
// IMAGE UPLOAD
// ─────────────────────────────────────────────
function handleFile(file) {
  clearError('thumbError');

  if (!file) return;

  const allowed = ['image/jpeg', 'image/png', 'image/webp'];
  if (!allowed.includes(file.type)) {
    showError('thumbError', 'Please upload a JPG, PNG, or WEBP image.');
    showNotification('Invalid file type.', 'error');
    return;
  }

  const maxBytes = 5 * 1024 * 1024; // 5MB
  if (file.size > maxBytes) {
    showError('thumbError', 'File is too large. Maximum size is 5MB.');
    showNotification('File too large (max 5MB).', 'error');
    return;
  }

  const reader = new FileReader();
  reader.onload = function(e) {
    const dataURL = e.target.result;
    state.thumbnailDataURL = dataURL;
    state.thumbnail = file;

    previewImg.src = dataURL;
    uploadPrompt.style.display = 'none';
    previewWrap.style.display = 'block';

    // Check aspect ratio
    const img = new Image();
    img.onload = function() {
      const ratio = img.width / img.height;
      const ideal = 16 / 9;
      const diff  = Math.abs(ratio - ideal);
      cropInfo.style.display = 'block';
      if (diff < 0.05) {
        cropStatus.textContent = 'Aspect ratio: 16:9 — Perfect fit.';
        cropStatus.className = 'crop-status';
      } else {
        const w = img.width, h = img.height;
        cropStatus.textContent = `Image is ${w}×${h}px (ratio ${(ratio).toFixed(2)}). It will be cropped to fit 16:9.`;
        cropStatus.className = 'crop-status warn';
      }
    };
    img.src = dataURL;
    showNotification('Thumbnail uploaded successfully.');
  };
  reader.readAsDataURL(file);
}

// Click on upload zone
uploadZone.addEventListener('click', (e) => {
  if (e.target === changeImageBtn || changeImageBtn.contains(e.target)) return;
  thumbnailInput.click();
});

changeImageBtn.addEventListener('click', (e) => {
  e.stopPropagation();
  thumbnailInput.click();
});

thumbnailInput.addEventListener('change', () => {
  if (thumbnailInput.files.length) handleFile(thumbnailInput.files[0]);
});

// Drag & drop
uploadZone.addEventListener('dragover', (e) => {
  e.preventDefault();
  uploadZone.classList.add('dragover');
});

uploadZone.addEventListener('dragleave', () => {
  uploadZone.classList.remove('dragover');
});

uploadZone.addEventListener('drop', (e) => {
  e.preventDefault();
  uploadZone.classList.remove('dragover');
  const file = e.dataTransfer.files[0];
  if (file) handleFile(file);
});

// ─────────────────────────────────────────────
// CHAR COUNTERS
// ─────────────────────────────────────────────
postTitle.addEventListener('input', () => {
  const len = postTitle.value.length;
  titleCount.textContent = len;
  titleCount.parentElement.classList.toggle('warn', len > 100);
  clearError('titleError');
});

postDescription.addEventListener('input', () => {
  const len = postDescription.value.length;
  descCount.textContent = len;
  descCount.parentElement.classList.toggle('warn', len > 850);
  clearError('descError');
});

// ─────────────────────────────────────────────
// URL VALIDATION
// ─────────────────────────────────────────────
function isValidUrl(str) {
  try {
    const url = new URL(str);
    return url.protocol === 'http:' || url.protocol === 'https:';
  } catch {
    return false;
  }
}

videoUrlInput.addEventListener('input', () => {
  const val = videoUrlInput.value.trim();
  clearError('urlError');
  if (val && isValidUrl(val)) {
    urlPreview.style.display = 'flex';
    urlVal.textContent = val.length > 55 ? val.slice(0, 55) + '…' : val;
  } else {
    urlPreview.style.display = 'none';
  }
});

// ─────────────────────────────────────────────
// VALIDATION HELPERS
// ─────────────────────────────────────────────
function showError(id, msg) {
  const el = document.getElementById(id);
  if (el) el.textContent = msg;
}

function clearError(id) {
  const el = document.getElementById(id);
  if (el) el.textContent = '';
}

function validateStep1() {
  if (!state.thumbnail) {
    showError('thumbError', 'Please upload a thumbnail image before continuing.');
    showNotification('Thumbnail is required.', 'error');
    return false;
  }
  return true;
}

function validateStep2() {
  let valid = true;

  const title = postTitle.value.trim();
  const url   = videoUrlInput.value.trim();
  const cat   = postCategory.value;
  const desc  = postDescription.value.trim();

  if (!title) {
    showError('titleError', 'Title is required.');
    valid = false;
  } else if (title.length < 5) {
    showError('titleError', 'Title must be at least 5 characters.');
    valid = false;
  }

  if (!url) {
    showError('urlError', 'Video URL is required.');
    valid = false;
  } else if (!isValidUrl(url)) {
    showError('urlError', 'Please enter a valid URL starting with http:// or https://');
    valid = false;
  }

  if (!cat) {
    showError('categoryError', 'Please select a category.');
    valid = false;
  }

  if (!desc) {
    showError('descError', 'Description is required.');
    valid = false;
  } else if (desc.length < 20) {
    showError('descError', 'Description must be at least 20 characters.');
    valid = false;
  }

  if (!valid) showNotification('Please fix the errors before continuing.', 'error');
  return valid;
}

// ─────────────────────────────────────────────
// POPULATE REVIEW
// ─────────────────────────────────────────────
function populateReview() {
  document.getElementById('reviewThumb').src    = state.thumbnailDataURL;
  document.getElementById('reviewTitle').textContent  = state.title;
  document.getElementById('reviewCategory').textContent = state.category;
  document.getElementById('reviewDesc').textContent    = state.description;

  const reviewUrl = document.getElementById('reviewUrl');
  reviewUrl.href        = state.videoUrl;
  reviewUrl.textContent = state.videoUrl.length > 60
    ? state.videoUrl.slice(0, 60) + '…'
    : state.videoUrl;

  const tags = state.tags ? state.tags.split(',').map(t => t.trim()).filter(Boolean).join(', ') : 'No tags';
  document.getElementById('reviewTags').textContent  = tags;
  document.getElementById('reviewDate').textContent  = new Date().toLocaleDateString('en-GB', {
    day: 'numeric', month: 'long', year: 'numeric'
  });
}

// ─────────────────────────────────────────────
// NAVIGATION BUTTONS
// ─────────────────────────────────────────────
step1Next.addEventListener('click', () => {
  if (validateStep1()) goToStep(2);
});

step2Back.addEventListener('click', () => goToStep(1));

step2Next.addEventListener('click', () => {
  if (validateStep2()) {
    state.title       = postTitle.value.trim();
    state.videoUrl    = videoUrlInput.value.trim();
    state.category    = postCategory.value;
    state.tags        = postTags.value.trim();
    state.description = postDescription.value.trim();
    populateReview();
    goToStep(3);
  }
});

step3Back.addEventListener('click', () => goToStep(2));

publishBtn.addEventListener('click', () => {
  publishBtn.textContent = 'Publishing…';
  publishBtn.disabled = true;

  const formData = new FormData();
formData.append('thumbnail',   state.thumbnail);
formData.append('title',       state.title);
formData.append('video_url',   state.videoUrl);
formData.append('category',    state.category);
formData.append('tags',        state.tags);
formData.append('description', state.description);

fetch('save_post.php', { method: 'POST', body: formData })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      goToSuccess();
    } else {
      showNotification(data.message || 'Failed to publish.', 'error');
    }
  })
  .catch(() => showNotification('Network error. Try again.', 'error'))
  .finally(() => {
    publishBtn.textContent = 'Publish Post';
    publishBtn.disabled = false;
  });

  setTimeout(() => {
    goToSuccess();
  }, 1400);
});

createAnother.addEventListener('click', () => {
  // Reset everything
  state.thumbnail = null;
  state.thumbnailDataURL = null;
  state.title = '';
  state.videoUrl = '';
  state.category = '';
  state.tags = '';
  state.description = '';

  thumbnailInput.value = '';
  previewWrap.style.display = 'none';
  uploadPrompt.style.display = 'flex';
  cropInfo.style.display = 'none';

  postTitle.value       = '';
  videoUrlInput.value   = '';
  postCategory.value    = '';
  postTags.value        = '';
  postDescription.value = '';
  titleCount.textContent = '0';
  descCount.textContent  = '0';
  urlPreview.style.display = 'none';

  goToStep(1);
});

/* =============================================
   CONTENTHUB DASHBOARD – app.js
============================================= */

// ─────────────────────────────────────────────
// DATA STORE
// ─────────────────────────────────────────────
const CURRENT_USER = 'John Doe';
const CURRENT_AVATAR = 'JD';

const CATEGORY_ICONS = {
  Technology: '&#9729;',
  Science:    '&#9650;',
  Design:     '&#9670;',
  Business:   '&#9644;',
  Education:  '&#9632;',
};

let posts = [
  {
    id: 1,
    title: 'Building Scalable Web Apps with Node.js',
    author: 'Alice Kim',
    avatar: 'AK',
    category: 'Technology',
    description: 'An in-depth look at architecting Node.js applications that can scale horizontally. Covers microservices, load balancing, caching strategies, and deployment pipelines on cloud infrastructure.',
    videoUrl: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    thumbnail: 'https://picsum.photos/seed/node1/800/450',
    date: '2025-12-10',
    comments: [
      { id: 1, author: 'Mark T', text: 'Excellent breakdown of the load balancing section!', date: '2025-12-11' },
      { id: 2, author: 'Sara P', text: 'Could you do a follow-up on Kubernetes?', date: '2025-12-12' },
    ],
    ratings: [8, 9, 7, 9, 10],
    views: 1240,
    saved: false,
    reported: false,
  },
  {
    id: 2,
    title: 'The Future of Quantum Computing',
    author: 'Dr. Rajesh Nair',
    avatar: 'RN',
    category: 'Science',
    description: 'Quantum computing promises to solve problems classical computers cannot. This lecture covers qubits, superposition, entanglement, and near-term applications in drug discovery, cryptography, and AI.',
    videoUrl: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    thumbnail: 'https://picsum.photos/seed/quantum2/800/450',
    date: '2025-11-28',
    comments: [
      { id: 1, author: 'Priya S', text: 'Finally a clear explanation of entanglement!', date: '2025-11-29' },
    ],
    ratings: [10, 9, 10, 8],
    views: 3820,
    saved: false,
    reported: false,
  },
  {
    id: 3,
    title: 'Mastering UI Design Principles',
    author: 'John Doe',
    avatar: 'JD',
    category: 'Design',
    description: 'Covers Gestalt principles, visual hierarchy, color theory, and typography in the context of modern digital product design. Practical exercises included throughout.',
    videoUrl: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    thumbnail: 'https://picsum.photos/seed/design3/800/450',
    date: '2025-11-15',
    comments: [],
    ratings: [9, 8, 9, 7, 8, 9],
    views: 2110,
    saved: true,
    reported: false,
  },
  {
    id: 4,
    title: 'Startup Fundraising: Seed to Series A',
    author: 'Laura Chen',
    avatar: 'LC',
    category: 'Business',
    description: 'A founder\'s candid walkthrough of raising $4M in seed funding. Topics include pitch deck construction, investor outreach, due diligence, and term sheet negotiation.',
    videoUrl: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    thumbnail: 'https://picsum.photos/seed/startup4/800/450',
    date: '2025-10-30',
    comments: [
      { id: 1, author: 'Tom B', text: 'The term sheet section was incredibly eye-opening.', date: '2025-10-31' },
      { id: 2, author: 'Nina G', text: 'Please cover post-series A too!', date: '2025-11-01' },
    ],
    ratings: [8, 7, 8, 9],
    views: 987,
    saved: false,
    reported: false,
  },
  {
    id: 5,
    title: 'Introduction to Machine Learning Algorithms',
    author: 'John Doe',
    avatar: 'JD',
    category: 'Education',
    description: 'A beginner-friendly series covering supervised, unsupervised, and reinforcement learning. Includes live Python demos using scikit-learn and real-world datasets.',
    videoUrl: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    thumbnail: 'https://picsum.photos/seed/ml5/800/450',
    date: '2025-10-05',
    comments: [
      { id: 1, author: 'Chen W', text: 'Best ML intro I have found — no jargon overload!', date: '2025-10-06' },
    ],
    ratings: [9, 10, 9, 9, 8, 10],
    views: 4561,
    saved: true,
    reported: false,
  },
];

let reportTarget = null; // { type: 'post'|'comment', postId, commentId? }

// ─────────────────────────────────────────────
// HELPERS
// ─────────────────────────────────────────────
function avgRating(ratings) {
  if (!ratings.length) return 0;
  return (ratings.reduce((a, b) => a + b, 0) / ratings.length).toFixed(1);
}

function formatDate(dateStr) {
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
}

function showToast(msg) {
  const existing = document.querySelector('.toast');
  if (existing) existing.remove();
  const t = document.createElement('div');
  t.className = 'toast';
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 3000);
}

// ─────────────────────────────────────────────
// BUILD POST CARD
// ─────────────────────────────────────────────
function buildPostCard(post) {
  const avg = avgRating(post.ratings);
  const userRating = post._userRating || 0;

  const card = document.createElement('div');
  card.className = 'post-card';
  card.dataset.id = post.id;

  card.innerHTML = `
    <div class="post-thumbnail" data-id="${post.id}">
      ${post.thumbnail
        ? `<img src="${post.thumbnail}" alt="${post.title}" loading="lazy" />`
        : `<div class="thumb-placeholder">&#9641;</div>`}
      <div class="play-btn">&#9654;</div>
    </div>
    <div class="post-body">
      <div class="post-meta-top">
        <span class="post-category">${post.category}</span>
        <span class="post-date">${formatDate(post.date)}</span>
      </div>
      <div class="post-author-line">
        <div class="post-avatar">${post.avatar}</div>
        <span class="post-author-name">${post.author}</span>
      </div>
      <div class="post-title" data-id="${post.id}">${post.title}</div>
      <div class="post-desc">${post.description}</div>
      <div class="post-actions">
        <button class="action-btn btn-save ${post.saved ? 'saved' : ''}" data-id="${post.id}">
          ${post.saved ? '&#9670; Saved' : '&#9671; Save'}
        </button>
        <a href="${post.videoUrl}" target="_blank" rel="noopener" class="watch-btn">&#9654; Watch</a>
        <button class="action-btn danger btn-report-post" data-id="${post.id}">&#9888; Report</button>
        <div class="rating-wrap">
          <span class="rating-label">Rate:</span>
          <div class="stars" data-id="${post.id}">
            ${[1,2,3,4,5,6,7,8,9,10].map(n =>
              `<span class="star ${n <= userRating ? 'lit' : ''}" data-val="${n}" title="${n}">&#9733;</span>`
            ).join('')}
          </div>
          <span class="rating-avg">&#9733; ${avg}</span>
        </div>
      </div>
    </div>
    <div class="comments-section" data-id="${post.id}">
      <button class="comments-toggle" data-id="${post.id}">
        &#9660; Comments (${post.comments.length})
      </button>
      <div class="comments-list" style="display:none;" id="comments-${post.id}">
        ${buildCommentsList(post)}
        <div class="comment-input-wrap">
          <input type="text" placeholder="Write a comment…" id="commentInput-${post.id}" />
          <button class="btn-primary" data-id="${post.id}" id="commentSubmit-${post.id}">Post</button>
        </div>
      </div>
    </div>
  `;

  // Thumbnail click → open modal
  card.querySelector('.post-thumbnail').addEventListener('click', () => openPostModal(post.id));
  card.querySelector('.post-title').addEventListener('click', () => openPostModal(post.id));

  // Save
  card.querySelector('.btn-save').addEventListener('click', (e) => {
    post.saved = !post.saved;
    const btn = e.currentTarget;
    btn.classList.toggle('saved', post.saved);
    btn.innerHTML = post.saved ? '&#9670; Saved' : '&#9671; Save';
    showToast(post.saved ? `"${post.title}" saved.` : 'Post removed from saved.');
    if (document.getElementById('section-saved').classList.contains('active')) {
      renderSaved();
    }
  });

  // Report post
  card.querySelector('.btn-report-post').addEventListener('click', () => {
    openReportModal({ type: 'post', postId: post.id });
  });

  // Stars
  const starsEl = card.querySelector('.stars');
  const ratingAvgEl = card.querySelector('.rating-avg');
  starsEl.querySelectorAll('.star').forEach(star => {
    star.addEventListener('click', () => {
      const val = parseInt(star.dataset.val);
      if (post._userRating === val) return;
      if (post._userRating) {
        post.ratings.pop(); // remove previous user rating
      }
      post._userRating = val;
      post.ratings.push(val);
      // update visuals
      starsEl.querySelectorAll('.star').forEach(s => {
        s.classList.toggle('lit', parseInt(s.dataset.val) <= val);
      });
      ratingAvgEl.textContent = '★ ' + avgRating(post.ratings);
      showToast(`You rated "${post.title}" ${val}/10`);
    });

    star.addEventListener('mouseenter', () => {
      const val = parseInt(star.dataset.val);
      starsEl.querySelectorAll('.star').forEach(s => {
        s.classList.toggle('lit', parseInt(s.dataset.val) <= val);
      });
    });

    star.addEventListener('mouseleave', () => {
      const val = post._userRating || 0;
      starsEl.querySelectorAll('.star').forEach(s => {
        s.classList.toggle('lit', parseInt(s.dataset.val) <= val);
      });
    });
  });

  // Comments toggle
  card.querySelector('.comments-toggle').addEventListener('click', (e) => {
    const list = document.getElementById(`comments-${post.id}`);
    const open = list.style.display === 'none';
    list.style.display = open ? 'flex' : 'none';
    if (open) list.style.flexDirection = 'column';
    e.currentTarget.innerHTML = `${open ? '&#9650;' : '&#9660;'} Comments (${post.comments.length})`;
  });

  // Comment submit
  card.querySelector(`#commentSubmit-${post.id}`).addEventListener('click', () => {
    submitComment(post.id);
  });
  card.querySelector(`#commentInput-${post.id}`).addEventListener('keydown', (e) => {
    if (e.key === 'Enter') submitComment(post.id);
  });

  return card;
}

function buildCommentsList(post) {
  if (!post.comments.length) {
    return `<div style="color:var(--text-muted);font-size:14px;font-style:italic;padding:8px 0;">No comments yet. Be the first!</div>`;
  }
  return post.comments.map(c => `
    <div class="comment-item" id="comment-${post.id}-${c.id}">
      <div class="comment-header">
        <span class="comment-author">${c.author}</span>
        <div style="display:flex;align-items:center;gap:10px;">
          <span class="comment-date">${formatDate(c.date)}</span>
          <button class="comment-report" data-post="${post.id}" data-comment="${c.id}" title="Report comment">&#9888; Report</button>
        </div>
      </div>
      <div class="comment-text">${c.text}</div>
    </div>
  `).join('');
}

function submitComment(postId) {
  const input = document.getElementById(`commentInput-${postId}`);
  const text = input.value.trim();
  if (!text) return;
  const post = posts.find(p => p.id === postId);
  const newComment = {
    id: Date.now(),
    author: CURRENT_USER,
    text,
    date: new Date().toISOString().split('T')[0],
  };
  post.comments.push(newComment);
  input.value = '';
  // refresh comment list inside current card
  const list = document.getElementById(`comments-${postId}`);
  const inputWrap = list.querySelector('.comment-input-wrap');
  // remove old comments (everything before input wrap)
  Array.from(list.children).forEach(child => {
    if (child !== inputWrap) child.remove();
  });
  list.insertAdjacentHTML('afterbegin', buildCommentsList(post));
  // Re-attach report listeners
  attachCommentReportListeners(list, postId);
  // Update toggle label
  const toggle = document.querySelector(`.comments-toggle[data-id="${postId}"]`);
  if (toggle) toggle.innerHTML = `&#9650; Comments (${post.comments.length})`;
  showToast('Comment posted!');
}

function attachCommentReportListeners(container, postId) {
  container.querySelectorAll('.comment-report').forEach(btn => {
    btn.addEventListener('click', () => {
      openReportModal({ type: 'comment', postId, commentId: parseInt(btn.dataset.comment) });
    });
  });
}

// ─────────────────────────────────────────────
// RENDER FEEDS
// ─────────────────────────────────────────────
function renderFeed(filteredPosts, containerId) {
  const container = document.getElementById(containerId);
  container.innerHTML = '';
  if (!filteredPosts.length) {
    container.innerHTML = `<div class="empty-state"><div class="empty-icon">&#9633;</div><p>No posts found.</p></div>`;
    return;
  }
  filteredPosts.forEach((post, i) => {
    const card = buildPostCard(post);
    card.style.animationDelay = `${i * 0.06}s`;
    container.appendChild(card);
  });
}

function renderHome(categoryFilter) {
  let filtered = posts.filter(p => !p.reported);
  if (categoryFilter && categoryFilter !== 'all') {
    filtered = filtered.filter(p => p.category === categoryFilter);
  }
  renderFeed(filtered, 'feed');
}

function renderSaved() {
  const saved = posts.filter(p => p.saved && !p.reported);
  renderFeed(saved, 'savedFeed');
}

function renderPerformance() {
  const myPosts = posts.filter(p => p.author === CURRENT_USER);
  const container = document.getElementById('performanceContainer');
  container.innerHTML = '';

  if (!myPosts.length) {
    container.innerHTML = `<div class="empty-state"><div class="empty-icon">&#9698;</div><p>You have not posted any content yet.</p></div>`;
    return;
  }

  myPosts.forEach(post => {
    const div = document.createElement('div');
    div.className = 'perf-card';
    div.innerHTML = `
      ${post.thumbnail
        ? `<img class="perf-thumb" src="${post.thumbnail}" alt="${post.title}" loading="lazy" />`
        : `<div class="perf-thumb-ph">&#9641;</div>`}
      <div class="perf-info">
        <div class="perf-title">${post.title}</div>
        <div class="perf-date">${formatDate(post.date)} &bull; ${post.category}</div>
        <div class="perf-stats">
          <div class="stat-block"><div class="stat-num">${post.views.toLocaleString()}</div><div class="stat-label">Views</div></div>
          <div class="stat-block"><div class="stat-num">${avgRating(post.ratings)}</div><div class="stat-label">Avg Rating</div></div>
          <div class="stat-block"><div class="stat-num">${post.ratings.length}</div><div class="stat-label">Ratings</div></div>
          <div class="stat-block"><div class="stat-num">${post.comments.length}</div><div class="stat-label">Comments</div></div>
        </div>
      </div>
    `;
    container.appendChild(div);
  });
}

function renderCategories() {
  const grid = document.getElementById('categoryGrid');
  grid.innerHTML = '';
  Object.keys(CATEGORY_ICONS).forEach(cat => {
    const count = posts.filter(p => p.category === cat && !p.reported).length;
    const card = document.createElement('div');
    card.className = 'category-card';
    card.innerHTML = `
      <div class="cat-icon">${CATEGORY_ICONS[cat]}</div>
      <div class="cat-name">${cat}</div>
      <div class="cat-count">${count} post${count !== 1 ? 's' : ''}</div>
    `;
    card.addEventListener('click', () => {
      switchSection('home');
      document.getElementById('categoryFilter').value = cat;
      renderHome(cat);
    });
    grid.appendChild(card);
  });
}

// ─────────────────────────────────────────────
// POST MODAL
// ─────────────────────────────────────────────
function openPostModal(postId) {
  const post = posts.find(p => p.id === postId);
  if (!post) return;
  const body = document.getElementById('modalBody');
  body.innerHTML = `
    <div class="modal-video-wrap">
      <img src="${post.thumbnail || ''}" alt="${post.title}" style="width:100%;height:100%;object-fit:cover;" />
    </div>
    <div class="modal-detail">
      <div class="modal-detail-meta">
        <span class="post-category">${post.category}</span>
        <span class="post-date">${formatDate(post.date)}</span>
        <span style="color:var(--gold);font-size:13px;">&#9733; ${avgRating(post.ratings)} (${post.ratings.length} ratings)</span>
      </div>
      <h2>${post.title}</h2>
      <div class="post-author-line" style="margin-bottom:16px;">
        <div class="post-avatar">${post.avatar}</div>
        <span class="post-author-name">${post.author}</span>
      </div>
      <div class="modal-detail-desc">${post.description}</div>
      <div style="margin-top:24px;display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
        <a href="${post.videoUrl}" target="_blank" rel="noopener" class="watch-btn">&#9654; Watch Video</a>
        <span style="color:var(--text-muted);font-size:13px;">&#9633; ${post.views.toLocaleString()} views</span>
        <span style="color:var(--text-muted);font-size:13px;">&#9670; ${post.comments.length} comments</span>
      </div>
    </div>
  `;
  document.getElementById('postModal').classList.add('open');
}

document.getElementById('closePostModal').addEventListener('click', () => {
  document.getElementById('postModal').classList.remove('open');
});

document.getElementById('postModal').addEventListener('click', (e) => {
  if (e.target === document.getElementById('postModal')) {
    document.getElementById('postModal').classList.remove('open');
  }
});

// ─────────────────────────────────────────────
// REPORT MODAL
// ─────────────────────────────────────────────
function openReportModal(target) {
  reportTarget = target;
  const post = posts.find(p => p.id === target.postId);
  let label = `Reporting post: "${post.title}"`;
  if (target.type === 'comment') {
    const comment = post.comments.find(c => c.id === target.commentId);
    label = `Reporting comment by ${comment ? comment.author : 'unknown'}`;
  }
  document.getElementById('reportTarget').textContent = label;
  document.getElementById('reportMessage').textContent = '';
  document.getElementById('reportMessage').className = 'form-message';
  document.getElementById('reportDetails').value = '';
  document.getElementById('reportModal').classList.add('open');
}

document.getElementById('closeReportModal').addEventListener('click', () => {
  document.getElementById('reportModal').classList.remove('open');
});

document.getElementById('reportModal').addEventListener('click', (e) => {
  if (e.target === document.getElementById('reportModal')) {
    document.getElementById('reportModal').classList.remove('open');
  }
});

document.getElementById('submitReport').addEventListener('click', () => {
  if (!reportTarget) return;
  const reason = document.getElementById('reportReason').value;
  const msg = document.getElementById('reportMessage');

  if (reportTarget.type === 'post') {
    const post = posts.find(p => p.id === reportTarget.postId);
    if (post) post.reported = true;
  }

  msg.textContent = 'Thank you. Your report has been submitted for review.';
  msg.className = 'form-message success';

  setTimeout(() => {
    document.getElementById('reportModal').classList.remove('open');
    reportTarget = null;
    // Refresh feeds
    const activeSection = document.querySelector('.section.active')?.id;
    if (activeSection === 'section-home') renderHome(document.getElementById('categoryFilter').value);
    if (activeSection === 'section-saved') renderSaved();
    showToast('Report submitted. Thank you!');
  }, 1600);
});

// ─────────────────────────────────────────────
// CREATE POST
// ─────────────────────────────────────────────
document.getElementById('submitPost').addEventListener('click', () => {
  const title       = document.getElementById('newTitle').value.trim();
  const videoUrl    = document.getElementById('newVideoUrl').value.trim();
  const thumbnail   = document.getElementById('newThumbnail').value.trim();
  const category    = document.getElementById('newCategory').value;
  const description = document.getElementById('newDescription').value.trim();
  const msg         = document.getElementById('formMessage');

  if (!title || !videoUrl || !description) {
    msg.textContent = 'Please fill in the Title, Video URL, and Description fields.';
    msg.className = 'form-message error';
    return;
  }

  const newPost = {
    id: Date.now(),
    title, videoUrl, thumbnail, category, description,
    author: CURRENT_USER,
    avatar: CURRENT_AVATAR,
    date: new Date().toISOString().split('T')[0],
    comments: [],
    ratings: [],
    views: 0,
    saved: false,
    reported: false,
  };

  posts.unshift(newPost);

  msg.textContent = 'Post published successfully!';
  msg.className = 'form-message success';

  // Clear form
  ['newTitle','newVideoUrl','newThumbnail','newDescription'].forEach(id => {
    document.getElementById(id).value = '';
  });

  setTimeout(() => {
    msg.textContent = '';
    switchSection('home');
    renderHome('all');
    showToast('Your post is live!');
  }, 1400);
});

// ─────────────────────────────────────────────
// SEARCH
// ─────────────────────────────────────────────
function doSearch() {
  const q = document.getElementById('searchInput').value.trim().toLowerCase();
  if (!q) {
    renderFeed([], 'searchFeed');
    return;
  }
  const results = posts.filter(p =>
    !p.reported &&
    (p.title.toLowerCase().includes(q) ||
     p.description.toLowerCase().includes(q) ||
     p.category.toLowerCase().includes(q) ||
     p.author.toLowerCase().includes(q))
  );
  renderFeed(results, 'searchFeed');
}

document.getElementById('searchBtn').addEventListener('click', doSearch);
document.getElementById('searchInput').addEventListener('keydown', (e) => {
  if (e.key === 'Enter') doSearch();
});

// ─────────────────────────────────────────────
// CATEGORY FILTER (Home)
// ─────────────────────────────────────────────
document.getElementById('categoryFilter').addEventListener('change', (e) => {
  renderHome(e.target.value);
});

// ─────────────────────────────────────────────
// SIDEBAR NAVIGATION
// ─────────────────────────────────────────────
function switchSection(name) {
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));

  const section = document.getElementById(`section-${name}`);
  if (section) section.classList.add('active');

  const navItem = document.querySelector(`.nav-item[data-section="${name}"]`);
  if (navItem) navItem.classList.add('active');

  // Lazy render on switch
  if (name === 'home')        renderHome(document.getElementById('categoryFilter').value);
  if (name === 'saved')       renderSaved();
  if (name === 'performance') renderPerformance();
  if (name === 'category')    renderCategories();
  if (name === 'search')      { document.getElementById('searchFeed').innerHTML = ''; document.getElementById('searchInput').value = ''; }
}

document.querySelectorAll('.nav-item').forEach(item => {
  item.addEventListener('click', (e) => {
    e.preventDefault();
    const section = item.dataset.section;
    if (section === 'logout') {
      switchSection('logout');
    } else {
      switchSection(section);
    }
    // close sidebar on mobile
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('open');
  });
});

// Logout buttons
document.getElementById('confirmLogout').addEventListener('click', () => {
  showToast('Logged out. Redirecting…');
  setTimeout(() => { alert('You have been logged out.'); }, 1000);
});

document.getElementById('cancelLogout').addEventListener('click', () => {
  switchSection('home');
});

// ─────────────────────────────────────────────
// HAMBURGER
// ─────────────────────────────────────────────
document.getElementById('hamburger').addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('open');
  document.getElementById('sidebarOverlay').classList.toggle('open');
});

document.getElementById('sidebarOverlay').addEventListener('click', () => {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('open');
});

// ─────────────────────────────────────────────
// INIT
// ─────────────────────────────────────────────
(function init() {
  renderHome('all');
})();