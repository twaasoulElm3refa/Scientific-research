import { createRouter, createWebHistory } from "vue-router";
import Home from "../views/home/home.vue";
import Login from "../views/auth/login.vue";
import Register from "../views/auth/register.vue";
import profile from "../views/home/profile.vue";
import contact from "../views/home/contact.vue";
import adminAuth from "../services/adminAuth";

const AdminLogin = () => import("../views/admin/Login.vue");
const AdminLayout = () => import("../layouts/AdminLayout.vue");
const AdminDashboard = () => import("../views/admin/Dashboard.vue");
const AddDocument = () => import("../views/admin/documents/Create.vue");
const AllDocuments = () => import("../views/admin/documents/Index.vue");
const GoogleDriveSettings = () => import("../views/admin/settings/GoogleDrive.vue");

const routes = [
    {
        path: "/",
        component: Home,
    },
    {
        path: "/profile",
        component: profile,
    },
    {
        path: "/contact",
        component: contact,
    },
    {
        path: "/login",
        component: Login,
        meta: { hideNavbar: true, hideFooter: true },
    },
    {
        path: "/register",
        component: Register,
        meta: { hideNavbar: true, hideFooter: true },
    },
    {
        path: "/admin/login",
        name: "admin.login",
        component: AdminLogin,
        meta: {
            guestOnly: true,
            hideNavbar: true,
            hideFooter: true,
        },
    },
    {
        path: "/admin",
        component: AdminLayout,
        meta: {
            requiresAdmin: true,
            hideNavbar: true,
            hideFooter: true,
        },
        children: [
            {
                path: "",
                name: "admin.dashboard",
                component: AdminDashboard,
            },
            {
                path: "documents/create",
                name: "admin.documents.create",
                component: AddDocument,
            },
            {
                path: "documents",
                name: "admin.documents.index",
                component: AllDocuments,
            },
            {
                path: "settings/google-drive",
                name: "admin.settings.google-drive",
                component: GoogleDriveSettings,
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to) => {
    const requiresAdmin = to.matched.some((record) => record.meta.requiresAdmin);
    const guestOnly = to.matched.some((record) => record.meta.guestOnly);

    if (requiresAdmin) {
        if (!adminAuth.isAuthenticated()) {
            return { name: "admin.login" };
        }

        try {
            await adminAuth.fetchCurrentUser();
            return true;
        } catch {
            adminAuth.clearAuth();
            return { name: "admin.login" };
        }
    }

    if (guestOnly && adminAuth.isAuthenticated()) {
        try {
            await adminAuth.fetchCurrentUser();
            return { name: "admin.dashboard" };
        } catch {
            adminAuth.clearAuth();
        }
    }

    return true;
});

window.addEventListener("admin-auth:unauthorized", () => {
    adminAuth.clearAuth();

    window.setTimeout(() => {
        if (router.currentRoute.value.name !== "admin.login") {
            router.replace({ name: "admin.login" });
        }
    }, 0);
});

export default router;
