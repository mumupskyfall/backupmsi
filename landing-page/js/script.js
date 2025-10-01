// DOM Elements
const buttons = document.querySelectorAll('.btn');
const messageArea = document.getElementById('messageArea');

// Button content mapping
const buttonMessages = {
    home: {
        title: '🏠 Home',
        content: 'Welcome to the Web Security Testing Platform! Choose any vulnerability type below to start learning and practicing web security concepts.'
    },
    login: {
        title: '💉 SQL Injection',
        content: 'Learn about SQL Injection attacks where malicious SQL statements are inserted into entry fields to manipulate database queries and extract sensitive data.'
    },
    comment: {
        title: '🚨 Cross-Site Scripting (XSS)',
        content: 'Practice identifying and exploiting XSS vulnerabilities where malicious scripts are injected into web pages viewed by other users.'
    },
    ifu: {
        title: '📁 Insecure File Upload (IFU)',
        content: 'Explore file upload vulnerabilities where applications fail to properly validate uploaded files, allowing malicious code execution.'
    },
    cookies: {
        title: '🍪 Cookie Manipulation',
        content: 'Learn how to manipulate cookies to bypass authentication, escalate privileges, or access unauthorized data in web applications.'
    },
    sanitycheck: {
        title: '⚡ Sanity Check',
        content: 'Practice command injection attacks where user input is passed unsanitized to system commands, allowing arbitrary code execution.'
    }
};

// Add hover handlers to buttons
buttons.forEach(button => {
    // Show message on hover
    button.addEventListener('mouseenter', () => {
        const action = button.getAttribute('data-action');
        showMessageArea(action);
        button.classList.add('hovered');
    });
    
    // Hide message when leaving button
    button.addEventListener('mouseleave', () => {
        hideMessageArea();
        button.classList.remove('hovered');
    });
    
    // Allow normal link navigation
    button.addEventListener('click', (e) => {
        // Let the link work normally - no preventDefault()
        const href = button.getAttribute('href');
        console.log(`Navigating to: ${href}`);
        // Link will navigate normally after this
    });
});

// Show message area function
function showMessageArea(action) {
    const message = buttonMessages[action];
    
    if (message) {
        messageArea.style.opacity = '1';
        messageArea.style.transform = 'translateY(0)';
        messageArea.classList.add('active');
        messageArea.innerHTML = `
            <div style="animation: fadeIn 0.3s ease;">
                <h3 style="margin-bottom: 1rem; font-size: 1.3rem;">${message.title}</h3>
                <p style="line-height: 1.6; opacity: 0.9;">${message.content}</p>
            </div>
        `;
    }
}

// Hide message area function
function hideMessageArea() {
    messageArea.style.opacity = '0.5';
    messageArea.style.transform = 'translateY(10px)';
    messageArea.classList.remove('active');
    messageArea.innerHTML = '<p>Hover over any button to learn about different web vulnerabilities</p>';
}

// Add CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .btn.hovered {
        transform: translateY(-8px) scale(1.08) !important;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25) !important;
    }
    
    .message-area {
        transition: opacity 0.3s ease, transform 0.3s ease;
    }
`;
document.head.appendChild(style);

// Add loading animation
window.addEventListener('load', () => {
    document.body.style.opacity = '0';
    document.body.style.transition = 'opacity 0.5s ease';
    
    setTimeout(() => {
        document.body.style.opacity = '1';
    }, 100);
});

// Add stagger animation to buttons on load
document.addEventListener('DOMContentLoaded', () => {
    buttons.forEach((button, index) => {
        button.style.opacity = '0';
        button.style.transform = 'translateY(20px)';
        button.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
        
        setTimeout(() => {
            button.style.opacity = '1';
            button.style.transform = 'translateY(0)';
        }, 100 + (index * 100));
    });
    
    // Set initial message area state
    hideMessageArea();
});
