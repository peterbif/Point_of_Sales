<template>
  <div class="vl-parent">
    <Loading v-model:active="isLoading" :is-full-page="true" color="blue" />

    <ShareModal ref="messageBox"></shareModal>

    <!-- Form Modal -->
    <div class="modal fade" ref="formModal" tabindex="-1" aria-hidden="true" data-bs-keyboard="false"
      data-bs-backdrop="static" data-bs-focus="false">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header py-2 bg-secondary text-light">
            <h5 class="modal-title" style="font-weight: bold">
              {{ form.id ? "Edit" : "Create" }} User
            </h5>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveData" id="form">
              <div class="row">
                <div class="col-12 mb-3">
                  <label class="form-label required">Username</label>
                  <input type="text" :class="['form-control', { 'is-invalid': errors.username }]"
                    :disabled="form.id > 0" v-model="form.username" ref="autofocus" />
                  <span v-if="errors.username" class="invalid-feedback"> {{ errors.username[0] }} </span>
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label required">Email</label>
                  <input type="text" :class="['form-control', { 'is-invalid': errors.username }]"
                    :disabled="form?.id > 0" v-model="form.email" ref="autofocus" />
                  <span v-if="errors?.username" class="invalid-feedback"> {{ errors?.email[0] }} </span>
                </div>
                <div class="mb-3">
                  <div class="row">
                    <div class="col-9">
                      <label for="role" class="form-label">Role</label>
                      <select id="role" name="role"
                        :class="['form-select text-capitalize', { 'is-invalid': errors.role }]" v-model="form.role">
                        <option v-for="role in ['admin', 'cashier', 'superadmin']" :key="role">
                          {{ role }}
                        </option>
                      </select>
                      <span v-if="errors.role" class="invalid-feedback"> {{ errors.role[0] }} </span>
                    </div>
                    <div class="col-3">
                      <label for="active" class="form-label">Active</label>
                      <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="active" name="active"
                          style="cursor: pointer;" v-model="form.active" />
                      </div>
                    </div>
                  </div>
                </div>
                <div :class="['mb-3', { 'required': form.id == 0 }]">
                  <label for="password" class="form-label">Password</label>
                  <input id="password" name="password" type="password" v-model="form.password"
                    :class="['form-control', { 'is-invalid': errors.password }]" />
                  <span v-if="errors.password" class="invalid-feedback"> {{ errors.password[0] }} </span>
                </div>
                <div class="mb-3">
                  <label for="password_confirmation" class="form-label">Confirm Password</label>
                  <input id="password_confirmation" name="password_confirmation" type="password" class="form-control"
                    v-model="form.password_confirmation" />
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="bi bi-x-lg"></i> Cancel
            </button>
            <button type="submit" class="btn btn-primary px-3" form="form">
              <i class="bi bi-floppy" style="padding-right: 3px;"></i> Save
            </button>
          </div>
        </div>
      </div>
    </div>

    <button type="button" class="btn btn-primary" style="float: right" @click="openModal" v-if="auth.user?.role == 'superadmin' || auth.user?.role == 'superadmin2'">
      <i class="bi bi-plus-circle"></i> Add New
    </button>

    <div class="pagetitle">
      <h1>User</h1>
    </div>
    <section class="section">
      <div class="col">
        <div class="card">
          <div class="card-body">
            <!-- Filter -->
            <form @submit.prevent="getData(true)">
              <div class="row pt-4">
                <div class="col-md-10">
                  <div class="row justify-content-start">
                    <div class="col-lg-3 col-sm-6">
                      <label class="form-label">Username</label>
                      <input type="text" class="form-control" v-model="filter.username" placeholder="Search..." />
                    </div>
                    <div class="col-lg-3 col-sm-6">
                      <label class="form-label">Email</label>
                      <input type="text" class="form-control" v-model="filter.email" placeholder="Search..." />
                    </div>
                    <div class="col-lg-3 col-sm-6">
                      <label class="form-label">Role</label>
                      <select class="form-select text-capitalize" v-model="filter.role">
                        <option value="0">ALL</option>
                        <option v-for="role in ['admin', 'cashier', 'superadmin']" :key="role">
                          {{ role }}
                        </option>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="col-md-2 align-self-end">


                  <button type="submit" class="btn btn-secondary pt-1 mx-2 my-2" style="float: right" @click="clear">
                    <i class="bi bi-trash-fill"></i> Clear
                  </button>

                  <button type="submit" class="btn btn-secondary pt-1 my-2" style="float: right">
                    <i class="bi bi-search"></i> Search
                  </button>

                </div>
              </div>
            </form>
            <hr class="text-secondary" />
            <!-- Data List -->
            <table class="table table-striped">
              <thead>
                <tr class="table-dark">
                  <th scope="col" width="50px">#</th>
               
                  <th scope="col" @click="sortData('username')" style="cursor: pointer">
                    Username <i class="text-secondary"
                      :class="filter.sortBy == 'username' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                  <th scope="col" @click="sortData('email')" style="cursor: pointer">
                    Email <i class="text-secondary"
                      :class="filter.sortBy == 'email' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                  <th scope="col" @click="sortData('role')" style="cursor: pointer">
                    Role <i class="text-secondary"
                      :class="filter.sortBy == 'role' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                  <th class="text-center" scope="col" @click="sortData('active')" style="cursor: pointer">
                    Active <i class="text-secondary"
                      :class="filter.sortBy == 'active' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                  <th scope="col" @click="sortData('created_at')" style="cursor: pointer" width="200px">
                    Created Time <i class="text-secondary"
                      :class="filter.sortBy == 'created_at' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                  <th scope="col" width="100px" v-if="auth.user?.role == 'superadmin' || auth.user?.role == 'superadmin2'">
                    Action</th>
                </tr>
              </thead>
              <tbody v-if="dataList?.data?.length > 0">
                <tr  v-for="(d, index) in dataList?.data"
                  :key="d.id">
                  <th scope="row">{{ dataList.from + index }}</th>
               
                  <td>{{ d.username }}</td>
                  <td>{{ d.email }}</td>

                  <td class="text-capitalize">{{ d.role }}</td>
                  <td class="text-center">
                    <i
                      :class="['bi fs-3', { 'bi-toggle2-on text-success': d.active, 'bi-toggle2-off text-danger': !d.active }]"></i>
                  </td>
                  <td>{{ dateFormat(d.created_at) }}</td>
                  <td v-if="auth.user?.role == 'superadmin' || auth.user?.role == 'superadmin2'">
                    <i class="bi bi-pencil-square text-success mx-2" role="button" @click="editData(d.id)"></i>

                    <i class="bi bi-trash3-fill pe-3 text-danger" role="button" @click="deleteData(d.id)"></i>
                  </td>
                </tr>
               
              </tbody>

              <tbody  v-else>
                <tr>
                  <td colspan="10" class="shadow-none">
                    No record found
                  </td>
                </tr>
              </tbody>
            </table>
            <!-- Pagination -->
            <div class="d-flex justify-content-end">
              <nav v-if="dataList?.links?.length > 3">
                <ul class="pagination">
                  <li :class="['page-item', data.url ? '' : 'disabled', data.active ? 'active' : '']"
                    v-for="data in dataList?.links" :key="data.id">
                    <span class="page-link" style="cursor: pointer" v-html="data?.label" v-if="data?.url && !data?.active"
                      @click="paginate(data?.url.substring(data.url.lastIndexOf('?page=') + 6))"></span>
                    <span class="page-link" v-html="data?.label" v-else></span>
                  </li>
                </ul>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/css/index.css';
import { onMounted, onUnmounted, ref } from 'vue';
import { Modal } from 'bootstrap';
import { clearForm, dateFormat, setFocus } from '../../helper.js';
import ShareModal from '../share/Modal.vue';
import axios from 'axios';
import { useAuthStore } from '@/store/auth';


const isLoading = ref(false);
const formModalInstance = ref(null);
const formModal = ref(null);
const messageBox = ref(null);
const autofocus = ref(null);

const auth = useAuthStore();

const form = ref(
  {
    id: null,
    username: null,
    email:null,
    role: null,
    active: true,
    password: null,
    password_confirmation: null
  }
);
const filter = ref(
  {
    username: null,
    email:null,
    role: null,
    sortBy: null,
    orderBy: null,
    page: 1
  }
);
const dataList = ref([]);
const errors = ref({});
onMounted(() => {
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
  getData(true);
});
onUnmounted(() => {
  if (formModalInstance.value) {
    formModalInstance.value.dispose();
  }
});

//form

const clear = (e) => {
  e.preventDefault();
  filter.value.email = null;
  filter.value.username = null;
  filter.value.role = [];
  dataList.value = [];
};

// add or create
const openModal = () => {
  isLoading.value = false;
  formModalInstance.value.show();
};

// submit form
const saveData = () => {
  isLoading.value = true;
  axios[form.value.id > 0 ? "put" : "post"]("api/user/save", form.value)
    .then((response) => {
      if (response.data.success) {
        formModalInstance.value.hide();
        messageBox.value.showModal(1);
        getData();
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

// load data
const getData = (resetPge = false) => {
  isLoading.value = true;
  if (resetPge)
    filter.value.page = 1;
  axios.post("api/user/list", filter.value).then((response) => {
    if (response.data.success) {
      dataList.value = response.data.data;
    }
  })
    .catch((ex) => {
      console.log(ex);
    })
    .finally(() => {
      isLoading.value = false;
    });
};

// Pagination
const paginate = (page_number) => {
  filter.value.page = page_number;
  if (page_number > dataList.last_page) {
    filter.value.page = dataList.last_page;
  }
  if (page_number <= 0) {
    filter.value.page = 1;
  }
  getData();
};

// sort
const sortData = (field) => {
  if (filter.value.sortBy === field) {
    filter.value.orderBy = filter.value.orderBy == 'asc' ? 'desc' : 'asc';
  } else {
    filter.value.sortBy = field;
    filter.value.orderBy = 'asc';
  }
  getData();
};

// edit
const editData = (id) => {
  isLoading.value = true;
  axios.get("api/user/edit/" + id).then((response) => {
    Object.keys(form.value).forEach(key => {
      if (key in response.data) {
        form.value[key] = response.data[key];
      }
    });
    form.value.active = form.value.active ? true : false;
    formModalInstance.value.show();
  })
    .catch((ex) => {
      console.log(ex);
    })
    .finally(() => {
      isLoading.value = false;
    });
};

// delete
const deleteData = (id) => {
  messageBox.value.showModal(4, () => {
    isLoading.value = true;
    axios.delete("api/user/delete/" + id).then(() => {
      getData(true);
    })
      .catch((ex) => {
        console.log(ex);
      })
      .finally(() => {
        isLoading.value = false;
      });
  });
};

</script>