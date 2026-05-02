/**
 * API Configuration
 * Student Feedback System Frontend
 */

// API Base URL - Update this to match your backend URL
const API_BASE_URL = 'http://localhost/WD%20Master%20Project/backend';

// API Endpoints
const API_ENDPOINTS = {
    // Authentication
    AUTH: `${API_BASE_URL}/api/auth.php`,
    
    // Feedback
    FEEDBACK: `${API_BASE_URL}/api/feedback.php`,
    
    // Users
    USERS: `${API_BASE_URL}/api/users.php`,
    
    // Courses
    COURSES: `${API_BASE_URL}/api/courses.php`
};

// API Helper Functions
const API = {
    /**
     * Make a GET request to the API
     */
    async get(endpoint, params = {}) {
        try {
            const url = new URL(endpoint);
            Object.keys(params).forEach(key => {
                if (params[key] !== null && params[key] !== undefined) {
                    url.searchParams.append(key, params[key]);
                }
            });
            
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include' // Include cookies for session
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API GET Error:', error);
            throw error;
        }
    },

    /**
     * Make a POST request to the API
     */
    async post(endpoint, data = {}) {
        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data),
                credentials: 'include' // Include cookies for session
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API POST Error:', error);
            throw error;
        }
    },

    /**
     * Make a PUT request to the API
     */
    async put(endpoint, data = {}) {
        try {
            const response = await fetch(endpoint, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data),
                credentials: 'include' // Include cookies for session
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API PUT Error:', error);
            throw error;
        }
    },

    /**
     * Make a DELETE request to the API
     */
    async delete(endpoint, params = {}) {
        try {
            const url = new URL(endpoint);
            Object.keys(params).forEach(key => {
                if (params[key] !== null && params[key] !== undefined) {
                    url.searchParams.append(key, params[key]);
                }
            });
            
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include' // Include cookies for session
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('API DELETE Error:', error);
            throw error;
        }
    }
};

// Authentication Functions
const AuthAPI = {
    /**
     * Login user
     */
    async login(email, password, userType) {
        return await API.post(API_ENDPOINTS.AUTH, {
            action: 'login',
            email,
            password,
            userType
        });
    },

    /**
     * Register user
     */
    async register(userData) {
        return await API.post(API_ENDPOINTS.AUTH, {
            action: 'register',
            ...userData
        });
    },

    /**
     * Logout user
     */
    async logout() {
        return await API.post(API_ENDPOINTS.AUTH, {
            action: 'logout'
        });
    },

    /**
     * Check authentication status
     */
    async checkAuth() {
        return await API.get(API_ENDPOINTS.AUTH, {
            action: 'check'
        });
    }
};

// Feedback Functions
const FeedbackAPI = {
    /**
     * Get feedback list (admin)
     */
    async getList(params = {}) {
        return await API.get(API_ENDPOINTS.FEEDBACK, {
            action: 'list',
            ...params
        });
    },

    /**
     * Get student feedback
     */
    async getStudentFeedback() {
        return await API.get(API_ENDPOINTS.FEEDBACK, {
            action: 'student'
        });
    },

    /**
     * Get teacher feedback
     */
    async getTeacherFeedback() {
        return await API.get(API_ENDPOINTS.FEEDBACK, {
            action: 'teacher'
        });
    },

    /**
     * Submit feedback
     */
    async submitFeedback(feedbackData) {
        return await API.post(API_ENDPOINTS.FEEDBACK, {
            action: 'submit',
            ...feedbackData
        });
    },

    /**
     * Update feedback (admin)
     */
    async updateFeedback(feedbackData) {
        return await API.put(API_ENDPOINTS.FEEDBACK, {
            action: 'update',
            ...feedbackData
        });
    },

    /**
     * Delete feedback (admin)
     */
    async deleteFeedback(feedbackId) {
        return await API.delete(API_ENDPOINTS.FEEDBACK, {
            id: feedbackId
        });
    },

    /**
     * Get feedback statistics
     */
    async getStats() {
        return await API.get(API_ENDPOINTS.FEEDBACK, {
            action: 'stats'
        });
    },

    /**
     * Get rating distribution
     */
    async getRatingDistribution() {
        return await API.get(API_ENDPOINTS.FEEDBACK, {
            action: 'rating-distribution'
        });
    }
};

// User Management Functions
const UserAPI = {
    /**
     * Get user list (admin)
     */
    async getList(params = {}) {
        return await API.get(API_ENDPOINTS.USERS, {
            action: 'list',
            ...params
        });
    },

    /**
     * Get departments
     */
    async getDepartments() {
        return await API.get(API_ENDPOINTS.USERS, {
            action: 'departments'
        });
    },

    /**
     * Get user profile
     */
    async getProfile() {
        return await API.get(API_ENDPOINTS.USERS, {
            action: 'profile'
        });
    },

    /**
     * Create user (admin)
     */
    async createUser(userData) {
        return await API.post(API_ENDPOINTS.USERS, {
            action: 'create',
            ...userData
        });
    },

    /**
     * Update user (admin)
     */
    async updateUser(userData) {
        return await API.put(API_ENDPOINTS.USERS, {
            action: 'update',
            ...userData
        });
    },

    /**
     * Delete user (admin)
     */
    async deleteUser(userId, userType) {
        return await API.delete(API_ENDPOINTS.USERS, {
            id: userId,
            type: userType
        });
    }
};

// Course Management Functions
const CourseAPI = {
    /**
     * Get course list (admin)
     */
    async getList(params = {}) {
        return await API.get(API_ENDPOINTS.COURSES, {
            action: 'list',
            ...params
        });
    },

    /**
     * Get student courses
     */
    async getStudentCourses() {
        return await API.get(API_ENDPOINTS.COURSES, {
            action: 'student'
        });
    },

    /**
     * Get teacher courses
     */
    async getTeacherCourses() {
        return await API.get(API_ENDPOINTS.COURSES, {
            action: 'teacher'
        });
    },

    /**
     * Get departments
     */
    async getDepartments() {
        return await API.get(API_ENDPOINTS.COURSES, {
            action: 'departments'
        });
    },

    /**
     * Get teachers
     */
    async getTeachers(params = {}) {
        return await API.get(API_ENDPOINTS.COURSES, {
            action: 'teachers',
            ...params
        });
    },

    /**
     * Create course (admin)
     */
    async createCourse(courseData) {
        return await API.post(API_ENDPOINTS.COURSES, {
            action: 'create',
            ...courseData
        });
    },

    /**
     * Update course (admin)
     */
    async updateCourse(courseData) {
        return await API.put(API_ENDPOINTS.COURSES, {
            action: 'update',
            ...courseData
        });
    },

    /**
     * Delete course (admin)
     */
    async deleteCourse(courseId) {
        return await API.delete(API_ENDPOINTS.COURSES, {
            id: courseId
        });
    }
};

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        API,
        AuthAPI,
        FeedbackAPI,
        UserAPI,
        CourseAPI,
        API_ENDPOINTS
    };
}
