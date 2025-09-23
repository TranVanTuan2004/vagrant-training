// File helper để quản lý user data trong localStorage

// Lấy thông tin user từ localStorage
function getUserData() {
    const userDataStr = localStorage.getItem('user');
    if (userDataStr) {
        try {
            return JSON.parse(userDataStr);
        } catch (e) {
            console.error('Lỗi parse user data:', e);
            return null;
        }
    }
    return null;
}

// Kiểm tra user có đăng nhập không
function isUserLoggedIn() {
    const userData = getUserData();
    return userData && userData.is_logged_in === true;
}

// Lưu thông tin user vào localStorage
function saveUserData(userData) {
    const data = {
        ...userData,
        is_logged_in: true,
        login_time: new Date().toISOString()
    };
    localStorage.setItem('user', JSON.stringify(data));
}

// Cập nhật thông tin user
function updateUserData(updates) {
    const userData = getUserData();
    if (userData) {
        const updatedData = { ...userData, ...updates };
        localStorage.setItem('user', JSON.stringify(updatedData));
        return true;
    }
    return false;
}

// Xóa thông tin user (logout)
function clearUserData() {
    localStorage.removeItem('user');
}

// Lấy ID của user hiện tại
function getCurrentUserId() {
    const userData = getUserData();
    return userData ? userData.id : null;
}

// Lấy tên của user hiện tại
function getCurrentUserName() {
    const userData = getUserData();
    return userData ? userData.name : null;
}

// Lấy fullname của user hiện tại
function getCurrentUserFullname() {
    const userData = getUserData();
    return userData ? userData.fullname : null;
}

// Lấy thời gian đăng nhập
function getLoginTime() {
    const userData = getUserData();
    return userData ? userData.login_time : null;
}

// Kiểm tra session có hết hạn không (ví dụ: sau 24 giờ)
function isSessionExpired(hours = 24) {
    const loginTime = getLoginTime();
    if (!loginTime) return true;
    
    const loginDate = new Date(loginTime);
    const now = new Date();
    const diffHours = (now - loginDate) / (1000 * 60 * 60);
    
    return diffHours > hours;
}

// Tự động logout nếu session hết hạn
function checkSessionExpiry() {
    if (isUserLoggedIn() && isSessionExpired()) {
        clearUserData();
        alert('Session đã hết hạn. Vui lòng đăng nhập lại.');
        window.location.href = 'login.php';
    }
}

// Export functions để sử dụng trong các file khác
window.UserHelper = {
    getUserData,
    isUserLoggedIn,
    saveUserData,
    updateUserData,
    clearUserData,
    getCurrentUserId,
    getCurrentUserName,
    getCurrentUserFullname,
    getLoginTime,
    isSessionExpired,
    checkSessionExpiry
};
