<template>
  <div class="vl-parent">
    <Loading v-model:active="isLoading" :is-full-page="true" />
    <div class="pagetitle">
      <h1>Sale Summary</h1>
    </div>
    <section class="section">
      <div class="col">
        <div class="card">
          <div class="card-body">
            <form @submit.prevent="getData">
              <div class="row pt-4">
                <div class="col-md-10">
                  <class class="row justify-content-start">
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
                  
                    <div class="col-lg-3 col-sm-6">
                      <label class="form-label">Product </label>

                        <select class="form-select" v-model="filter.product_id">
                           <option disabled value="">Select Product</option>
                           <option  value="all">All Products</option>

                        <option
                          v-for="product in productStore.products"
                             :key="product.id"
                              :value="product.id"
                                    >
                                                {{ product.name }}
                                              </option>
                                            </select>
                  </div>
                  <div class="col-lg-3 col-sm-6">
                      <label class="form-label">Product Category </label>

                        <select class="form-select" v-model="filter.category_id">
                           <option disabled value="">Select Category</option>
                        <option
                          v-for="category in productCategoryStore.productCategories"
                             :key="category.id"
                              :value="category.id"
                                    >
                                                {{ category.name }}
                                              </option>
                                            </select>
                  </div>
                  </class>
                </div>
                <div class="col-md-2 align-self-end">
                  <button type="submit" class="btn btn-secondary pt-1 mx-2 my-2" style="float: right">
                    <i class="bi bi-search"></i> Search
                  </button>
                  <button type="button" class="btn btn-secondary pt-1 my-2" style="float: right" @click="clear">
                    <i class="bi bi-search"></i> Clear
                  </button>
                </div>
              </div>
            </form>
            <hr class="text-secondary" />
            <table class="table shadow mb-4">
              <thead>
                <tr class="table-dark">
                  <th class="text-center">
                    Inventory
                  </th>
                  <th class="text-center">
                    Total Amount
                  </th>
                  <th class="text-center">
                    Total Discount
                  </th>
                  <th class="text-center">
                    Net Amount
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr class="fs-4">
                  <th class="text-center text-primary">
                    {{ summaryData.inventory }}
                  </th>
                  <th class="text-center text-primary">
                    {{ currencyFormat(summaryData.grand_total) }}
                  </th>
                  <th class="text-center text-danger">
                    {{ currencyFormat(summaryData.total_discount) }}
                  </th>
                  <th class="text-center text-success">
                    {{ currencyFormat(summaryData.net_amount) }}
                  </th>
                </tr>
              </tbody>
            </table>
            <table class="table shadow">
              <thead>
                <tr class="table-dark">
                  <th >{{name}}</th>
                  <th class="text-end" width="300px">Inventory</th>


                  <th class="text-end" width="300px">Total Amount</th>
                  <th class="text-end" width="300px">Total Discount</th>
                  <th class="text-end" width="300px">Net Amount</th>

                </tr>
              </thead>
              <tbody v-if="dataList.length > 0">
                <tr  v-for="d in dataList" :key="d.id">
                  <td>{{ d.label }}</td>
                  <th class="text-end" width="300px">{{d.inventory}}</th>

                  <td class="text-end">{{ currencyFormat(d.total) }}</td>
                  <td class="text-end">{{ currencyFormat(d.discount) }}</td>
                  <td class="text-end">{{ currencyFormat(d.total - d.discount) }}</td>
                </tr>
              
              </tbody>

              <tbody v-else>  <tr >
                  <td colspan="10" class="shadow-none">
                    No record found
                  </td>
                </tr></tbody>
            </table>
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
import { currencyFormat, dateFilterConfig } from '../../helper.js';
import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
import { useProductStore } from '../../store/product.js'


const isLoading = ref(false);
const filter = ref(
  {
    from_date: new Date(),
    to_date: new Date(),
    product_id:"",
    category_id:"",
  }
);
const dataList = ref([]);
const summaryData = ref({});

const productStore = useProductStore()
const productCategoryStore = useProductStore()

const name = ref("Product");

const clear = () =>{

  filter.value.product_id = "";

  filter.value.category_id = "";

  filter.value.from_date = null;
  filter.value.to_date = null;
   dataList.value = [];
  summaryData.value = {};
  
}


onMounted(() => {
  summaryData.value = {
    grand_total: 0,
    total_discount: 0,
    net_amount: 0,
    inventory:0,
  };

  getData();
  productStore.getAllProducts();

  productCategoryStore.getAllProductCategories();
});

const onStartChange = (selectedDates, dateStr, instance) => {
  dateFilterConfig.value.minDate = dateStr;
};

const onEndChange = (selectedDates, dateStr, instance) => {
  dateFilterConfig.value.maxDate = dateStr;
};

// load data
const getData = () => {

  if(filter.value.product_id){

    name.value = "Product";
  }

 if(filter.value.category_id){

   name.value = "Product category";
}
  isLoading.value = true;
  axios.post("api/report/sale-summary", filter.value).then((response) => {
    if (response.data.success) {
      dataList.value = response.data.data;
      summaryData.value = response.data.data.reduce(
        (sum, item) => {
          sum.grand_total += Number(item.total) ?? 0;
          sum.total_discount += Number(item.discount) ?? 0;
          sum.inventory += Number(item.inventory) ?? 0;

          
          return sum;
        },
        { grand_total: 0, total_discount: 0 ,  inventory:0,}
      );
      summaryData.value.net_amount = (summaryData.value.grand_total - summaryData.value.total_discount) ?? 0;
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