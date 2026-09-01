import api from "./api";

const readStoredUser = () => {
    try {
        return JSON.parse(localStorage.getItem("admin_user")) || null;
    } catch {
        localStorage.removeItem("admin_user");
        return null;
    }
};

let currentUser = readStoredUser();
let verified = false;
let verificationRequest = null;

const storeUser = (user) => {
    currentUser = user;
    localStorage.setItem("admin_user", JSON.stringify(user));
};

export const getToken = () => localStorage.getItem("admin_token");

export const getCurrentUser = () => currentUser;

export const isAuthenticated = () => Boolean(getToken());

export const clearAuth = () => {
    localStorage.removeItem("admin_token");
    localStorage.removeItem("admin_user");
    currentUser = null;
    verified = false;
    verificationRequest = null;
};

export const login = async (email, password) => {
    const response = await api.post("/admin/login", { email, password });

    localStorage.setItem("admin_token", response.data.token);
    storeUser(response.data.user);
    verified = false;

    return response.data.user;
};

export const fetchCurrentUser = async () => {
    if (!getToken()) {
        clearAuth();
        throw new Error("No admin token is available.");
    }

    if (verified && currentUser) {
        return currentUser;
    }

    if (!verificationRequest) {
        verificationRequest = api
            .get("/admin/me")
            .then((response) => {
                storeUser(response.data.user);
                verified = true;
                return currentUser;
            })
            .finally(() => {
                verificationRequest = null;
            });
    }

    return verificationRequest;
};

export const logout = async () => {
    try {
        if (getToken()) {
            await api.post("/admin/logout");
        }
    } finally {
        clearAuth();
    }
};

export default {
    clearAuth,
    fetchCurrentUser,
    getCurrentUser,
    getToken,
    isAuthenticated,
    login,
    logout,
};
