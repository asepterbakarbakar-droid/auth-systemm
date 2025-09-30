// Dashboard JavaScript
class Dashboard {
    constructor() {
        this.userData = null;
        this.currentSection = 'overview';
        this.init();
    }

    async init() {
        await this.checkAuthentication();
        this.setupEventListeners();
        this.loadDashboardData();
        this.startRealTimeUpdates();
    }

    async checkAuthentication() {
        try {
            // Check local storage first
            const savedUser = localStorage.getItem('currentUser');
            if (savedUser) {
                this.userData = JSON.parse(savedUser);
                this.displayUserInfo();
                return;
            }

            // Check session via API
            const response = await fetch('http://localhost/auth-system/api/check_auth.php', {
                credentials: 'include'
            });
            
            if (!response.ok) {
                throw new Error('Authentication failed');
            }

            const data = await response.json();
            
            if (data.authenticated) {
                this.userData = data.user;
                localStorage.setItem('currentUser', JSON.stringify(data.user));
                this.displayUserInfo();
            } else {
                this.redirectToLogin();
            }
        } catch (error) {
            console.error('Auth check failed:', error);
            this.redirectToLogin();
        }
    }

    displayUserInfo() {
        if (this.userData) {
            document.getElementById('user-name').textContent = this.userData.name;
            document.getElementById('user-email').textContent = this.userData.email;
            
            // Create avatar from first letter of name
            const firstLetter = this.userData.name.charAt(0).toUpperCase();
            document.getElementById('user-avatar').innerHTML = firstLetter;
            
            // Update welcome message
            document.getElementById('welcome-message').textContent = 
                `Selamat datang, ${this.userData.name}!`;
        }
    }

    setupEventListeners() {
        // Navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const section = e.target.closest('.nav-link').dataset.section;
                this.navigateToSection(section);
            });
        });

        // Real-time clock
        this.updateClock();
        setInterval(() => this.updateClock(), 1000);
    }

    navigateToSection(section) {
        // Update active nav link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        document.querySelector(`[data-section="${section}"]`).classList.add('active');

        // Update page title
        const titles = {
            overview: 'Dashboard Overview',
            users: 'User Management',
            settings: 'System Settings',
            reports: 'Analytics Reports'
        };
        document.getElementById('page-title').textContent = titles[section] || 'Dashboard';

        // Show/hide sections
        document.querySelectorAll('.content-section').forEach(sectionEl => {
            sectionEl.style.display = 'none';
        });
        document.getElementById(`${section}-section`).style.display = 'block';

        // Load section-specific data
        this.loadSectionData(section);
    }

    async loadDashboardData() {
        try {
            // Load users count
            const usersResponse = await fetch('http://localhost/auth-system/api/get_users.php');
            if (usersResponse.ok) {
                const usersData = await usersResponse.json();
                if (usersData.success) {
                    document.getElementById('total-users').textContent = usersData.total || usersData.users.length;
                    
                    // Calculate active users (today)
                    const today = new Date().toDateString();
                    const activeUsers = usersData.users.filter(user => {
                        try {
                            return new Date(user.created_at).toDateString() === today;
                        } catch (e) {
                            return false;
                        }
                    }).length;
                    document.getElementById('active-users').textContent = activeUsers;
                }
            }

            // Update sessions count (simulated)
            document.getElementById('sessions').textContent = Math.floor(Math.random() * 50) + 1;

        } catch (error) {
            console.error('Error loading dashboard data:', error);
            this.showAlert('Error loading dashboard data', 'error');
        }
    }

    loadSectionData(section) {
        switch(section) {
            case 'users':
                this.loadUsersData();
                break;
            case 'reports':
                this.loadReportsData();
                break;
            case 'settings':
                this.loadSettingsData();
                break;
        }
    }

    async loadUsersData() {
        try {
            const response = await fetch('http://localhost/auth-system/api/get_users.php');
            const data = await response.json();
            
            let html = '';
            
            if (data.success && data.users && data.users.length > 0) {
                html = `
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Registered</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                data.users.forEach(user => {
                    const registerDate = new Date(user.created_at).toLocaleDateString('id-ID');
                    html += `
                        <tr>
                            <td>${this.escapeHtml(user.name)}</td>
                            <td>${this.escapeHtml(user.email)}</td>
                            <td>${registerDate}</td>
                            <td>
                                <span class="user-status status-active">Active</span>
                            </td>
                            <td>
                                <button class="btn btn-primary" onclick="editUser(${user.id})" style="padding: 5px 10px; font-size: 12px;">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger" onclick="deleteUser(${user.id})" style="padding: 5px 10px; font-size: 12px;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });

                html += `
                        </tbody>
                    </table>
                `;
            } else {
                html = '<p>No users found.</p>';
            }

            document.getElementById('users-content').innerHTML = html;

        } catch (error) {
            console.error('Error loading users:', error);
            document.getElementById('users-content').innerHTML = 
                '<div class="alert alert-error">Error loading users data</div>';
        }
    }

    loadReportsData() {
        // Simulate reports data loading
        document.querySelector('#reports-section p').textContent = 
            'Analytics and reports will be displayed here. This feature is coming soon!';
    }

    loadSettingsData() {
        // Simulate settings data loading
        document.querySelector('#settings-section p').textContent = 
            'System settings and configuration options will be displayed here.';
    }

    updateClock() {
        const now = new Date();
        document.getElementById('login-time').textContent = now.toLocaleTimeString('id-ID');
        
        // Update activity timestamps
        document.getElementById('login-time-text').textContent = 
            `Today at ${now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}`;
        document.getElementById('session-time').textContent = 
            `${now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}`;
    }

    startRealTimeUpdates() {
        // Update stats every 30 seconds
        setInterval(() => {
            this.loadDashboardData();
        }, 30000);
    }

    showAlert(message, type) {
        const alertContainer = document.getElementById('alert-container');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `
            <strong>${type === 'success' ? '✓' : '✗'}</strong> ${message}
            <button onclick="this.parentElement.remove()" style="float: right; background: none; border: none; cursor: pointer;">×</button>
        `;
        
        alertContainer.appendChild(alert);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alert.parentElement) {
                alert.remove();
            }
        }, 5000);
    }

    redirectToLogin() {
        alert('Session expired. Please login again.');
        window.location.href = 'index.html';
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Global functions
function logout() {
    if (confirm('Are you sure you want to logout?')) {
        localStorage.removeItem('currentUser');
        window.location.href = 'index.html';
    }
}

function refreshData() {
    if (window.dashboard) {
        window.dashboard.loadDashboardData();
        window.dashboard.showAlert('Data refreshed successfully', 'success');
    }
}

function navigateToSection(section) {
    if (window.dashboard) {
        window.dashboard.navigateToSection(section);
    }
}

function addNewUser() {
    alert('Add new user functionality will be implemented here!');
}

function editUser(userId) {
    alert(`Edit user ${userId} functionality will be implemented here!`);
}

function deleteUser(userId) {
    if (confirm(`Are you sure you want to delete user ${userId}?`)) {
        alert(`Delete user ${userId} functionality will be implemented here!`);
    }
}

function loadActivity() {
    if (window.dashboard) {
        window.dashboard.showAlert('Activity data refreshed', 'success');
    }
}

// Initialize dashboard when page loads
document.addEventListener('DOMContentLoaded', () => {
    window.dashboard = new Dashboard();
});

console.log('🚀 Dashboard initialized');