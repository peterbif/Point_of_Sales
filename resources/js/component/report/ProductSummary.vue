<template>
  <div class="vl-parent">
    <Loading v-model:active="isLoading" :is-full-page="true" />
    <button type="button" class="btn btn-success" style="float: right" @click="exportToExcel">
      <i class="bi bi-file-earmark-excel"></i> Export to Excel
    </button>

    <div class="pagetitle">
      <h1>Product Summary</h1>
    </div>
    <section class="section">
      <div class="col">
        <div class="card">
          <div class="card-body">
            <form @submit.prevent="getData(true)">
              <div class="row pt-4">
                <div class="col-md-10">
                  <div class="row justify-content-start">
                    <div class="col-lg-3 col-sm-6">
                      <label class="form-label">Product Name</label>
                      <input type="text" class="form-control" v-model="filter.product_name" placeholder="Search..." />
                    </div>
                    <div class="col-lg-3 col-sm-6">
                      <label class="form-label">Product Category</label>
                      <select class="form-select" v-model="filter.product_category_id">
                        <option value="0">ALL</option>
                        <option v-for="data in productCategoryList" :key="data.id" :value="data.id">
                          {{ data.name }}
                        </option>
                      </select>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                      <label class="form-label">From Date</label>
                      <flat-pickr v-model="filter.from_date" class="form-control" :config="dateFilterConfig"
                        @change="onStartChange" />
                    </div>
                    <div class="col-lg-3 col-sm-6">
                      <label class="form-label">To Date</label>
                      <flat-pickr v-model="filter.to_date" class="form-control" :config="dateFilterConfig"
                        @change="onEndChange" />
                    </div>
                  </div>
                </div>
                <div class="col-md-2 align-self-end">

                  <button type="button" class="btn btn-secondary pt-1 mx-2 my-2" style="float: right" @click="clear">
                    <i class="bi bi-search"></i> Clear
                  </button>

                  <button type="submit" class="btn btn-secondary pt-1 my-2" style="float: right">
                    <i class="bi bi-search"></i> Search
                  </button>

                  
                </div>
              </div>
            </form>
            <hr class="text-secondary" />
            <table class="table table-striped">
              <thead>
                <tr class="table-dark">
                  <th style="width: 50px">#</th>
                  <th scope="col" @click="sortData('order_details.description')" style="cursor: pointer">
                    Product Name <i class="text-secondary"
                      :class="filter.sortBy == 'order_details.description' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                  <th scope="col" @click="sortData('category_name')" style="cursor: pointer">
                    Product Category <i class="text-secondary"
                      :class="filter.sortBy == 'category_name' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                  <th scope="col" @click="sortData('qty')" style="cursor: pointer" class="text-end">
                    Inventory <i class="text-secondary"
                      :class="filter.sortBy == 'qty' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                </tr>
              </thead>
              <tbody  v-if="dataList && dataList.data && dataList.data.length > 0">
                <tr v-for="(d, index) in dataList.data"
                  :key="d.id">
                  <th scope="row">{{ dataList.from + index }}</th>
                  <td>{{ d.description }}</td>
                  <td>{{ d.category_name }}</td>
                  <td class="text-end">{{ d.qty }}</td>
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
import { onMounted, ref } from 'vue';
import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
import { dateFilterConfig } from '../../helper';

const isLoading = ref(false);
const productCategoryList = ref([]);
const dataList = ref([]);
onMounted(() => {
  getProductCategoryList();
  getData(true);
});

// get product category list
const getProductCategoryList = () => {
  isLoading.value = true;
  axios.get("api/product/category-list").then((response) => {
    productCategoryList.value = response.data;
  })
    .catch((ex) => {
      console.log(ex);
    })
    .finally(() => {
      isLoading.value = false;
    });
};




const filter = ref(
  {
    product_name: null,
    product_category_id: 0,
    from_date: new Date(),
    to_date: new Date(),
    sortBy: 'qty',
    orderBy: 'desc',
    page: 1
  }
);


const clear = () =>{

filter.value.product_id = "";
filter.value.product_name = "";


filter.value.product_category_id = "";

filter.value.from_date = null;
filter.value.to_date = null;
 dataList.value = [];
summaryData.value = {};

}

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

const exportToExcel = () => {
  isLoading.value = true;
  axios.post("api/report/export-product-summary", filter.value, {
    responseType: 'blob' // REQUIRED!
  }).then((response) => {
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'Product Summary Report.xlsx');
    document.body.appendChild(link);
    link.click();
  })
    .catch((ex) => {
      console.log(ex);
    })
    .finally(() => {
      isLoading.value = false;
    });
};

const onStartChange = (selectedDates, dateStr, instance) => {
  dateFilterConfig.value.minDate = dateStr;
};

const onEndChange = (selectedDates, dateStr, instance) => {
  dateFilterConfig.value.maxDate = dateStr;
};

// load data
const getData = (resetPge = false) => {
  isLoading.value = true;
  if (resetPge)
    filter.value.page = 1;
  axios.post("api/report/product-summary", filter.value).then((response) => {
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

</script>