// Default user accounts system for CampusMart
// This file contains default user accounts and authentication functions

// Default user accounts
const defaultUsers = [
    // Admin accounts
    {
        id: 'admin001',
        studentId: 'ADMIN001',
        username: 'admin',
        password: 'admin123',
        fullName: 'System Administrator',
        email: 'admin@jhcerilles.edu.ph',
        role: 'admin',
        course: 'Administration',
        yearLevel: 'Staff',
        dateCreated: '2024-01-01',
        isActive: true,
        profilePicture: null
    },
    {
        id: 'admin002',
        studentId: 'ADMIN002',
        username: 'superadmin',
        password: 'super123',
        fullName: 'Super Administrator',
        email: 'superadmin@jhcerilles.edu.ph',
        role: 'admin',
        course: 'System Administration',
        yearLevel: 'Staff',
        dateCreated: '2024-01-01',
        isActive: true,
        profilePicture: null
    },
    // Student accounts
    {
        id: 'std001',
        studentId: 'JH2024001',
        username: 'Sapuay',
        password: 'student123',
        fullName: 'John Doe',
        email: 'john.doe@jhcerilles.edu.ph',
        role: 'student',
        course: 'Computer Science',
        yearLevel: '3rd Year',
        dateCreated: '2024-01-15',
        isActive: true,
        profilePicture: null
    },
    {
        id: 'std002',
        studentId: 'JH2024002',
        username: 'junil',
        password: 'student123',
        fullName: 'Sarah Martinez',
        email: 'sarah.martinez@jhcerilles.edu.ph',
        role: 'student',
        course: 'Engineering',
        yearLevel: '2nd Year',
        dateCreated: '2024-01-16',
        isActive: true,
        profilePicture: null
    },
    {
        id: 'std003',
        studentId: 'JH2024003',
        username: 'Hilaos',
        password: 'student123',
        fullName: 'Mike Cruz',
        email: 'mike.cruz@jhcerilles.edu.ph',
        role: 'student',
        course: 'Business Administration',
        yearLevel: '4th Year',
        dateCreated: '2024-01-17',
        isActive: true,
        profilePicture: null
    }
];

// Initialize users in localStorage if not exists
function initializeUsers() {
    if (!localStorage.getItem('campusMartUsers')) {
        localStorage.setItem('campusMartUsers', JSON.stringify(defaultUsers));
    }
}

// Get all users
function getAllUsers() {
    const users = localStorage.getItem('campusMartUsers');
    return users ? JSON.parse(users) : defaultUsers;
}

// Get user by username or student ID
function getUserByLogin(login) {
    const users = getAllUsers();
    return users.find(user => 
        user.username === login || 
        user.studentId === login || 
        user.email === login
    );
}

// Authenticate user
function authenticateUser(login, password) {
    const user = getUserByLogin(login);
    if (user && user.password === password && user.isActive) {
        return {
            success: true,
            user: {
                id: user.id,
                studentId: user.studentId,
                username: user.username,
                fullName: user.fullName,
                email: user.email,
                role: user.role,
                course: user.course,
                yearLevel: user.yearLevel
            }
        };
    }
    return {
        success: false,
        message: 'Invalid credentials or inactive account'
    };
}

// Login user and store session
function loginUser(login, password) {
    const authResult = authenticateUser(login, password);
    if (authResult.success) {
        localStorage.setItem('isLoggedIn', 'true');
        localStorage.setItem('currentUser', JSON.stringify(authResult.user));
        localStorage.setItem('userRole', authResult.user.role);
        localStorage.setItem('studentId', authResult.user.studentId);
        return authResult;
    }
    return authResult;
}

// Logout user
function logoutUser() {
    localStorage.removeItem('isLoggedIn');
    localStorage.removeItem('currentUser');
    localStorage.removeItem('userRole');
    localStorage.removeItem('studentId');
}

// Get current logged in user
function getCurrentUser() {
    const user = localStorage.getItem('currentUser');
    return user ? JSON.parse(user) : null;
}

// Check if user is logged in
function isLoggedIn() {
    return localStorage.getItem('isLoggedIn') === 'true';
}

// Check if current user is admin
function isAdmin() {
    const user = getCurrentUser();
    return user && user.role === 'admin';
}

// Check if current user is student
function isStudent() {
    const user = getCurrentUser();
    return user && user.role === 'student';
}

// Add new user (admin function)
function addUser(userData) {
    const users = getAllUsers();
    const newUser = {
        id: 'usr' + Date.now(),
        studentId: userData.studentId,
        username: userData.username,
        password: userData.password || 'defaultPass123',
        fullName: userData.fullName,
        email: userData.email,
        role: userData.role || 'student',
        course: userData.course,
        yearLevel: userData.yearLevel,
        dateCreated: new Date().toISOString().split('T')[0],
        isActive: true,
        profilePicture: null
    };
    
    users.push(newUser);
    localStorage.setItem('campusMartUsers', JSON.stringify(users));
    return newUser;
}

// Update user (admin function)
function updateUser(userId, userData) {
    const users = getAllUsers();
    const userIndex = users.findIndex(user => user.id === userId);
    
    if (userIndex !== -1) {
        users[userIndex] = { ...users[userIndex], ...userData };
        localStorage.setItem('campusMartUsers', JSON.stringify(users));
        return users[userIndex];
    }
    return null;
}

// Delete/deactivate user (admin function)
function deactivateUser(userId) {
    const users = getAllUsers();
    const userIndex = users.findIndex(user => user.id === userId);
    
    if (userIndex !== -1) {
        users[userIndex].isActive = false;
        localStorage.setItem('campusMartUsers', JSON.stringify(users));
        return true;
    }
    return false;
}

// Activate user (admin function)
function activateUser(userId) {
    const users = getAllUsers();
    const userIndex = users.findIndex(user => user.id === userId);
    
    if (userIndex !== -1) {
        users[userIndex].isActive = true;
        localStorage.setItem('campusMartUsers', JSON.stringify(users));
        return true;
    }
    return false;
}

// Get users by role
function getUsersByRole(role) {
    const users = getAllUsers();
    return users.filter(user => user.role === role);
}

// Get students only
function getStudents() {
    return getUsersByRole('student');
}

// Get admins only
function getAdmins() {
    return getUsersByRole('admin');
}

// Search users
function searchUsers(searchTerm) {
    const users = getAllUsers();
    const term = searchTerm.toLowerCase();
    return users.filter(user => 
        user.fullName.toLowerCase().includes(term) ||
        user.studentId.toLowerCase().includes(term) ||
        user.username.toLowerCase().includes(term) ||
        user.email.toLowerCase().includes(term) ||
        user.course.toLowerCase().includes(term)
    );
}

// Initialize users when script loads
initializeUsers();

// Export functions for use in other scripts
window.CampusMartAuth = {
    getAllUsers,
    getUserByLogin,
    authenticateUser,
    loginUser,
    logoutUser,
    getCurrentUser,
    isLoggedIn,
    isAdmin,
    isStudent,
    addUser,
    updateUser,
    deactivateUser,
    activateUser,
    getUsersByRole,
    getStudents,
    getAdmins,
    searchUsers
};
