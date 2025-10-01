// Override alert function to show XSS flag
const originalAlert = window.alert;
window.alert = function(message) {
    // Show XSS flag instead of regular alert
    const encryptedFlag = "dGtqY3N7Q3IwU3NfUzF0M181Q3IxUHQxbkd9";
    const flag = atob(encryptedFlag);
    originalAlert("🚨 XSS SUCCESSFUL! 🚨\n\nFlag: " + flag + "\n\nPayload executed: " + message);
};

// Initialize with some demo comments
const initialComments = [
    {
        name: "Alice",
        comment: "Great website! Thanks for sharing.",
        time: "2 hours ago"
    },
    {
        name: "Bob",
        comment: "This is very helpful information. <b>Keep up the good work!</b>",
        time: "1 hour ago"
    },
    {
        name: "Charlie",
        comment: "Looking forward to testing this system!",
        time: "30 minutes ago"
    }
];

// Clear any stored XSS payloads and load fresh initial comments
localStorage.removeItem('comments');
let comments = initialComments;

function displayComments() {
    const commentsSection = document.getElementById('demoComments');
    commentsSection.innerHTML = '';
    
    comments.forEach((comment, index) => {
        const commentDiv = document.createElement('div');
        commentDiv.className = 'comment-item';
        
        // VULNERABLE: Direct innerHTML without sanitization
        commentDiv.innerHTML = `
            <div class="comment-header">
                <span class="comment-author">${comment.name}</span>
                <span class="comment-time">${comment.time}</span>
            </div>
            <div class="comment-content">${comment.comment}</div>
        `;
        
        commentsSection.appendChild(commentDiv);
    });
}

function addComment(name, comment) {
    const now = new Date();
    const timeString = now.toLocaleTimeString();
    
    const newComment = {
        name: name || 'Anonymous',
        comment: comment,
        time: `Just now (${timeString})`
    };
    
    comments.unshift(newComment); // Add to beginning
    localStorage.setItem('comments', JSON.stringify(comments));
    displayComments();
}

function clearComments() {
    comments = [];
    localStorage.removeItem('comments');
    displayComments();
    
    // Show confirmation
    const banner = document.querySelector('.warning-banner');
    const originalContent = banner.innerHTML;
    banner.innerHTML = '<i class="fas fa-check"></i><strong>All comments cleared!</strong>';
    banner.style.background = 'rgba(16, 185, 129, 0.1)';
    banner.style.borderColor = 'rgba(16, 185, 129, 0.3)';
    banner.style.color = '#86efac';
    
    setTimeout(() => {
        banner.innerHTML = originalContent;
        banner.style.background = 'rgba(239, 68, 68, 0.1)';
        banner.style.borderColor = 'rgba(239, 68, 68, 0.3)';
        banner.style.color = '#fecaca';
    }, 2000);
}

// Vulnerable input processing functions
function livePreview(input) {
    const previewDiv = document.getElementById('livePreview');
    if (input.trim() === '') {
        previewDiv.innerHTML = 'Live preview will appear here...';
    } else {
        // VULNERABLE: Direct innerHTML without sanitization
        previewDiv.innerHTML = 'Preview: ' + input;
    }
}

function processInput(input) {
    // VULNERABLE: Direct eval of input for "advanced features"
    if (input.includes('javascript:')) {
        try {
            // Extract and execute javascript: URLs
            const jsCode = input.match(/javascript:([^"'\s<>]+)/);
            if (jsCode && jsCode[1]) {
                eval(jsCode[1]);
            }
        } catch(e) {
            console.log('JS execution error:', e);
        }
    }
    
    // VULNERABLE: Direct innerHTML processing
    if (input.includes('<script>') || input.includes('onerror') || input.includes('onload')) {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = input;
        // This will execute any scripts
    }
}

// Handle form submission
document.getElementById('commentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const name = document.getElementById('name').value;
    const comment = document.getElementById('comment').value;
    
    if (comment.trim() === '') {
        alert('Please enter a comment!');
        return;
    }
    
    addComment(name, comment);
    
    // Clear form
    document.getElementById('name').value = '';
    document.getElementById('comment').value = '';
    
    // Show success message
    const submitBtn = document.querySelector('.submit-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-check"></i> Posted!';
    submitBtn.style.background = 'linear-gradient(45deg, #059669, #047857)';
    
    setTimeout(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.style.background = 'linear-gradient(45deg, #10b981, #059669)';
    }, 1500);
});

// Initialize comments display
displayComments();

// Make input box vulnerable to attribute injection
const commentTextarea = document.getElementById('comment');
commentTextarea.placeholder = "Write your comment here...";

// Add vulnerable attribute processing
commentTextarea.addEventListener('input', function(e) {
    const value = e.target.value;
    // VULNERABLE: Process potential HTML attributes
    if (value.includes('onfocus=') || value.includes('onmouseover=') || value.includes('onclick=')) {
        // Extract and create vulnerable element
        const match = value.match(/on\w+\s*=\s*([^>\s]+)/);
        if (match) {
            // Create a temporary element to trigger the event
            const tempInput = document.createElement('input');
            tempInput.setAttribute('type', 'hidden');
            tempInput.setAttribute('onfocus', match[1]);
            document.body.appendChild(tempInput);
            tempInput.focus();
            document.body.removeChild(tempInput);
        }
    }
});
