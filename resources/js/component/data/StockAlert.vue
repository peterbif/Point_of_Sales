<template>
  <div class="vl-parent">
    <Loading v-model:active="isLoading" :is-full-page="true" color="blue" />

    <ShareModal ref="messageBox"></ShareModal>

    <!-- Form Modal -->
    <div class="modal fade" ref="formModal" tabindex="-1" aria-hidden="true" data-bs-keyboard="false"
      data-bs-backdrop="static" data-bs-focus="false">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header py-2 bg-secondary text-light">
            <h5 class="modal-title" style="font-weight: bold">
              {{ form.id ? "Edit" : "Create" }} Product Category
            </h5>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveData" id="form">
              <div class="row">
                <div class="col-12 mb-3">
                  <label class="form-label required">Very Low Stock</label>
                  <input type="text" :class="['form-control', { 'is-invalid': errors.name }]" v-model="form.very_low_stock"
                    ref="autofocus" />
                  <span v-if="errors.very_low_stock" class="invalid-feedback"> {{ errors.very_low_stock[0] }} </span>
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label">Low Stock</label>
                  <input type="text" :class="['form-control', { 'is-invalid': errors.low_stock }]" v-model="form.low_stock" />
                  <span v-if="errors.low_stock" class="invalid-feedback"> {{ errors.low_stock[0] }} </span>
                </div>

                <div class="col-12 mb-3">
                  <label class="form-label">Stock Expiry Date Alert (Days)</label>
                  <input type="number" :class="['form-control', { 'is-invalid': errors.low_stock }]" v-model="form.stock_alert_days" />
                  <span v-if="errors.stock_alert_days" class="invalid-feedback"> {{ errors.stock_alert_days[0] }} </span>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="bi bi-x-lg"></i> Cancel
            </button>
            <button type="submit" class="btn btn-primary px-3" form="form" :disabled="store.isLoading">
              <i class="bi bi-floppy" style="padding-right: 3px;"></i>   {{ store.isLoading ? 'Saving...' : 'Save' }}

            </button>
          </div>
        </div>
      </div>
    </div>

    <button type="button" class="btn btn-primary" style="float: right" @click="openModal">
      <i class="bi bi-plus-circle"></i> Add New
    </button>

    <div class="pagetitle">
      <h1>Stock Alert</h1>
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
                      <label class="form-label">Very Low Stock</label>
                      <input type="text" class="form-control" v-model="filter.very_low_stock" placeholder="Search..." />
                    </div>

                    <div class="col-lg-3 col-sm-6">
                      <label class="form-label">Low Stock</label>
                      <input type="text" class="form-control" v-model="filter.low_stock" placeholder="Search..." />
                    </div>
                  </div>
                </div>
                <div class="col-md-2 align-self-end">
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
                  
                  <th scope="col" @click="sortData('very_low_stock')" style="cursor: pointer">
                    Very Low Stock Alert <i class="text-secondary"
                      :class="filter.sortBy == 'name' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                  <th scope="col" @click="sortData('low_stock')" style="cursor: pointer">
                    Low Stock Alert <i class="text-secondary"
                      :class="filter.sortBy == 'order' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                  <th scope="col" @click="sortData('expiry_date')" style="cursor: pointer">
                     Stock Expiry Date Alert (months) <i class="text-secondary"
                      :class="filter.sortBy == 'order' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                  <th scope="col" @click="sortData('created_at')" style="cursor: pointer" >
                    Created Time <i class="text-secondary"
                      :class="filter.sortBy == 'created_at' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                  <th scope="col" width="100px">
                    Action</th>
                </tr>
              </thead>
              <tbody v-if="dataList?.data?.length > 0" >
                <tr v-for="(d, index) in dataList.data"
                  :key="d.id">
                  <th scope="row">{{ dataList.from + index }}</th>
                
                  <td>{{ d?.very_low_stock }}</td>
                  <td>{{ d?.low_stock }}</td>
                  <td>{{ d?.stock_alert_days }}</td>

                  <td>{{ dateFormat(d.created_at) }}</td>
                  <td>
                    <i class="bi bi-trash3-fill pe-3 text-danger" role="button" @click="deleteData(d.id)"></i>
                    <i class="bi bi-pencil-square text-success" role="button" @click="editData(d)"></i>
                  </td>
                </tr>
             
              </tbody>

              <tbody v-else>
                <tr >
                  <td colspan="10" class="shadow-none">
                    No record found
                  </td>
                </tr>
              </tbody>
            </table>
            <!-- Pagination -->
            <div class="d-flex justify-content-end">
              <nav v-if="dataList.links && dataList.links.length > 3">
                <ul class="pagination">
                  <li :class="['page-item', data.url ? '' : 'disabled', data.active ? 'active' : '']"
                    v-for="data in dataList.links" :key="data">
                    <span class="page-link" style="cursor: pointer" v-html="data.label" v-if="data.url && !data.active"
                      @click="paginate(data.url.substring(data.url.lastIndexOf('?page=') + 6))"></span>
                    <span class="page-link" v-html="data.label" v-else></span>
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
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { Modal } from 'bootstrap';
import { clearForm, dateFormat, setFocus } from '../../helper.js';
import ShareModal from '../share/Modal.vue';
import axios from 'axios';
import { useStocktore } from "@/store/stockalerts";

const isLoading = ref(false);
const formModalInstance = ref(null);
const formModal = ref(null);
const messageBox = ref(null);
const autofocus = ref(null);
const form = ref(
  {
    id: null,
    very_low_stock: null,
    low_stock:null,
    stock_alert_days:null,
  }
);
const filter = ref(
  {
    very_low_stock: null,
    sortBy: null,
    low_stock:null,
    stock_alert_days:null,
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

// add or create
const openModal = () => {
  isLoading.value = false;
  formModalInstance.value.show();
};

// submit form
// const saveData = () => {
//   isLoading.value = true;
//   axios[form.value.id > 0 ? "put" : "post"]("api/product-category/save", form.value)
//     .then((response) => {
//       if (response.data.success) {
//         formModalInstance.value.hide();
//         messageBox.value.showModal(1);
//         getData();
//       } else {
//         errors.value = response.data.errors;
//         setFocus(autofocus);
//       }
//     })
//     .catch((ex) => {
//       console.log(ex);
//       setFocus(autofocus);
//     })
//     .finally(() => {
//       isLoading.value = false;
//     });
// };

const store = useStocktore();

const saveData = async (e) => {
  e.preventDefault();
  const result = await store.saveData(form.value);

  if (result.success) {
    formModalInstance.value.hide();
    messageBox.value.showModal(1);
    getData();
  } else {
    errors.value = result.errors || {};
    setFocus(autofocus);
  }
};

// load data
const getData = (resetPge = false, e) => {
  // e.preventDefault();

  isLoading.value = true;
  if (resetPge)
    filter.value.page = 1;
  axios.post("api/stock-alerts/list", filter.value).then((response) => {
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
const editData = async (data) => {
  isLoading.value = true;

  try {
    
    

    // If backend uses different naming
    form.value.very_low_stock = data.very_low_stock ?? null;
    form.value.low_stock = data.low_stock ?? null; 
    form.value.stock_alert_days = data.stock_alert_days ?? null; 

     form.value.id = data.id

    formModalInstance.value.show();

  } catch (ex) {
    console.error(ex);
  } finally {
    isLoading.value = false;
  }
};

// delete
const deleteData = (id) => {
  messageBox.value.showModal(4, () => {
    isLoading.value = true;
    axios.delete("api/stock-alerts/delete/" + id).then(() => {
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