<template>
  <div class="vl-parent">
    <Loading v-model:active="isLoading" :is-full-page="true" color="blue" />
    <!-- Share Modal -->
    <ShareModal ref="messageBox"></ShareModal>

    <!-- Form Modal -->
    <div class="modal fade" ref="formModal" tabindex="-1" aria-hidden="true" data-bs-keyboard="false"
      data-bs-backdrop="static" data-bs-focus="false">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header py-2 bg-secondary text-light">
            <h5 class="modal-title" style="font-weight: bold">
              {{ form.id ? "Edit" : "Add" }} Product
            </h5>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveData" id="form">
              <div class="row">
                <div class="col-12 mb-3">
                  <label class="form-label">Image</label>
                  <div style="position: relative; width: 40%;" :class="{ 'is-invalid': errors.image }">
                    <i class="bi bi-x-circle fs-3 m-0 p-0 text-danger"
                      style="position: absolute; right: 5px;top: -2px; cursor: pointer;" @click.stop="removeImage"></i>
                    <img :src="form.image_preview" style="width: 100%;cursor: pointer;" class="img-thumbnail"
                      @click="upload" />
                  </div>
                  <span v-if="errors.image" class="invalid-feedback"> {{ errors.image[0] }} </span>
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label required">Name</label>
                  <input type="text" :class="['form-control', { 'is-invalid': errors.name }]" v-model="form.name"
                    ref="autofocus" />
                  <span v-if="errors.name" class="invalid-feedback"> {{ errors.name[0] }} </span>
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label required">Product Category</label>


                    <select :class="['form-select', { 'is-invalid': errors.product_category_id }]"
                    v-model="form.product_category_id" :disabled="form.processing">
                    <option v-for="(category, id) in productCategoryList" :key="category.id" :value="category.id">
                      {{ form.product_category_id === id && form.category_name
                     ? form.category_name
                    : category.name
                    }}
                    </option>


                  </select>
                  <span v-if="errors.product_category_id" class="invalid-feedback"> {{
                    errors.product_category_id[0] }} </span>
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label">Inventory</label>
                  <input type="text" :class="['form-control', { 'is-invalid': errors.order }]" v-model="form.inventory" />
                  <span v-if="errors.inventory" class="invalid-feedback"> {{ errors.inventory[0] }} </span>
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label required">Unit Price</label>
                  <div class="input-group" :class="{ 'is-invalid': errors.unit_price }">
                    <span class="input-group-text">&#8358;</span>
                    <input type="text" :class="['form-control', { 'is-invalid': errors.unit_price }]"
                      v-model="form.unit_price" :disabled="form.processing" />
                  </div>
                  <span v-if="errors.unit_price" class="invalid-feedback"> {{
                    errors.unit_price[0] }} </span>
                </div>
              
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="closeModal">
              <i class="bi bi-x-lg"></i> Cancel
            </button>
            <button type="submit" class="btn btn-primary px-3" form="form">
              <i class="bi bi-floppy" style="padding-right: 3px;"></i> Save
            </button>
          </div>
        </div>
      </div>
    </div>

    <!--Form Modal Quantity-->

    <!-- <div  class="modal fade" ref="formModalQuantity" tabindex="-2" aria-hidden="true" data-bs-keyboard="false"
      data-bs-backdrop="static" data-bs-focus="false">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header py-2 bg-secondary text-light">
            <h5 class="modal-title" style="font-weight: bold">
              Add more quantity
            </h5>
          </div>
          <div class="modal-body">
            <form id="form2" @submit.prevent="addInventory">
            
                <div class="col-12 mb-3">
                  <label class="form-label">Inventory</label>
                  <input type="text" :class="['form-control', { 'is-invalid': errors.order }]" v-model="form.inventory" />
                  <span v-if="errors.inventory" class="invalid-feedback"> {{ errors.inventory[0] }} </span>
                </div>
            
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="closeModal2">
              <i class="bi bi-x-lg"></i> Cancel
            </button>
            <button type="submit" class="btn btn-primary px-3" form="form2"  >
              <i class="bi bi-floppy" style="padding-right: 3px;"></i> Save
            </button>
          </div>
        </div>
      </div>
    </div> -->

    <!-- <button type="button" class="btn btn-primary" style="float: right" @click="openModal">
      <i class="bi bi-plus-circle"></i> Add New
    </button> -->

    <div class="pagetitle">
      <h1>Manage Order(s)</h1>
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
                      <label class="form-label">Receipt#</label>
                      <input type="text" class="form-control" v-model="filter.transaction_id" placeholder="Search..." />
                    </div>
                    <!-- <div class="col-lg-3 col-sm-6">
                      <label class="form-label">Status</label>
                      <select class="form-select" v-model="filter.product_category_id">
                        <option value="0">ALL</option>
                        <option v-for="data in productCategoryList" :key="data.id" :value="data.id">
                          {{ data.name }}
                        </option>
                      </select>
                    </div> -->
                  </div>
                </div>
                <div class="col-md-2 align-self-end">

                    
                  <button
                    type="button"
                    class="btn btn-secondary pt-1 mx-2 my-2"
                    style="float: right"
                    @click="clearFilter"
                  >
                    <i class="bi bi-trash3-fill"></i> Clear
                  </button>
                  <button type="submit" class="btn btn-secondary pt-1 my-2" style="float: right">
                    <i class="bi bi-search"></i> Search
                  </button>

                
                </div>
              </div>
            </form>
            <hr class="text-secondary" />
            <!-- Default Product -->
            <table class="table table-striped"  v-if="dataList?.length > 0">
              <thead>
                <tr class="table-dark">
                  <th scope="col" width="50px">#</th>
                  <th scope="col" width="100px">Image</th>
                  <th scope="col" @click="sortData('products.name')" style="cursor: pointer">
                    Product  <i class="text-secondary"
                      :class="filter.sortBy == 'products.name' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                
                  <th scope="col" @click="sortData('product_categories.name')" style="cursor: pointer">
                    Category <i class="text-secondary"
                      :class="filter.sortBy == 'product_categories.name' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                  <th class="text-center" scope="col" @click="sortData('inventory')" style="cursor: pointer">
                    Inventory <i class="text-secondary"
                      :class="filter.sortBy == 'order' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                  <th scope="col" @click="sortData('unit_price')" style="cursor: pointer">
                    Unit Price <i class="text-secondary"
                      :class="filter.sortBy == 'unit_price' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                  
                  <th scope="col" @click="sortData('unit_price')" style="cursor: pointer">
                    Cashier <i class="text-secondary"
                      :class="filter.sortBy == 'unit_price' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                
                  <th scope="col" @click="sortData('products.created_at')" style="cursor: pointer" width="200px">
                    Created Time <i class="text-secondary"
                      :class="filter.sortBy == 'products.created_at' ? (filter.orderBy == 'desc' ? 'bi bi-sort-alpha-down-alt' : 'bi bi-sort-alpha-down') : 'bi bi-arrow-down-up'"></i>
                  </th>
                  <th scope="col" width="100px">Action</th>

                </tr>
              </thead>
              
              <!-- <span>{{ dataList }}</span> -->
              <tbody  v-for="(d2) in dataList"
                :key="d2.id">
                <tr  v-for="(d , index) in d2.order_details" :key="d.id">
                  <th scope="row">{{  index+1 }}</th>
                 
                  <td>
                    <a :href="d?.product?.image ? ('storage/' + d?.product?.image ) : defaultImage" target="_blank">
                      <img :src="d?.product?.image ? ('storage/' + d?.product?.image ) : defaultImage" style="height: 40px;"
                        class="img-thumbnail" />``
                    </a>
                  </td>
                  <td>{{ d?.product?.name }}</td>

                  <td>{{ d?.product?.category?.name }}</td>
                  <td class="text-center"> {{ d?.qty }}   </td>

                  <td>{{ currencyFormat(d?.unit_price) }}</td>
                  <td>{{ d2?.user?.username }}</td>

                  <td>{{ dateFormat(d?.created_at) }}</td>
                  <td>
                    <i class="bi bi-trash3-fill pe-3 text-danger" role="button" @click="deleteData(d2?.id,  d?.product.name)"></i>
                    <!-- <i class="bi bi-pencil-square text-success" role="button" @click="editData(d.id)"></i> -->
                  </td>
                </tr>
             
              </tbody>

              </table>
          

              <table v-else>
        
              <tbody >
                <tr >
                  <td colspan="10" class="shadow-none">
                    No record found
                  </td>
                </tr>
              </tbody>
            
            </table>
            Pagination
            <div class="d-flex justify-content-end">
              <nav v-if="dataList?.links && dataList?.links?.length > 3">
                <ul class="pagination">
                  <li :class="['page-item', data.url ? '' : 'disabled', data.active ? 'active' : '']"
                    v-for="data in dataList?.links" :key="data.id">
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
import Swal from "sweetalert2";
import { onMounted, onUnmounted, ref } from 'vue';
import { Modal } from 'bootstrap';
import { clearForm, currencyFormat, dateFormat, setFocus } from '../../helper.js';
import ShareModal from '../share/Modal.vue';
import axios from 'axios';
import Barcode from './Barcode.vue'
import MessageBox from "./MessageBox.vue";

// import JsBarcode from "jsbarcode"

const props = defineProps({
  code: String
})

const isLoading = ref(false);
const formModalInstance = ref(null);
const formModalInstance2 = ref(null)
const formModal = ref(null);
const formModalQuantity = ref(null);
const messageBox = ref(null);
const autofocus = ref(null);
const productCategoryList = ref([]);
const defaultImage = "images/default.png";
const selectedId = ref(null);


//const showModalQuantity = ref(false)

//const resetPge = ref(false);
const form = ref(
  {
    id: null,
    name: null,
    product_category_id: null,
    unit_price: null,
    inventory: null,
    image: null,
    image_preview: defaultImage,
    image_remove: null,
    category_name:null,
  }
);
const filter = ref(
  {
    // name: null,
    transaction_id: null,
    // sortBy: null,
    // orderBy: null,
    // page: 1
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

  if (formModalQuantity.value) {
    formModalInstance2.value = new Modal(formModalQuantity.value);

   
    formModalQuantity.value.addEventListener("shown.bs.modal", () => {
      setFocus(autofocus);
    });
 
    formModalQuantity.value.addEventListener("hide.bs.modal", () => {
      document.activeElement?.blur();
    });

    formModalQuantity.value.addEventListener("hidden.bs.modal", () => {
      clearForm(form.value);
      errors.value = {};
    });
  }
  getData(true);
  // getProductCategoryList();
});
onUnmounted(() => {
  if (formModalInstance.value) {
    formModalInstance.value.dispose();
  }


  // if (formModalInstance2.value) {
  //   formModalInstance2.value.dispose();
  // }
});


const clearFilter = () => {
  filter.value.name = null;
  filter.value.product_category_id = null;
  dataList.value = [];
  filter.value.sortBy = null;
  filter.value.orderBy = null;
  filter.value.page = 1;
  filter.transaction_id = ""
};

// add or create
const openModal = () => {
  form.value.category_name = null;
  isLoading.value = false;
  form.value.image_preview = defaultImage;
  form.value.image_remove = null;
  formModalInstance.value.show();
};

const closeModal = () => {
  form.value.category_name = null;
  isLoading.value = false;
  form.value.image_preview = null;
  form.value.image_remove = null;
  formModalInstance.value = false;
  

};

const closeModal2 = () => {
  form.value.inventory = null;
  errors.value = {};
  isLoading.value = false;
  formModalQuantity.value = null;
  formModalInstance2.value = false;
};


// submit form
// const saveData = () => {
//   isLoading.value = true;
//   axios.post("api/product/save", form.value, {
//     headers: {
//       'Content-Type': 'multipart/form-data'
//     }
//   })
//     .then((response) => {
//       if (response.data.success) {
//         formModalInstance.value.hide();
//         messageBox.value.showModal(1);
//       //  resetPge.value = true;
//         getData(true);
//       } else {
//         errors.value = response.data.errors;
//         messageBox.value.showModal(errors.value);

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

const saveData = async () => {
  isLoading.value = true;
  errors.value = null;

  try {
    const response = await axios.post(
      "api/product/save",
      form.value,
      {
        headers: {
          "Content-Type": "multipart/form-data"
        }
      }
    );

    if (response?.data?.success) {
      formModalInstance.value?.hide();
      messageBox.value?.showModal(1);
      await getData(true);
    } else {
      errors.value = response?.data?.errors || ["Unknown error occurred"];
      messageBox.value?.showModal(errors.value);
      setFocus(autofocus);
    }

  } catch (error) {
    // Server responded with error status (4xx, 5xx)
    if (error.response) {
      console.error("Server Error:", error.response);

      if (error.response.status === 422) {
        errors.value = error.response.data.errors;
      } else if (error.response.status === 500) {
        errors.value = ["Internal server error. Please try again."];
      } else {
        errors.value = error.response.data?.message || ["Unexpected error occurred"];
      }

    }
    // Request made but no response (network error)
    else if (error.request) {
      console.error("Network Error:", error.request);
      errors.value = ["Network error. Please check your connection."];
    }
    // Something else happened
    else {
      console.error("Error:", error.message);
      errors.value = [error.message];
    }

    messageBox.value?.showModal(errors.value);
    setFocus(autofocus);

  } finally {
    isLoading.value = false;
  }
};




// const addInventory = async () => {
//   try {
//     isLoading.value = true;
//     errors.value = {};

//     const response = await axios.post(
//       "/api/product/addQuantity",
//       form.value
//     );

//     if (response.data.success) {
//       closeModal2();
//       getData(true); // refresh table
//     } else {
//       errors.value = response.data.errors || {};
//     }
//   } catch (error) {
//     if (error.response?.status === 422) {
//       errors.value = error.response.data.errors;
//     } else {
//       console.error(error);
//     }
//   } finally {
//     isLoading.value = false;
//   }
// };

// const addQuatity = (d) => {
//   form.value.id = d.id
//   formModalQuantity.value = new Modal(formModalQuantity.value);
//   formModalQuantity.value.show();
//  // showModalQuantity.value = true;
// }

// load data
const getData = (resetPge = false) => {
  if(!filter.value.transaction_id) return
  
  isLoading.value = true;
   if (resetPge){
     filter.value.page = 1;
  axios.post("api/orders/search", filter.value).then((response) => {
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
   }
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
  getData(true);
};

// sort
const sortData = (field) => {
  if (filter.value.sortBy === field) {
    filter.value.orderBy = filter.value.orderBy == 'asc' ? 'desc' : 'asc';
  } else {
    filter.value.sortBy = field;
    filter.value.orderBy = 'asc';
  }
  getData(true);
};

// edit
// const editData = (id) => {
//   form.value.category_name = null;
//   isLoading.value = true;
//   axios.get("api/product/edit/" + id).then((response) => {
//     Object.keys(form.value).forEach(key => {
//       if (key in response.data) {
//         form.value[key] = response.data[key];
//       }
//     });
//     form.value.image_preview = (form.value.image ? ('storage/' + form.value.image) : defaultImage);
//     form.value.image = null
//     form.value.image_remove = null;
//     form.value.category_name = response.data.category_name
//     formModalInstance.value.show();
//   })
//     .catch((ex) => {
//       console.log(ex);
//     })
//     .finally(() => {
//       isLoading.value = false;
//     });
// };

// delete

const deleteData = async (id, name) => {
  const result = await Swal.fire({
    title: "Are you sure?",
    text: `You are about to delete order  ${name}`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Yes, delete it!",
  });

  if (result.isConfirmed) {
    try {
      isLoading.value = true;

      await axios.delete(`api/orders/delete/${id}`);

      await Swal.fire({
        title: "Deleted!",
        text: `Item with ID ${id} has been deleted.`,
        icon: "success",
        timer: 1500,
        showConfirmButton: false,
      });

      getData(true);
    } catch (error) {
      console.error(error);

      Swal.fire({
        title: "Error!",
        text: "Failed to delete item.",
        icon: "error",
      });
    } finally {
      isLoading.value = false;
    }
  }
};

// upload file
const upload = () => {
  let acceptFileType = ['image/png', 'image/jpg', 'image/jpeg'];
  let input = document.createElement('input');
  input.type = 'file';
  input.accept = '.png,.jpg,.jpeg';
  input.onchange = _ => {
    let file = input.files[0];
    if (!acceptFileType.includes(file.type.toLocaleLowerCase())) {
      errors.value.image = 'Accept file type: png, jpg, jpeg';
      return;
    } else if (file.size > 1048576) {
      errors.value.image = 'File size must be less than 1mb';
      return;
    }
    form.value.image_preview = URL.createObjectURL(file);
    errors.value.image = null;
    form.value.image = file;
  };
  input.click();
};

const removeImage = () => {
  form.value.image_remove = 1;
  form.value.image = null;
  form.value.image_preview = defaultImage;
};
</script>