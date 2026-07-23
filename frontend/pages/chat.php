<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/cek_login.php';
require_once __DIR__ . '/../config/api.php';
require_once __DIR__ . '/../includes/navbar.php';

// Fetch chats from API
$chatApi = apiRequest('GET', '/chat');
$messages = [];

if ($chatApi['success'] && isset($chatApi['data']) && is_array($chatApi['data'])) {
    $messages = $chatApi['data'];
} else {
    // Initial mock messages if offline
    $messages = [
        [
            'id' => 1,
            'pengirim' => 'admin',
            'pesan' => 'Halo! Ada yang bisa kami bantu hari ini mengenai produk LaptopStore?',
            'created_at' => '10:00',
            'tipe' => 'text'
        ],
        [
            'id' => 2,
            'pengirim' => 'user',
            'pesan' => 'Halo admin, apakah laptop ASUS ROG Strix G16 ready stock?',
            'created_at' => '10:02',
            'tipe' => 'text'
        ],
        [
            'id' => 3,
            'pengirim' => 'admin',
            'pesan' => 'Ready kak! Stok kami terbatas sisa 5 unit saja. Bisa langsung checkout di halaman produk ya.',
            'created_at' => '10:03',
            'tipe' => 'text'
        ]
    ];
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="chat-container shadow-sm">
                <!-- Chat Header -->
                <div class="chat-header">
                    <div class="d-flex align-items-center">
                        <i class="fa-solid fa-headset fs-3 me-3 text-tech-blue"></i>
                        <div>
                            <h6 class="m-0 font-weight-bold">Customer Support</h6>
                            <small class="text-success"><i class="fa-solid fa-circle me-1" style="font-size: 0.6rem;"></i> Online</small>
                        </div>
                    </div>
                    <div>
                        <button class="btn btn-outline-light border-secondary btn-sm" id="btnRefreshChat" data-bs-toggle="tooltip" title="Muat Ulang Chat">
                            <i class="fa-solid fa-rotate"></i>
                        </button>
                    </div>
                </div>

                <!-- Chat Body (Messages List) -->
                <div class="chat-messages" id="chatMessagesArea">
                    <?php foreach ($messages as $msg): 
                        $senderClass = ($msg['pengirim'] === 'user') ? 'sent' : 'received';
                        $isDeleted = !empty($msg['is_deleted']);
                    ?>
                        <div class="chat-message <?= $senderClass ?> position-relative" data-id="<?= htmlspecialchars($msg['id'] ?? '') ?>">
                            <div class="bubble">
                                <?php if (isset($msg['tipe']) && $msg['tipe'] === 'image'): ?>
                                    <img src="<?= htmlspecialchars($msg['pesan']) ?>" class="img-fluid rounded-3 mb-2" style="max-height: 200px;">
                                <?php else: ?>
                                    <?= htmlspecialchars($msg['pesan']) ?>
                                <?php endif; ?>
                            </div>
                            <span class="meta">
                                <?= htmlspecialchars($msg['created_at']) ?>
                                <?php if (!empty($msg['is_edited'])): ?><small class="text-muted ms-1">(diedit)</small><?php endif; ?>
                            </span>
                            <?php if ($msg['pengirim'] === 'user' && !$isDeleted): ?>
                                <div class="message-actions text-end mt-1" style="font-size:0.75rem;">
                                    <?php if (isset($msg['tipe']) && $msg['tipe'] !== 'image'): ?>
                                        <a href="javascript:void(0);" onclick="editMessage(<?= $msg['id'] ?>, '<?= htmlspecialchars(addslashes($msg['pesan'])) ?>')" class="text-secondary me-2 text-decoration-none"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                    <?php endif; ?>
                                    <a href="javascript:void(0);" onclick="deleteMessage(<?= $msg['id'] ?>)" class="text-danger text-decoration-none"><i class="fa-solid fa-trash"></i> Hapus</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Chat Footer (Inputs) -->
                <div class="chat-input-area">
                    <form id="chatForm" class="d-flex align-items-center gap-2">
                        <!-- Attachment actions -->
                        <div class="dropdown">
                            <button class="btn btn-light border" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Pilih Lampiran">
                                <i class="fa-solid fa-paperclip text-secondary"></i>
                            </button>
                            <ul class="dropdown-menu border-0 shadow mt-2">
                                <li>
                                    <label class="dropdown-item py-2" style="cursor: pointer;">
                                        <i class="fa-solid fa-image me-2 text-success"></i> Galeri
                                        <input type="file" id="chatGalleryInput" accept="image/*" class="d-none">
                                    </label>
                                </li>
                                <li>
                                    <label class="dropdown-item py-2" style="cursor: pointer;">
                                        <i class="fa-solid fa-camera me-2 text-danger"></i> Kamera
                                        <input type="file" id="chatCameraInput" accept="image/*" capture="environment" class="d-none">
                                    </label>
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Text Input -->
                        <input type="text" class="form-control" id="chatMsgText" placeholder="Tulis pesan ke LaptopStore..." required autocomplete="off">
                        
                        <button type="submit" class="btn btn-primary btn-tech-primary px-4">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const API_BASE = '<?= BASE_API_URL ?>';

document.addEventListener('DOMContentLoaded', function() {
    const $chatArea = document.getElementById('chatMessagesArea');
    
    // Auto scroll chat to bottom
    $chatArea.scrollTop = $chatArea.scrollHeight;

    // Refresh Chat Action
    document.getElementById('btnRefreshChat').addEventListener('click', function() {
        this.querySelector('i').classList.add('fa-spin');
        setTimeout(() => location.reload(), 400);
    });

    // Auto-refresh every 15 seconds
    setInterval(function() {
        fetch(API_BASE + '/chat', {
            headers: {
                'Authorization': 'Bearer <?= $_SESSION['token'] ?? '' ?>',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && Array.isArray(data.data)) {
                // Only refresh if new messages arrived
                const lastMsg = data.data[data.data.length - 1];
                const lastRendered = $chatArea.lastElementChild;
                if (lastMsg && lastRendered) {
                    // Simple check: if last rendered doesn't have the last message text
                    const lastText = lastMsg.pesan || '';
                    if (!lastRendered.textContent.includes(lastText.substring(0, 20))) {
                        renderMessages(data.data);
                    }
                }
            }
        })
        .catch(() => {});
    }, 15000);

    function renderMessages(messages) {
        $chatArea.innerHTML = '';
        messages.forEach(msg => {
            const senderClass = msg.pengirim === 'user' ? 'sent' : 'received';
            let content = '';
            if (msg.tipe === 'image') {
                content = `<img src="${msg.pesan}" class="img-fluid rounded-3 mb-2" style="max-height: 200px;">`;
            } else {
                const div = document.createElement('div');
                div.textContent = msg.pesan;
                content = div.innerHTML;
            }
            
            let actions = '';
            if (msg.pengirim === 'user' && !msg.is_deleted) {
                actions = `<div class="message-actions text-end mt-1" style="font-size:0.75rem;">`;
                if (msg.tipe !== 'image') {
                    // Escape single quotes for js onclick
                    const escapedPesan = (msg.pesan || '').replace(/'/g, "\\'");
                    actions += `<a href="javascript:void(0);" onclick="editMessage(${msg.id}, '${escapedPesan}')" class="text-secondary me-2 text-decoration-none"><i class="fa-solid fa-pen-to-square"></i> Edit</a>`;
                }
                actions += `<a href="javascript:void(0);" onclick="deleteMessage(${msg.id})" class="text-danger text-decoration-none"><i class="fa-solid fa-trash"></i> Hapus</a>`;
                actions += `</div>`;
            }

            const editedLabel = msg.is_edited ? `<small class="text-muted ms-1">(diedit)</small>` : '';

            $chatArea.innerHTML += `
                <div class="chat-message ${senderClass} position-relative" data-id="${msg.id}">
                    <div class="bubble">${content}</div>
                    <span class="meta">${msg.created_at}${editedLabel}</span>
                    ${actions}
                </div>`;
        });
        $chatArea.scrollTop = $chatArea.scrollHeight;
    }

    window.editMessage = function(id, oldText) {
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
                fetch(API_BASE + '/chat/' + id, {
                    method: 'PUT',
                    headers: {
                        'Authorization': 'Bearer <?= $_SESSION['token'] ?? '' ?>',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ pesan: newText })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        Swal.fire('Gagal', data.message || 'Gagal mengubah pesan.', 'error');
                    }
                })
                .catch(() => Swal.fire('Offline', 'Pesan berhasil diubah (Simulasi Offline)', 'success', () => location.reload()));
            }
        });
    };

    window.deleteMessage = function(id) {
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
                fetch(API_BASE + '/chat/' + id, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer <?= $_SESSION['token'] ?? '' ?>'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        Swal.fire('Gagal', data.message || 'Gagal menghapus pesan.', 'error');
                    }
                })
                .catch(() => Swal.fire('Offline', 'Pesan berhasil dihapus (Simulasi Offline)', 'success', () => location.reload()));
            }
        });
    };

    // Handle Form Submit (Sending text)
    document.getElementById('chatForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const textInput = document.getElementById('chatMsgText');
        const text = textInput.value.trim();
        if (!text) return;

        const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        
        // Append sent bubble locally (optimistic UI)
        appendMessageBubble(escapeHtml(text), time, 'sent', 'text');
        textInput.value = '';

        // Send to API via FormData
        const formData = new FormData();
        formData.append('pesan', text);

        fetch(API_BASE + '/chat', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer <?= $_SESSION['token'] ?? '' ?>'
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                console.warn('Pesan dikirim (mode offline):', text);
            }
        })
        .catch(() => {
            // Show mock admin reply if offline
            setTimeout(function() {
                const replyText = "Terima kasih telah menghubungi LaptopStore. Admin kami akan segera meninjau pesan Anda.";
                appendMessageBubble(escapeHtml(replyText), time, 'received', 'text');
            }, 1500);
        });
    });

    // Handle Image Attachment via Galeri/Kamera
    document.getElementById('chatGalleryInput').addEventListener('change', handleImageUpload);
    document.getElementById('chatCameraInput').addEventListener('change', handleImageUpload);

    function handleImageUpload(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(evt) {
            const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            // Render image bubble locally
            appendMessageBubble(evt.target.result, time, 'sent', 'image');
            
            // Send to API
            const formData = new FormData();
            formData.append('gambar', file);

            fetch(API_BASE + '/chat', {
                method: 'POST',
                headers: { 'Authorization': 'Bearer <?= $_SESSION['token'] ?? '' ?>' },
                body: formData
            }).catch(() => {
                setTimeout(function() {
                    appendMessageBubble(escapeHtml("Kami telah menerima gambar Anda. Mohon ditunggu sebentar ya."), time, 'received', 'text');
                }, 2000);
            });
        };
        reader.readAsDataURL(file);
        e.target.value = '';
    }

    function appendMessageBubble(content, time, sender, type) {
        let bubbleContent = '';
        if (type === 'image') {
            bubbleContent = `<img src="${content}" class="img-fluid rounded-3 mb-2" style="max-height: 200px;">`;
        } else {
            bubbleContent = content;
        }

        const div = document.createElement('div');
        div.className = `chat-message ${sender}`;
        div.innerHTML = `<div class="bubble">${bubbleContent}</div><span class="meta">${time}</span>`;
        $chatArea.appendChild(div);
        $chatArea.scrollTo({ top: $chatArea.scrollHeight, behavior: 'smooth' });
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
