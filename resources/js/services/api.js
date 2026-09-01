import axios from "axios";

const api = axios.create({
    baseURL: "/api",
    headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
    },
});

api.interceptors.request.use((config) => {
    const token = localStorage.getItem("admin_token");

    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
});

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem("admin_token");
            localStorage.removeItem("admin_user");

            if (window.location.pathname !== "/admin/login") {
                window.dispatchEvent(new CustomEvent("admin-auth:unauthorized"));
            }
        }

        return Promise.reject(error);
    },
);

export default api;
