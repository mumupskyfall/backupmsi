document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const resultDiv = document.getElementById('sqlResult');
    
    // Simulate vulnerable SQL query construction
    const sqlQuery = `SELECT * FROM users WHERE username = '${username}' AND password = '${password}'`;
    
    // Check for SQL injection patterns
    const sqlInjectionPatterns = [
        /'/,  // Single quote
        /--/, // SQL comment
        /\/\*/, // SQL comment start
        /\*\//, // SQL comment end
        /\bor\b/i, // OR keyword
        /\band\b/i, // AND keyword
        /\bunion\b/i, // UNION keyword
        /\bselect\b/i, // SELECT keyword
        /\bdrop\b/i, // DROP keyword
        /\binsert\b/i, // INSERT keyword
        /\bupdate\b/i, // UPDATE keyword
        /\bdelete\b/i, // DELETE keyword
        /=/  // Equals sign for conditions like 1=1
    ];
    
    let isInjection = false;
    for (let pattern of sqlInjectionPatterns) {
        if (pattern.test(username) || pattern.test(password)) {
            isInjection = true;
            break;
        }
    }
    
    resultDiv.style.display = 'block';
    
    if (isInjection) {
        // Simulate successful SQL injection
        const encryptedFlag = "dGtqY3N7NVFsXzFuSjNDVDEwbn0=";
        const flag = atob(encryptedFlag);
        resultDiv.innerHTML = `
            <div style="color: #10b981;">
                <strong>🎯 SQL Injection Detected!</strong><br>
                <strong>Constructed Query:</strong><br>
                <code>${sqlQuery}</code><br><br>
                <strong>🔓 Login Bypassed Successfully!</strong><br>
                <strong>Flag: </strong> <code>${flag}</code>
            </div>
        `;
    } else if ((username === 'admin' && password === 'password') || 
               (username === 'user' && password === 'test')) {
        // Legitimate login
        resultDiv.innerHTML = `
            <div style="color: #3b82f6;">
                <strong>✅ Legitimate Login</strong><br>
                <strong>Query:</strong> <code>${sqlQuery}</code><br>
                <strong>Status:</strong> Valid credentials provided.
            </div>
        `;
    } else {
        // Failed login
        resultDiv.innerHTML = `
            <div style="color: #ef4444;">
                <strong>❌ Login Failed</strong><br>
                <strong>Query:</strong> <code>${sqlQuery}</code><br>
                <strong>Error:</strong> Invalid username or password.<br>
                <em>Try some SQL injection techniques!</em>
            </div>
        `;
    }
});
