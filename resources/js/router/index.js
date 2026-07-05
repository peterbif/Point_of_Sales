import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/store/auth';

const routes = [
    {
        name: "login",
        path: "/login",
        component: () => import("@/component/share/Login.vue"),
    },
    // Cashier
    {
        path: "/cashier",
        component: () => import("@/component/layout/Cashier.vue"),
        meta: {
            requiresAuth: true,
            role: ['cashier']
        },
    },
    {
        path: "/",
        component: () => import("@/component/layout/Admin.vue"),
        meta: {
            requiresAuth: true,
            role: ['admin', 'superadmin', 'superadmin2']
        },
        children: [
            // ========= Dashboard =========
            {
                path: "/",
                component: () => import("@/component/dashboard/Dashboard.vue"),
            },
            // ========= Data =========
            {
                path: "/vat",
                component: () => import("@/component/data/Vat.vue")
            },
            {
                path: "/stock-alert",
                component: () => import("@/component/data/StockAlert.vue")
            },
            {
                path: "/product",
                component: () => import("@/component/data/Product.vue")
            },
            {
                path: "/manage-orders",
                component: () => import("@/component/data/ManageOrders.vue")
            },
            {
                path: "/productinventory",
                component: () => import("@/component/data/ProductInventory.vue")
            },

            {
                path: "/product-category",
                component: () => import("@/component/data/ProductCategory.vue")
            },

            {
                path: "/product-deleted",
                component: () => import("@/component/data/ProductDeleted.vue")
            },

            // ========= Operation =========
            {
                path: "/balance-adjustment",
                component: () => import("@/component/operation/BalanceAdjustment.vue")
            },
            // Setting           
            {
                path: "/user",
                component: () => import("@/component/setting/User.vue")
            },

            {
                path: "/resetdate",
                component: () => import("@/component/setting/ResetSubscriptionDate.vue")
            },

            {
                path: "/:pathMatch(.*)*",
                component: () => import("@/component/share/404.vue"),
            },
            // ========= Report =========
            {
                name: "product-summary",
                path: "/product-summary",
                component: () => import("@/component/report/ProductSummary.vue")
            },
            {
                name: "sale-history",
                path: "/sale-history",
                component: () => import("@/component/report/SaleHistory.vue")
            },
            {
                name: "sale-summary",
                path: "/sale-summary",
                component: () => import("@/component/report/SaleSummary.vue")
            },
        ],
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})
router.beforeEach(async (to, from, next) => {
    const auth = useAuthStore()
    if (auth.user === null) {
        await auth.getUser();
    }
    if (to.meta.requiresAuth && !auth.user) {
        next('/login')
    }
    else if (to.meta.role && !to.meta.role.includes(auth.user?.role)) {
        if (auth.user?.role == 'cashier')
            return next('/cashier')
        else
            return next('/dashboard')
    }
    else {
        next()
    }
})
export default router
