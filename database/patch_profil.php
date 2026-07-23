<?php
// Script to patch profil.php with photo upload support
$target = __DIR__ . '/../frontend/pages/profil.php';
$content = file_get_contents($target);

// === Patch 1: Replace avatar area with photo upload UI ===
$oldAvatar = '                    <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                        style="width:80px;height:80px;background:rgba(255,255,255,0.2);">
                        <i class="fa-solid fa-circle-user text-white" style="font-size: 3.5rem;"></i>
                    </div>';

$newAvatar = '                    <?php
                        $fotoProfil = $user[\'foto_profil\'] ?? \'\';
                        $fotoUrl = (!empty($fotoProfil) && file_exists(__DIR__ . \'/../uploads/profil/\' . basename($fotoProfil)))
                            ? \'../uploads/profil/\' . htmlspecialchars(basename($fotoProfil)) : \'\';
                    ?>
                    <div class="position-relative d-inline-block mb-3" style="cursor:pointer;" onclick="document.getElementById(\'fotoProfilInput\').click();" title="Klik untuk ganti foto">
                        <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center overflow-hidden"
                            style="width:80px;height:80px;background:rgba(255,255,255,0.2);border:3px solid rgba(255,255,255,0.5);">
                            <?php if (!empty($fotoUrl)): ?>
                                <img id="avatarPreview" src="<?= $fotoUrl ?>" alt="Foto Profil" style="width:100%;height:100%;object-fit:cover;">
                            <?php else: ?>
                                <img id="avatarPreview" src="" alt="" style="display:none;width:100%;height:100%;object-fit:cover;">
                                <i id="avatarIcon" class="fa-solid fa-circle-user text-white" style="font-size: 3.5rem;"></i>
                            <?php endif; ?>
                        </div>
                        <span class="position-absolute bottom-0 end-0 bg-white rounded-circle d-flex align-items-center justify-content-center" style="width:24px;height:24px;border:2px solid #3b82f6;">
                            <i class="fa-solid fa-camera text-primary" style="font-size:0.6rem;"></i>
                        </span>
                    </div>
                    <input type="file" id="fotoProfilInput" accept="image/jpeg,image/png,image/webp,image/gif" style="display:none;">';

if (strpos($content, '<div class="rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"') !== false) {
    $content = str_replace($oldAvatar, $newAvatar, $content);
    echo "Patch 1 (avatar area) applied.\n";
} else {
    echo "Patch 1 NOT APPLIED - pattern not found.\n";
}

// === Patch 2: Add uploadFotoStatus div after the small email tag ===
$oldEmailTag = '                    <small class="text-white-50"><?= htmlspecialchars($user[\'email\']) ?></small>
                </div>';
$newEmailTag = '                    <small class="text-white-50"><?= htmlspecialchars($user[\'email\']) ?></small>
                    <div id="uploadFotoStatus" class="mt-1 small"></div>
                </div>';

if (strpos($content, $oldEmailTag) !== false) {
    $content = str_replace($oldEmailTag, $newEmailTag, $content);
    echo "Patch 2 (status div) applied.\n";
} else {
    echo "Patch 2 NOT APPLIED - pattern not found.\n";
}

// === Patch 3: Inject JS for photo upload before </body> or before the script area ===
$uploadJs = <<<'JS'

<script>
// ===== Foto Profil Upload =====
(function() {
    var input = document.getElementById('fotoProfilInput');
    if (!input) return;
    input.addEventListener('change', function() {
        var file = this.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            document.getElementById('uploadFotoStatus').innerHTML = '<span class="text-warning">Ukuran file maks 2MB!</span>';
            return;
        }
        var preview = document.getElementById('avatarPreview');
        var icon    = document.getElementById('avatarIcon');
        var reader  = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            if (icon) icon.style.display = 'none';
        };
        reader.readAsDataURL(file);

        // Upload to server
        var statusEl = document.getElementById('uploadFotoStatus');
        statusEl.innerHTML = '<span class="text-white-50"><i class="fa fa-spinner fa-spin me-1"></i>Mengunggah...</span>';
        var formData = new FormData();
        formData.append('foto_profil', file);
        fetch('upload_foto_profil.php', { method: 'POST', body: formData })
            .then(function(r){ return r.json(); })
            .then(function(data) {
                if (data.success) {
                    statusEl.innerHTML = '<span class="text-success fw-semibold"><i class="fa fa-check me-1"></i>' + data.message + '</span>';
                    if (data.foto_url && preview) {
                        preview.src = data.foto_url + '?t=' + Date.now();
                        preview.style.display = 'block';
                        if (icon) icon.style.display = 'none';
                    }
                    setTimeout(function(){ statusEl.innerHTML = ''; }, 3000);
                } else {
                    statusEl.innerHTML = '<span class="text-danger">' + (data.message || 'Upload gagal') + '</span>';
                }
            })
            .catch(function() {
                statusEl.innerHTML = '<span class="text-danger">Gagal menghubungi server.</span>';
            });
    });
})();
</script>
JS;

// Append JS before closing body tag or at end
if (strpos($content, '// ===== Foto Profil Upload =====') === false) {
    if (strpos($content, '</body>') !== false) {
        $content = str_replace('</body>', $uploadJs . "\n</body>", $content);
        echo "Patch 3 (JS) injected before </body>.\n";
    } else {
        $content .= $uploadJs;
        echo "Patch 3 (JS) appended at end.\n";
    }
} else {
    echo "Patch 3 already applied.\n";
}

file_put_contents($target, $content);
echo "Done. File saved.\n";
