let uploadedFiles = JSON.parse(localStorage.getItem('uploadedFiles')) || [];
let totalUploadedSize = 0;

// File type detection for icons
function getFileIcon(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    const iconMap = {
        'pdf': 'fas fa-file-pdf',
        'doc': 'fas fa-file-word', 'docx': 'fas fa-file-word',
        'xls': 'fas fa-file-excel', 'xlsx': 'fas fa-file-excel',
        'ppt': 'fas fa-file-powerpoint', 'pptx': 'fas fa-file-powerpoint',
        'jpg': 'fas fa-file-image', 'jpeg': 'fas fa-file-image', 'png': 'fas fa-file-image', 'gif': 'fas fa-file-image',
        'mp4': 'fas fa-file-video', 'avi': 'fas fa-file-video', 'mkv': 'fas fa-file-video',
        'mp3': 'fas fa-file-audio', 'wav': 'fas fa-file-audio',
        'zip': 'fas fa-file-archive', 'rar': 'fas fa-file-archive', '7z': 'fas fa-file-archive',
        'txt': 'fas fa-file-alt',
        'php': 'fas fa-file-code', 'html': 'fas fa-file-code', 'js': 'fas fa-file-code', 'css': 'fas fa-file-code',
        'jsp': 'fas fa-file-code', 'asp': 'fas fa-file-code', 'aspx': 'fas fa-file-code',
        'sh': 'fas fa-terminal', 'bat': 'fas fa-terminal', 'exe': 'fas fa-cog'
    };
    return iconMap[ext] || 'fas fa-file';
}

// Check if file is potentially dangerous
function isDangerousFile(filename) {
    const dangerousExts = ['php', 'jsp', 'asp', 'aspx', 'sh', 'bat', 'exe', 'js', 'html', 'htm'];
    const ext = filename.split('.').pop().toLowerCase();
    return dangerousExts.includes(ext);
}

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Handle file selection
function handleFileSelect(event) {
    const files = Array.from(event.target.files);
    files.forEach(file => {
        // VULNERABILITY: No server-side validation, accept all files
        const fileData = {
            id: Date.now() + Math.random(),
            name: file.name,
            size: file.size,
            type: file.type,
            uploadTime: new Date().toLocaleString(),
            dangerous: isDangerousFile(file.name)
        };
        
        uploadedFiles.push(fileData);
        totalUploadedSize += file.size;
        
        // Check for exploit
        if (fileData.dangerous) {
            showExploit(file.name);
        } else {
            showSuccess();
        }
    });
    
    saveFiles();
    displayFiles();
    event.target.value = ''; // Reset input
}

// Show success message
function showSuccess() {
    const msg = document.getElementById('successMessage');
    msg.style.display = 'block';
    setTimeout(() => {
        msg.style.display = 'none';
    }, 3000);
}

// Show exploit message
function showExploit(filename) {
    const flagDiv = document.getElementById('flagDisplay');
    const flagText = document.getElementById('flagText');
    flagDiv.style.display = 'block';
    
    // Decrypt flag
    const encryptedFlag = "dGtqY3N7VW41M2N1cjNfRjFsM19VcGwwNGRfUHduM2R9";
    const flag = atob(encryptedFlag);
    
    // Add additional exploit info
    flagDiv.innerHTML = `
        <h4 style="color: #fca5a5; margin-bottom: 1rem;">🎉 File Upload Exploit Successful!</h4>
        <p style="color: #fca5a5; margin-bottom: 1rem;">Dangerous file uploaded: <strong>${filename}</strong></p>
        <div class="flag-text">${flag}</div>
    `;
}

// Display uploaded files
function displayFiles() {
    const container = document.getElementById('fileListContainer');
    const fileCount = document.getElementById('fileCount');
    const totalSize = document.getElementById('totalSize');
    
    fileCount.textContent = uploadedFiles.length;
    totalSize.textContent = formatFileSize(totalUploadedSize);
    
    if (uploadedFiles.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; color: rgba(255, 255, 255, 0.6); padding: 2rem;">
                <i class="fas fa-folder-open" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                No files uploaded yet
            </div>
        `;
        return;
    }
    
    container.innerHTML = uploadedFiles.map(file => `
        <div class="file-item" style="${file.dangerous ? 'border: 1px solid rgba(239, 68, 68, 0.5); background: rgba(239, 68, 68, 0.05);' : ''}">
            <div class="file-info">
                <div class="file-icon">
                    <i class="${getFileIcon(file.name)}"></i>
                </div>
                <div class="file-details">
                    <div class="file-name">${file.name} ${file.dangerous ? '⚠️' : ''}</div>
                    <div class="file-size">${formatFileSize(file.size)} • ${file.uploadTime}</div>
                </div>
            </div>
            <div class="file-actions">
                <button class="action-btn btn-view" onclick="viewFile('${file.id}')">
                    <i class="fas fa-eye"></i> View
                </button>
                <button class="action-btn btn-delete" onclick="deleteFile('${file.id}')">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
    `).join('');
}

// View file (simulate execution for dangerous files)
function viewFile(fileId) {
    const file = uploadedFiles.find(f => f.id == fileId);
    if (!file) return;
    
    if (file.dangerous) {
        // Simulate code execution
        const encryptedFlag = "dGtqY3N7VW41M2N1cjNfRjFsM19VcGwwNGRfUHduM2R9";
        const flag = atob(encryptedFlag);
        alert(`⚠️ SECURITY ALERT: Attempting to execute ${file.name}\n\n` +
              `This would execute dangerous code in a real environment!\n\n` +
              `Flag: ${flag}`);
        showExploit(file.name);
    } else {
        alert(`Viewing ${file.name}\n\nFile Type: ${file.type || 'Unknown'}\nSize: ${formatFileSize(file.size)}`);
    }
}

// Delete file
function deleteFile(fileId) {
    const fileIndex = uploadedFiles.findIndex(f => f.id == fileId);
    if (fileIndex > -1) {
        totalUploadedSize -= uploadedFiles[fileIndex].size;
        uploadedFiles.splice(fileIndex, 1);
        saveFiles();
        displayFiles();
    }
}

// Save files to localStorage
function saveFiles() {
    localStorage.setItem('uploadedFiles', JSON.stringify(uploadedFiles));
}

// Drag and drop functionality
const uploadArea = document.getElementById('uploadArea');

uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('dragover');
});

uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('dragover');
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
    
    const files = Array.from(e.dataTransfer.files);
    const fakeEvent = { target: { files: files } };
    handleFileSelect(fakeEvent);
});

// Initialize
window.addEventListener('load', () => {
    // Calculate total size
    totalUploadedSize = uploadedFiles.reduce((sum, file) => sum + (file.size || 0), 0);
    displayFiles();
});
