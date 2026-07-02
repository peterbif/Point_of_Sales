<template>
    <div>
        <!-- Include Modal Component-->
        <ShareModal ref="messageBox"></shareModal>
        <!-- Form Modal -->
        <div class="modal fade" ref="formModal" tabindex="-1" aria-hidden="true" data-bs-keyboard="false"
            data-bs-backdrop="static" data-bs-focus="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header py-2 bg-secondary text-light">
                        <h5 class="modal-title" style="font-weight: bold">
                            Change Password
                        </h5>
                    </div>
                    <div class="modal-body">
                        <form @submit.prevent="changePassword" id="formPassword">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" :value="auth?.user?.username" disabled />
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label required">Old Password</label>
                                    <input type="password" :disabled="isLoading"
                                        :class="['form-control', { 'is-invalid': errors.old_password }]"
                                        v-model="form.old_password" ref="autofocus" />
                                    <span v-if="errors.old_password" class="invalid-feedback"> {{
                                        errors.old_password[0] }}
                                    </span>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label required">New Password</label>
                                    <input type="password" :disabled="isLoading"
                                        :class="['form-control', { 'is-invalid': errors.new_password }]"
                                        v-model="form.new_password" />
                                    <span v-if="errors.new_password" class="invalid-feedback"> {{
                                        errors.new_password[0] }}
                                    </span>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label required">New Passwo rd Confirmation</label>
                                    <input type="password" :disabled="isLoading"
                                        :class="['form-control', { 'is-invalid': errors.new_password_confirmation }]"
                                        v-model="form.new_password_confirmation" />
                                    <span v-if="errors.new_password_confirmation" class="invalid-feedback"> {{
                                        errors.new_password_confirmation }}
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary px-3" form="formPassword" :disabled="isLoading">
                            <i v-if="isLoading" class="spinner-border spinner-border-sm" role="status"
                                aria-hidden="true"></i>
                            <i v-else class="bi bi-floppy" style="padding-right: 3px;"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <header id="header" class="header fixed-top d-flex align-items-center">
            <div class="d-flex align-items-center justify-content-between">
                <a href="/" class="logo d-flex align-items-center">
                    <!-- <img src="images/favicon.webp"  height="200" width="130" alt="UCH logo"> -->
                    <h3 class="fw-bold d-flex align-items-center gap-2">
  <i class="bi bi-shop text-primary"></i>
  Smart<span class="text-success">Store</span>
</h3>    
                     
                </a>
                <i class="bi bi-list toggle-sidebar-btn"></i>
            </div>
            <nav class="header-nav ms-auto">
                <ul class="d-flex align-items-center">
                    <li class="d-none d-md-inline-block form-inline ms-auto nav-item dropdown me-5">
                        <i class="bi bi-alarm-fill text-secondary pe-2"></i>
                        <span class="text-secondary">{{ auth?.user?.server_time }}</span>
                    </li>
                    <li class="nav-item dropdown pe-3">
                        <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#"
                            data-bs-toggle="dropdown">
                            <i class="bi bi-person-fill" style="font-size: 35px;"></i>
                            <span class="d-none d-md-block dropdown-toggle ps-2 text-capitalize">{{ auth?.user?.username
                                }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                            <li>
                                <button class="dropdown-item d-flex align-items-center" @click="openModal">
                                    <i class="bi bi-shield-lock"></i>
                                    <span>Change Password</span>
                                </button>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <button type="submit" class="dropdown-item d-flex align-items-center"
                                    @click="auth.logout()">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>Sign Out</span>
                                </button>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>

        </header>

        <!-- ======= Sidebar ======= -->
        <aside id="sidebar" class="sidebar">
            <ul class="sidebar-nav" id="sidebar-nav">
                <li class="nav-item">
                    <router-link :to="{ path: '/' }" :class="['nav-link', { collapsed: $route.path != '/' }]">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </router-link>
                </li>
                <li class="nav-heading">Main Data</li>
             
                <li class="nav-item">
                    <router-link :to="{ path: '/product' }"
                        :class="['nav-link', { collapsed: $route.path != '/product' }]">
                        <i class="bi bi-list-ul"></i>
                        <span>Product</span>
                    </router-link>
                </li>

                <li class="nav-item">
                    <router-link :to="{ path: '/vat' }"
                        :class="['nav-link', { collapsed: $route.path != '/vat' }]">
                        <i class="bi bi-percent"></i>
                        <span>Vat</span>
                    </router-link>
                </li>

                <li class="nav-item">
                    <router-link :to="{ path: '/stock-alert' }"
                        :class="['nav-link', { collapsed: $route.path != '/stock-alert' }]">
                        <i class="bi bi-calendar-check"></i>
                        <span>Stock Alert</span>
                    </router-link>
                </li>
                <li class="nav-item">
                    <router-link :to="{ path: '/productinventory' }"
                        :class="['nav-link', { collapsed: $route.path != '/productinventory' }]">
                        <i class="bi bi-list-columns"></i>
                        <span>Product Inventory</span>
                    </router-link>
                </li>
                <li class="nav-item">
                    <router-link :to="{ path: '/product-category' }"
                        :class="['nav-link', { collapsed: $route.path != '/product-category' }]">
                        <i class="bi bi-grid"></i>
                        <span>Product Category</span>
                    </router-link>
                </li>
                <li class="nav-heading">Operation</li>
                <li class="nav-item"  v-if="auth.user?.role == 'superadmin' || auth.user?.role == 'superadmin2'">
                    <router-link to="/balance-adjustment"
                        :class="['nav-link', { collapsed: $route.path != '/balance-adjustment' }]">
                        <i class="bi bi-coin"></i>
                        <span>Balance Adjustment</span>
                    </router-link>
                </li>
                <li class="nav-item" v-if="auth.user?.role == 'superadmin' || auth.user?.role == 'superadmin2'">
                    <router-link :to="{ path: '/manage-orders' }" :class="['nav-link', { collapsed: $route.path != '/table' }]">
                        <i class="bi bi-grid-3x3-gap"></i>
                        <span>Manage Orders</span>
                    </router-link>
                </li>
                <li class="nav-heading">Report</li>
                <li class="nav-item">
                    <router-link :to="{ path: '/sale-summary' }"
                        :class="['nav-link', { collapsed: $route.path != '/sale-summary' }]">
                        <i class="bi bi-graph-up-arrow"></i>
                        <span>Sale Summary</span>
                    </router-link>
                </li>
                <li class="nav-item">
                    <router-link :to="{ path: '/product-summary' }"
                        :class="['nav-link', { collapsed: $route.path != '/product-summary' }]">
                        <i class="bi bi-clipboard-data"></i>
                        <span>Product Summary</span>
                    </router-link>
                </li>
                <li class="nav-item">
                    <router-link :to="{ path: '/sale-history' }"
                        :class="['nav-link', { collapsed: $route.path != '/sale-history' }]">
                        <i class="bi bi-clock-history"></i>
                        <span>Sale History</span>
                    </router-link>
                </li>
                <li class="nav-heading" v-if="auth.user?.role == 'superadmin2' || auth.user?.role == 'superadmin'">System Setting</li>
                <li class="nav-item" v-if="auth.user?.role == 'superadmin2' || auth.user?.role == 'superadmin'">
                    <router-link :to="{ path: '/user' }" :class="['nav-link', { collapsed: $route.path != '/user' }]">
                        <i class="bi bi-people"></i>
                        <span>System User</span>
                    </router-link>

                    </li>

                    <li v-if="auth.user?.role == 'superadmin2'">
                    <router-link :to="{ path: '/resetdate' }" :class="['nav-link', { collapsed: $route.path != '/user' }]">
                        <i class="bi bi-people"></i>
                        <span>Reset Sub Date</span>
                    </router-link>
                </li>

                <li>
    
    
     <div class="mt-3 mt-md-5 ml">
    <p v-if="!network.isOnline">🔴 Offline</p>
    <p v-else-if="!network.isReachable">🟡 No internet access</p>
    <p v-else>🟢 Online</p>
  </div>
                </li>
            </ul>
        </aside>



   

        <main id="main" class="main">
            <router-view></router-view>
        </main>
    </div>
</template>
<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { Modal } from 'bootstrap';
import { useAuthStore } from '@/store/auth';
import ShareModal from '../share/Modal.vue';
import { clearForm, setFocus } from '../../helper.js';
import { useNetWorkStore } from '@/store/network'


const formModalInstance = ref(null);
const formModal = ref(null);
const autofocus = ref(null);
const messageBox = ref(null);
const isLoading = ref(false);
const auth = useAuthStore();
const network = useNetWorkStore()


const form = ref(
    {
        old_password: null,
        new_password: null,
        new_password_confirmation: null
    }
);
const errors = ref({});

onMounted(() => {
    import("../../main.js");
    document.body.style.display = "block";

    if (formModal.value) {
        formModalInstance.value = new Modal(formModal.value);
        formModal.value.addEventListener("shown.bs.modal", () => {
            setFocus(autofocus);
        });
        formModal.value.addEventListener("hide.bs.modal", () => {
            document.activeElement?.blur();
        });
        formModal.value.addEventListener("hidden.bs.modal", () => {
            clearForm(form.value);
            errors.value = {};
        });
    }

    network.init()
    
});
onUnmounted(() => {
    if (formModalInstance.value) {
        formModalInstance.value.dispose();
    }
});

// add or create
const openModal = () => {
    formModalInstance.value.show();
};

// submit form
const changePassword = () => {
    isLoading.value = true;
    axios.post("api/auth/change-password", form.value)
        .then((response) => {
            if (response.data.success) {
                formModalInstance.value.hide();
                messageBox.value.showModal(1, null, null, 'Your password has been changed successfully');
            } else {
                errors.value = response.data.errors;
                setFocus(autofocus);
            }
        })
        .catch((ex) => {
            console.log(ex);
            setFocus(autofocus);
        })
        .finally(() => {
            isLoading.value = false;
        });
};
</script>