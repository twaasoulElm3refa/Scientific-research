import { createRouter, createWebHistory } from "vue-router";
import Home from "../views/home/home.vue";
import Login from "../views/auth/login.vue";
import Register from "../views/auth/register.vue";
import profile from "../views/home/profile.vue";
import contact from "../views/home/contact.vue";

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
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
