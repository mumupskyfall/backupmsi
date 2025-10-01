// Initialize default cookies
function initializeCookies() {
    if (!getCookie('username')) {
        setCookieInternal('username', 'guest');
        setCookieInternal('user_id', '1001');
        setCookieInternal('user_role', 'user');
        setCookieInternal('session_token', 'abc123def456');
        setCookieInternal('is_admin', 'false');
        // Hidden flag cookie (encrypted)
        setCookieInternal('flag_cookie', 'tkjcs{W3b_C00k13}');
    }
    loadUser();
}

// Cookie helper functions
function setCookieInternal(name, value, days = 7) {
    let expires = "";
    if (days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function eraseCookie(name) {
    document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}

// User interface functions
function loadUser() {
    const username = getCookie('username') || 'guest';
    const userid = getCookie('user_id') || '1001';
    const role = getCookie('user_role') || 'user';
    const isAdmin = getCookie('is_admin') === 'true' || role === 'admin';

    document.getElementById('username').textContent = username.charAt(0).toUpperCase() + username.slice(1);
    document.getElementById('userid').textContent = userid;
    
    const roleElement = document.getElementById('userrole');
    
    if (isAdmin) {
        roleElement.textContent = 'ADMIN';
        roleElement.className = 'user-role role-admin';
    } else {
        roleElement.textContent = 'USER';
        roleElement.className = 'user-role role-user';
    }
}

function displayCookies() {
    const display = document.getElementById('cookieDisplay');
    const cookies = document.cookie.split(';');
    
    if (cookies.length === 1 && cookies[0] === '') {
        display.textContent = 'No cookies found';
        return;
    }
    
    let cookieText = 'Current Cookies:\n\n';
    cookies.forEach(cookie => {
        const [name, value] = cookie.trim().split('=');
        // Decrypt flag_cookie if found
        if (name === 'flag_cookie') {
            try {
                const decrypted = atob(value);
                cookieText += `${name}: ${decrypted} (decrypted)\n`;
            } catch(e) {
                cookieText += `${name}: ${value}\n`;
            }
        } else {
            cookieText += `${name}: ${value}\n`;
        }
    });
    
    display.textContent = cookieText;
}

// Initialize on page load
window.addEventListener('load', function() {
    initializeCookies();
});
