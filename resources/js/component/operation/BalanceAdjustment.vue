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
              {{ form.id ? "Edit" : "Create" }} Balance Adjustment
            </h5>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveData" id="form">
              <div class="row">
                <div class="col-12 mb-3">
                  <label class="form-label required">Amount</label>
                  <div class="input-group" :class="{ 'is-invalid': errors.amount }">
                    <span class="input-group-text">$</span>
                    <input type="text" :class="['form-control', { 'is-invalid': errors.amount }]" v-model="form.amount"
                      ref="autofocus" />
                  </div>
                  <span v-if="errors.amount" class="invalid-feedback"> {{
                    errors.amount[0] }} </span>
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label required">Adjustment Type</label>
                  <select :class="['form-select', { 'is-invalid': errors.type_id }]" v-model="form.type_id">
                    <option v-for="(value, key) in typeList" :key="key" :value="key">
                      {{ value }}
                    </option>
                  </select>
                  <span v-if="errors.type_id" class="invalid-feedback"> {{ errors.type_id[0] }}
                  </span>
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label required">Adjustment Date</label>
                  <flat-pickr v-model="form.adjustment_date" class="form-control" :config="dateConfig" />
                  <span v-if="errors.adjustment_date" class="invalid-feedback"> {{
                    errors.adjustment_date[0] }} </span>
                </div>

                <div class="col-12 mb-3">
                  <label class="form-label required">Remark</label>
                  <input type="text" :class="['form-control', { 'is-invalid': errors.remark }]" v-model="form.remark" />
                  <span v-if="errors.remark" class="invalid-feedback"> {{
                    errors.remark[0] }} </span>
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

    <button type="button" class="btn btn-primary" style="float: right" @click="openModal">
      <i class="bi bi-plus-circle"></i> Add New
    </button>

    <div class="pagetitle">
      <h1>Balance Adjustment</h1>
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
                      <label class="form-label">Remark</label>
                      <input type="text" class="form-control" v-model="filter.remark" placeholder="Search..." />
                    </div>
                    <div class="col-lg-3 col-sm-6">
                      <label class="form-label">Type</label>
                      <select class="form-select" v-model="filter.type_id">
                        <option value="0">ALL</option>
                        <option v-for="(value, key) in typeList" :key="key" :value="key">
                          {{ value }}
                        </option>
                      </select>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                      <label class="form-label">From Date</label>
                      <flat-pickr v-model="filter.from_date" class="form-control" :config="dateConfig"
                        @change="onStartChange" />
                    </div>
                    <div class="col-lg-3 col-sm-6">
                      <label class="form-label">To Date</label>
                      <flat-pickr v-model="filter.to_date" class="form-control" :config="dateConfig"
                        @change="onEndChange" />
                    </div>
                  </div>
                </div>
                <div class="col-md-2 align-self-end">
                  <button type="submit" class="btn btn-secondary pt-1" style="float: right">
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
                  <th scope="col" width="100px"> Action</th>
                  <th scope="col" @click="sortData('type_id')" style="cursor: pointer">
                    Type <i class="text-secondary"
                      :class="filter.sortBy == 'type_id' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                  <th scope="col" @click="sortData('amount')" style="cursor: pointer">
                    Amount <i class="text-secondary"
                      :class="filter.sortBy == 'amount' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                  <th scope="col" @click="sortData('remark')" style="cursor: pointer">
                    Remark <i class="text-secondary"
                      :class="filter.sortBy == 'remark' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                  <th scope="col" @click="sortData('adjustment_date')" style="cursor: pointer">
                    Adjustment Date <i class="text-secondary"
                      :class="filter.sortBy == 'adjustment_date' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                  <th scope="col" @click="sortData('created_at')" style="cursor: pointer" width="200px">
                    Created Time <i class="text-secondary"
                      :class="filter.sortBy == 'created_at' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="dataList && dataList.data && dataList.data.length > 0" v-for="(d, index) in dataList.data"
                  :key="d.id">
                  <th scope="row">{{ dataList.from + index }}</th>
                  <td>
                    <i class="bi bi-trash3-fill pe-3 text-danger" role="button" @click="deleteData(d.id)"></i>
                    <i class="bi bi-pencil-square text-success" role="button" @click="editData(d.id)"></i>
                  </td>
                  <td class="text-capitalize"
                    :class="{ 'text-danger': d.type_id == 2, 'text-success': d.type_id == 1 }">
                    {{ typeList[d.type_id] }}</td>
                  <td>{{ currencyFormat(d.amount) }}</td>
                  <td>{{ d.remark }}</td>
                  <td>{{ dateFormat(d.adjustment_date, true) }}</td>
                  <td>{{ dateFormat(d.created_at) }}</td>
                </tr>
                <tr v-else>
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
                    v-for="data in dataList.links">
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
import { onMounted, onUnmounted, ref } from 'vue';
import { Modal } from 'bootstrap';
import { clearForm, currencyFormat, dateFormat, setFocus } from '../../helper.js';
import ShareModal from '../share/Modal.vue';
import axios from 'axios';
import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';

const isLoading = ref(false);
const formModalInstance = ref(null);
const formModal = ref(null);
const messageBox = ref(null);
const autofocus = ref(null);
const typeList = ref({ "1": "Credit (+)", "2": "Debit (-)" });

const form = ref(
  {
    id: null,
    type_id: null,
    adjustment_date: Date(),
    amount: null,
    remark: null
  }
);
const filter = ref(
  {
    remark: null,
    type_id: 0,
    from_date: null,
    to_date: null,
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

// add or create
const openModal = () => {
  isLoading.value = false;
  formModalInstance.value.show();
};

// submit form
const saveData = () => {
  isLoading.value = true;
  axios[form.value.id > 0 ? "put" : "post"]("api/balance-adjustment/save", form.value)
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
  axios.post("api/balance-adjustment/list", filter.value).then((response) => {
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
  axios.get("api/balance-adjustment/edit/" + id).then((response) => {
    Object.keys(form.value).forEach(key => {
      if (key in response.data) {
        form.value[key] = response.data[key];
      }
    });
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
    axios.delete("api/balance-adjustment/delete/" + id).then(() => {
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

const dateConfig = ref({
  wrap: true,
  altFormat: "d-M-Y",
  altInput: true,
  dateFormat: "Y-m-d",
  enableTime: false,
  defaultHour: "00",
  time_24hr: true,
});

const onStartChange = (selectedDates, dateStr, instance) => {
  dateConfig.value.minDate = dateStr;
};

const onEndChange = (selectedDates, dateStr, instance) => {
  dateConfig.value.maxDate = dateStr;
};

</script>