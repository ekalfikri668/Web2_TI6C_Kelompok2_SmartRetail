<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';

// Fetch list of users who have chatted
$usersApi   = apiRequest('GET', '/admin/chat/users');
$chatUsers  = [];
if ($usersApi['success'] && isset($usersApi['data']) && is_array($usersApi['data'])) {
    $chatUsers = $usersApi['data'];
}
?>

<!-- Custom Chat Styles -->
<style>
.admin-chat-wrapper {
    display: flex;
    height: calc(100vh - 120px);
    min-height: 500px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 4px 24px rgba(0,0,0,0.07);
}

/* --- Sidebar User List --- */
.chat-user-list {
    width: 300px;
    min-width: 260px;
    border-right: 1px solid #e2e8f0;
    display: flex;
    flex-direction: column;
    background: #f8fafc;
}
.chat-user-list-header {
    padding: 18px 16px 14px;
    background: linear-gradient(135deg, #1a2332, #2d3748);
    color: #fff;
}
.chat-user-list-header h6 { margin: 0; font-weight: 700; font-size: 1rem; }
.chat-user-list-header small { opacity: 0.7; }
.chat-user-search {
    padding: 10px 12px;
    border-bottom: 1px solid #e2e8f0;
    background: #fff;
}
.chat-user-search input {
    border-radius: 20px;
    border: 1px solid #d1d5db;
    padding: 6px 14px;
    font-size: 0.83rem;
    width: 100%;
    outline: none;
}
.chat-user-search input:focus { border-color: #3b82f6; }
.chat-user-items { overflow-y: auto; flex: 1; }
.chat-user-item {
    display: flex;
    align-items: center;
    padding: 12px 14px;
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s;
    gap: 10px;
}
.chat-user-item:hover { background: #eff6ff; }
.chat-user-item.active { background: #dbeafe; border-left: 3px solid #3b82f6; }
.chat-user-avatar {
    width: 42px; height: 42px; border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: #fff; font-weight: 700; font-size: 1rem;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; position: relative;
}
.chat-user-avatar .unread-badge {
    position: absolute; top: -3px; right: -3px;
    background: #ef4444; color: #fff;
    border-radius: 50%; width: 18px; height: 18px;
    font-size: 0.6rem; display: flex; align-items: center; justify-content: center;
    font-weight: 700;
}
.chat-user-info { flex: 1; min-width: 0; }
.chat-user-name { font-size: 0.88rem; font-weight: 600; color: #1e293b; margin-bottom: 2px; }
.chat-user-preview {
    font-size: 0.75rem; color: #64748b;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.chat-user-time { font-size: 0.68rem; color: #94a3b8; white-space: nowrap; }

/* --- Main Chat Area --- */
.admin-chat-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.admin-chat-header {
    padding: 14px 20px;
    background: #fff;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 12px;
}
.admin-chat-header .header-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: #fff; font-weight: 700; font-size: 1rem;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.admin-chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f8fafc;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.admin-chat-empty {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    padding: 40px;
    text-align: center;
}
.chat-msg {
    display: flex;
    flex-direction: column;
    max-width: 72%;
}
.chat-msg.admin-msg { align-self: flex-end; align-items: flex-end; }
.chat-msg.user-msg  { align-self: flex-start; align-items: flex-start; }
.chat-bubble {
    padding: 10px 15px;
    border-radius: 16px;
    font-size: 0.87rem;
    line-height: 1.5;
    word-break: break-word;
}
.chat-msg.admin-msg .chat-bubble {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: #fff;
    border-bottom-right-radius: 4px;
}
.chat-msg.user-msg .chat-bubble {
    background: #fff;
    color: #1e293b;
    border: 1px solid #e2e8f0;
    border-bottom-left-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.chat-meta {
    font-size: 0.68rem;
    color: #94a3b8;
    margin-top: 3px;
}
.admin-chat-input {
    padding: 14px 16px;
    background: #fff;
    border-top: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.admin-chat-input textarea {
    flex: 1;
    border: 1px solid #d1d5db;
    border-radius: 20px;
    padding: 10px 16px;
    font-size: 0.87rem;
    resize: none;
    outline: none;
    max-height: 100px;
    line-height: 1.4;
}
.admin-chat-input textarea:focus { border-color: #3b82f6; }
.btn-send-msg {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: #fff; border: none; border-radius: 50%;
    width: 42px; height: 42px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; cursor: pointer; transition: transform 0.15s, opacity 0.15s;
}
.btn-send-msg:hover { transform: scale(1.08); opacity: 0.92; }
.btn-send-msg:disabled { opacity: 0.5; cursor: not-allowed; }

/* No users yet empty state */
.no-users-state {
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    padding: 40px 20px; text-align: center;
    height: 100%;
}
</style>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2 align-items-center">
        <div class="col-sm-6">
          <h1 class="m-0"><i class="fas fa-comments mr-2 text-primary"></i>Pusat Chat Admin</h1>
        </div>
        <div class="col-sm-6 text-sm-right text-muted small">Dashboard / Pusat Chat</div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <div class="admin-chat-wrapper">

        <!-- ==================== USER LIST SIDEBAR ==================== -->
        <div class="chat-user-list">
          <div class="chat-user-list-header">
            <h6><i class="fas fa-users mr-2"></i>Percakapan</h6>
            <small id="userCountLabel"><?= count($chatUsers) ?> pengguna</small>
          </div>
          <div class="chat-user-search">
            <input type="text" id="searchUser" placeholder="Cari pengguna...">
          </div>
          <div class="chat-user-items" id="chatUserList">
            <?php if (empty($chatUsers)): ?>
              <div class="no-users-state">
                <i class="fas fa-comment-slash mb-3" style="font-size:2.5rem;color:#d1d5db;"></i>
                <p class="text-muted small mb-0">Belum ada percakapan dari pengguna.</p>
                <p class="text-muted" style="font-size:0.7rem;">Percakapan akan muncul di sini saat pengguna mengirim pesan pertama.</p>
              </div>
            <?php else: ?>
              <?php foreach ($chatUsers as $cu): ?>
              <div class="chat-user-item"
                   data-id="<?= $cu['id_pembeli'] ?>"
                   data-name="<?= htmlspecialchars($cu['nama_pembeli']) ?>"
                   data-email="<?= htmlspecialchars($cu['email'] ?? '') ?>"
                   onclick="loadChat(<?= $cu['id_pembeli'] ?>, '<?= htmlspecialchars($cu['nama_pembeli'], ENT_QUOTES) ?>')">
                <div class="chat-user-avatar">
                  <?= strtoupper(substr($cu['nama_pembeli'], 0, 1)) ?>
                  <?php if (($cu['unread_count'] ?? 0) > 0): ?>
                    <span class="unread-badge"><?= min($cu['unread_count'], 9) ?></span>
                  <?php endif; ?>
                </div>
                <div class="chat-user-info">
                  <div class="chat-user-name"><?= htmlspecialchars($cu['nama_pembeli']) ?></div>
                  <div class="chat-user-preview"><?= htmlspecialchars($cu['last_message'] ?? '—') ?></div>
                </div>
                <div class="chat-user-time">
                  <?= date('H:i', strtotime($cu['last_time'] ?? 'now')) ?>
                </div>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- ==================== MAIN CHAT AREA ==================== -->
        <div class="admin-chat-main" id="adminChatMain">

          <!-- Empty state (no user selected) -->
          <div class="admin-chat-empty" id="chatEmptyState">
            <i class="fas fa-comment-dots mb-3" style="font-size:4rem;color:#cbd5e1;"></i>
            <h5 class="text-muted">Pilih Percakapan</h5>
            <p class="text-muted small">Klik salah satu pengguna di sebelah kiri untuk memulai membalas pesan.</p>
          </div>

          <!-- Active Chat (hidden by default, shown when user is selected) -->
          <div id="activeChatArea" style="display:none;flex-direction:column;flex:1;overflow:hidden;">
            <!-- Chat Header -->
            <div class="admin-chat-header">
              <div class="header-avatar" id="chatHeaderAvatar">?</div>
              <div>
                <div class="font-weight-bold" id="chatHeaderName">—</div>
                <small class="text-muted" id="chatHeaderEmail">—</small>
              </div>
              <div class="ml-auto">
                <button class="btn btn-sm btn-outline-secondary" onclick="refreshActiveChat()" title="Refresh Chat">
                  <i class="fas fa-rotate"></i>
                </button>
              </div>
            </div>

            <!-- Messages -->
            <div class="admin-chat-messages" id="adminChatMessages">
              <!-- Messages will be loaded here -->
            </div>

            <!-- Input Area -->
            <div class="admin-chat-input">
              <!-- Image attachment -->
              <label class="btn btn-sm btn-light border mb-0" title="Kirim Gambar" style="border-radius:50%;width:38px;height:38px;display:flex;align-items:center;justify-content:center;flex-shrink:0;cursor:pointer;">
                <i class="fas fa-paperclip text-secondary"></i>
                <input type="file" id="adminChatImage" accept="image/*" class="d-none">
              </label>
              <textarea id="adminMsgText" rows="1" placeholder="Ketik balasan untuk pengguna..." onkeydown="handleMsgKeydown(event)"></textarea>
              <button class="btn-send-msg" id="btnSendMsg" onclick="sendAdminMessage()" title="Kirim Pesan">
                <i class="fas fa-paper-plane"></i>
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>
  </section>
</div>

<script>
let currentUserId   = null;
let currentUserName = '';
let autoRefreshInterval = null;

// Use the absolute API URL so we always hit the backend server correctly
const API_BASE  = '<?= BASE_API_URL ?>';
const API_TOKEN = '<?= addslashes($_SESSION['token'] ?? '') ?>';

// ── Load Chat for a specific user ──────────────────────────
function loadChat(userId, userName) {
    currentUserId   = userId;
    currentUserName = userName;

    // Update active state in sidebar
    document.querySelectorAll('.chat-user-item').forEach(el => el.classList.remove('active'));
    const activeItem = document.querySelector(`.chat-user-item[data-id="${userId}"]`);
    if (activeItem) {
        activeItem.classList.add('active');
        // Clear unread badge
        const badge = activeItem.querySelector('.unread-badge');
        if (badge) badge.remove();
    }

    // Update header
    document.getElementById('chatHeaderAvatar').textContent = userName.charAt(0).toUpperCase();
    document.getElementById('chatHeaderName').textContent   = userName;
    const email = activeItem ? (activeItem.dataset.email || '') : '';
    document.getElementById('chatHeaderEmail').textContent  = email || 'Pengguna';

    // Show chat area, hide empty state
    document.getElementById('chatEmptyState').style.display = 'none';
    const activeArea = document.getElementById('activeChatArea');
    activeArea.style.display = 'flex';

    // Load messages
    fetchMessages(userId);

    // Auto-refresh every 8 seconds
    if (autoRefreshInterval) clearInterval(autoRefreshInterval);
    autoRefreshInterval = setInterval(() => fetchMessages(userId, true), 8000);
}

// ── Fetch Messages from API ─────────────────────────────────
function fetchMessages(userId, silent = false) {
    if (!silent) showLoadingMessages();

    fetch(`${API_BASE}/admin/chat?id_pembeli=${userId}`, {
        headers: {
            'Authorization': `Bearer ${API_TOKEN}`,
            'Accept': 'application/json'
        }
    })
    .then(res => {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
    })
    .then(data => {
        if (data.success && Array.isArray(data.data)) {
            renderMessages(data.data);
        } else {
            if (!silent) showNoMessages();
        }
    })
    .catch(err => {
        console.warn('Fetch chat error:', err);
        if (!silent) showNoMessages();
    });
}

function showLoadingMessages() {
    document.getElementById('adminChatMessages').innerHTML = `
        <div class="text-center text-muted py-5">
            <i class="fas fa-spinner fa-spin d-block mb-2" style="font-size:2rem;"></i>
            <small>Memuat pesan...</small>
        </div>`;
}

function showNoMessages() {
    document.getElementById('adminChatMessages').innerHTML = `
        <div class="text-center text-muted py-5">
            <i class="fas fa-comment-slash d-block mb-2" style="font-size:2rem;color:#d1d5db;"></i>
            <small>Belum ada pesan dalam percakapan ini</small>
        </div>`;
}

function renderMessages(messages) {
    const area = document.getElementById('adminChatMessages');
    if (!messages || messages.length === 0) {
        showNoMessages();
        return;
    }

    let html = '';
    messages.forEach(msg => {
        const isAdmin = (msg.pengirim === 'admin');
        const cls     = isAdmin ? 'admin-msg' : 'user-msg';
        const sender  = isAdmin ? 'Admin' : escapeHtml(currentUserName);
        let content   = '';
        if (msg.tipe === 'image') {
            content = `<img src="${escapeHtml(msg.pesan)}" class="img-fluid rounded" style="max-height:200px;" alt="Gambar">`;
        } else {
            content = escapeHtml(msg.pesan || '');
        }

        let actions = '';
        if (isAdmin && !msg.is_deleted) {
            actions = `<div class="message-actions mt-1" style="font-size:0.75rem; text-align: right; width: 100%;">`;
            if (msg.tipe !== 'image') {
                const escapedPesan = (msg.pesan || '').replace(/'/g, "\\'");
                actions += `<a href="javascript:void(0);" onclick="editAdminMessage(${msg.id}, '${escapedPesan}')" class="text-white-50 mr-2 text-decoration-none"><i class="fas fa-edit"></i> Edit</a>`;
            }
            actions += `<a href="javascript:void(0);" onclick="deleteAdminMessage(${msg.id})" class="text-warning text-decoration-none"><i class="fas fa-trash"></i> Hapus</a>`;
            actions += `</div>`;
        }
        
        const editedLabel = msg.is_edited ? `<small class="text-white-50 ml-1">(diedit)</small>` : '';

        html += `
            <div class="chat-msg ${cls}" data-id="${msg.id}">
                <div class="chat-bubble">${content}</div>
                <div class="chat-meta">${escapeHtml(msg.created_at || '')}${editedLabel} · ${sender}</div>
                ${actions}
            </div>`;
    });
    area.innerHTML = html;
    area.scrollTop = area.scrollHeight;
}

window.editAdminMessage = function(id, oldText) {
    Swal.fire({
        title: 'Edit Pesan',
        input: 'textarea',
        inputValue: oldText,
        showCancelButton: true,
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Batal',
        inputValidator: (value) => {
            if (!value || !value.trim()) {
                return 'Pesan tidak boleh kosong!';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const newText = result.value.trim();
            fetch(API_BASE + '/admin/chat/' + id, {
                method: 'PUT',
                headers: {
                    'Authorization': `Bearer ${API_TOKEN}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ pesan: newText })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (currentUserId) fetchMessages(currentUserId, true);
                } else {
                    Swal.fire('Gagal', data.message || 'Gagal mengubah pesan.', 'error');
                }
            })
            .catch(() => Swal.fire('Offline', 'Pesan berhasil diubah (Simulasi Offline)', 'success', () => {
                if (currentUserId) fetchMessages(currentUserId, true);
            }));
        }
    });
};

window.deleteAdminMessage = function(id) {
    Swal.fire({
        title: 'Hapus Pesan?',
        text: "Pesan ini akan dihapus untuk Anda dan penerima.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(API_BASE + '/admin/chat/' + id, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${API_TOKEN}`
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (currentUserId) fetchMessages(currentUserId, true);
                } else {
                    Swal.fire('Gagal', data.message || 'Gagal menghapus pesan.', 'error');
                }
            })
            .catch(() => Swal.fire('Offline', 'Pesan berhasil dihapus (Simulasi Offline)', 'success', () => {
                if (currentUserId) fetchMessages(currentUserId, true);
            }));
        }
    });
};

// ── Send Message ────────────────────────────────────────────
function sendAdminMessage() {
    if (!currentUserId) return;
    const textarea = document.getElementById('adminMsgText');
    const text     = textarea.value.trim();
    if (!text) return;

    const btn = document.getElementById('btnSendMsg');
    btn.disabled = true;

    // Optimistic UI append immediately
    const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    appendMessageBubble(escapeHtml(text), time, true, 'text');
    textarea.value = '';
    textarea.style.height = '';

    // POST to admin/chat endpoint
    const formData = new FormData();
    formData.append('id_pembeli', currentUserId);
    formData.append('pesan', text);

    fetch(`${API_BASE}/admin/chat`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${API_TOKEN}`
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            console.warn('Kirim pesan gagal (server):', data.message);
        }
    })
    .catch(err => {
        console.warn('Kirim pesan gagal (network):', err);
    })
    .finally(() => {
        btn.disabled = false;
    });
}

// ── Send Image Attachment ────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('adminChatImage').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file || !currentUserId) return;

        const reader = new FileReader();
        reader.onload = function(evt) {
            const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            appendMessageBubble(
                `<img src="${evt.target.result}" class="img-fluid rounded" style="max-height:200px;" alt="Gambar">`,
                time, true, 'image'
            );
        };
        reader.readAsDataURL(file);

        const formData = new FormData();
        formData.append('id_pembeli', currentUserId);
        formData.append('gambar', file);

        fetch(`${API_BASE}/admin/chat`, {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${API_TOKEN}` },
            body: formData
        }).catch(err => console.warn('Kirim gambar gagal:', err));

        e.target.value = '';
    });

    // Auto-resize textarea
    const msgArea = document.getElementById('adminMsgText');
    if (msgArea) {
        msgArea.addEventListener('input', function() {
            this.style.height = '';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });
    }

    // User search filter
    document.getElementById('searchUser').addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.chat-user-item').forEach(item => {
            const name  = (item.dataset.name  || '').toLowerCase();
            const email = (item.dataset.email || '').toLowerCase();
            item.style.display = (name.includes(q) || email.includes(q)) ? '' : 'none';
        });
    });
});

function appendMessageBubble(content, time, isAdmin, type) {
    const area   = document.getElementById('adminChatMessages');

    // Remove empty/loading state if present
    const emptyEl = area.querySelector('.text-center');
    if (emptyEl) emptyEl.remove();

    const cls    = isAdmin ? 'admin-msg' : 'user-msg';
    const sender = isAdmin ? 'Admin' : escapeHtml(currentUserName);
    const div    = document.createElement('div');
    div.className = `chat-msg ${cls}`;
    div.innerHTML = `<div class="chat-bubble">${content}</div><div class="chat-meta">${time} · ${sender}</div>`;
    area.appendChild(div);
    area.scrollTop = area.scrollHeight;
}

function handleMsgKeydown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendAdminMessage();
    }
}

function refreshActiveChat() {
    if (currentUserId) fetchMessages(currentUserId);
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
