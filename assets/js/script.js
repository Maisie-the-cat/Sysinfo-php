// Theme Management
function toggleTheme() {
    const currentTheme = document.body.className.includes('dark-theme') ? 'dark' : 'light';
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    // Update body class
    document.body.classList.remove(`${currentTheme}-theme`);
    document.body.classList.add(`${newTheme}-theme`);
    
    // Save preference
    localStorage.setItem('theme', newTheme);
    
    // Update button text and icon
    updateThemeButton(newTheme);
}

function updateThemeButton(theme) {
    const button = document.getElementById('theme-toggle');
    if (theme === 'dark') {
        button.innerHTML = '<i class="fas fa-sun"></i> Light Mode';
    } else {
        button.innerHTML = '<i class="fas fa-moon"></i> Dark Mode';
    }
}

// Initialize theme on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check for saved theme preference or default to 'light'
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const theme = savedTheme || (prefersDark ? 'dark' : 'light');
    
    document.body.classList.add(`${theme}-theme`);
    updateThemeButton(theme);
});

// Auto-refresh system information (optional)
function autoRefresh(interval = 30000) {
    setInterval(function() {
        location.reload();
    }, interval);
}

// Uncomment to enable auto-refresh every 30 seconds
// autoRefresh(30000);

// Update last updated time dynamically
setInterval(function() {
    const lastUpdated = document.getElementById('last-updated');
    if (lastUpdated) {
        const now = new Date();
        lastUpdated.textContent = now.toLocaleString();
    }
}, 1000);