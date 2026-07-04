<template>
  <div class="vl-parent">
    <Loading v-model:active="isLoading" :is-full-page="true" color="blue" />
    <!-- Share Modal -->
    <ShareModal ref="messageBox"></ShareModal>

    <!-- Product Form Modal -->
    <div
      class="modal fade" id="exampleModal33"     ref="productModal"
      tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel33" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header py-2 bg-secondary text-light">
            <h5 class="modal-title" id="exampleModalLabel33" style="font-weight: bold">
              {{ form.id ? "Edit" : "Add" }} Product
            </h5>
          </div>
          <div class="modal-body">
            <form id="form">
              <div class="row">
                <div class="col-12 mb-3">
                  <label class="form-label">Image</label>
                  <div
                    style="position: relative; width: 40%"
                    :class="{ 'is-invalid': errors.image }"
                  >
                    <i
                      class="bi bi-x-circle fs-3 m-0 p-0 text-danger"
                      style="
                        position: absolute;
                        right: 5px;
                        top: -2px;
                        cursor: pointer;
                      "
                      @click.stop="removeImage"
                    ></i>
                    <img
                      :src="form.image_preview"
                      style="width: 100%; cursor: pointer"
                      class="img-thumbnail"
                      @click="upload"
                    />
                  </div>
                  <span v-if="errors.image" class="invalid-feedback">
                    {{ errors.image[0] }}
                  </span>
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label required">Name</label>
                  <input
                    type="text"
                    :class="['form-control', { 'is-invalid': errors.name }]"
                    v-model="form.name"
                    ref="autofocus"
                  />
                  <span v-if="errors.name" class="invalid-feedback">
                    {{ errors.name[0] }}
                  </span>
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label">Barcode</label>
                  <input
                    type="text"
                    :class="['form-control', { 'is-invalid': errors.barcode }]"
                    v-model="form.barcode"
                    ref="autofocus"
                  />
                  <span v-if="errors.barcode" class="invalid-feedback">
                    {{ errors.barcode[0] }}
                  </span>
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label required">Expiry Date</label>
                  <flat-pickr
                    v-model="form.expiry_date"
                    :class="['form-control', { 'is-invalid': errors.name }]"
                    :config="dateConfig"
                  />
                  <span v-if="errors.expiry_date" class="invalid-feedback">
                    {{ errors.expiry_date[0] }}
                  </span>
                </div>

                <div class="col-12 mb-3">
                  <label class="form-label required">Stock Alert Days </label>
                  <div
                    class="input-group"
                    :class="{ 'is-invalid': errors.unit_price }"
                  >
                    <span class="input-group-text">QTY</span>
                    <input
                      type="number"
                      :class="[
                        'form-control',
                        { 'is-invalid': errors.stock_alert_days },
                      ]"
                      v-model="form.stock_alert_days"
                      :disabled="form.processing"
                    />
                  </div>
                  <span v-if="errors.stock_alert_days" class="invalid-feedback">
                    {{ errors.stock_alert_days[0] }}
                  </span>
                </div>


                <div class="col-12 mb-3">
                  <label class="form-label required">Stock Alert (Very Low)</label>
                  <div
                    class="input-group"
                    :class="{ 'is-invalid': errors.unit_price }"
                  >
                    <span class="input-group-text">QTY</span>
                    <input
                      type="number"
                      :class="[
                        'form-control',
                        { 'is-invalid': errors.stock_alert_qty_very_low },
                      ]"
                      v-model="form.stock_alert_qty_very_low"
                      :disabled="form.processing"
                    />
                  </div>
                  <span v-if="errors.wholesales_price" class="invalid-feedback">
                    {{ errors.stock_alert_qty_very_low[0] }}
                  </span>
                </div>



                <div class="col-12 mb-3">
                  <label class="form-label required">Stock Alert (Low)</label>
                  <div
                    class="input-group"
                    :class="{ 'is-invalid': errors.unit_price }"
                  >
                    <span class="input-group-text">QTY</span>
                    <input
                      type="number"
                      :class="[
                        'form-control',
                        { 'is-invalid': errors.stock_alert_qty_low },
                      ]"
                      v-model="form.stock_alert_qty_low"
                      :disabled="form.processing"
                    />
                  </div>
                  <span v-if="errors.stock_alert_qty_low" class="invalid-feedback">
                    {{ errors.stock_alert_qty_low[0] }}
                  </span>
                </div>




                <div class="col-12 mb-3">
                  <label class="form-label required">Product Category</label>
                  <select
                    :class="[
                      'form-select',
                      { 'is-invalid': errors.product_category_id },
                    ]"
                    v-model="form.product_category_id"
                    :disabled="form.processing"
                  >
                    <option value="new">New Category</option>
                    <option
                      v-for="(category, id) in productCategoryList"
                      :key="category.id"
                      :value="category.id"
                    >
                      {{
                        form.product_category_id === id && form.category_name
                          ? form.category_name
                          : category.name
                      }}
                    </option>
                  </select>
                  <span
                    v-if="errors.product_category_id"
                    class="invalid-feedback"
                  >
                    {{ errors.product_category_id[0] }}
                  </span>
                </div>

                <div
                  class="col-12 mb-3"
                  v-if="form.product_category_id == 'new'"
                >
                  <label class="form-label">Create New Category</label>
                  <input
                    type="text"
                    :class="['form-control', { 'is-invalid': errors.barcode }]"
                    v-model="form.category_name"
                    ref="autofocus"
                  />
                  <span v-if="errors.category_name" class="invalid-feedback">
                    {{ errors.category_name[0] }}
                  </span>
                  <!-- FIXED -->
                  <div class="d-flex justify-content-end mt-2">
                    <button
                      class="btn btn-primary btn-sm"
                      :disabled="store.isLoading"
                      @click="saveCategoryProduct"
                    >
                      {{ store.isLoading ? "Saving..." : "Save" }}
                    </button>
                  </div>
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label">Inventory</label>
                  <input
                    type="text"
                    :class="['form-control', { 'is-invalid': errors.order }]"
                    v-model="form.inventory"
                  />
                  <span v-if="errors.inventory" class="invalid-feedback">
                    {{ errors.inventory[0] }}
                  </span>
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label required">Unit Price</label>
                  <div
                    class="input-group"
                    :class="{ 'is-invalid': errors.unit_price }"
                  >
                    <span class="input-group-text">&#8358;</span>
                    <input
                      type="number"
                      :class="[
                        'form-control',
                        { 'is-invalid': errors.unit_price },
                      ]"
                      v-model="form.unit_price"
                      :disabled="form.processing"
                    />
                  </div>
                  <span v-if="errors.unit_price" class="invalid-feedback">
                    {{ errors.unit_price[0] }}
                  </span>
                </div>

                <div class="col-12 mb-3">
                  <label class="form-label required">Wholesales Price</label>
                  <div
                    class="input-group"
                    :class="{ 'is-invalid': errors.unit_price }"
                  >
                    <span class="input-group-text">&#8358;</span>
                    <input
                      type="number"
                      :class="[
                        'form-control',
                        { 'is-invalid': errors.wholesales_price },
                      ]"
                      v-model="form.wholesales_price"
                      :disabled="form.processing"
                    />
                  </div>
                  <span v-if="errors.wholesales_price" class="invalid-feedback">
                    {{ errors.wholesales_price[0] }}
                  </span>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
              @click="closeModal33"
            >
              <i class="bi bi-x-lg"></i> Cancel
            </button>
            <button
              type="submit"
              class="btn btn-primary px-3"
              form="form"
              :disabled="
                !(
                  form.product_category_id &&
                  form.expiry_date &&
                  form.inventory &&
                  form.name &&
                  form.unit_price &&
                  
                  form.stock_alert_days  &&
                  form.stock_alert_qty_very_low  &&
                  form.stock_alert_qty_low

                )
              "
              @click="saveData"
            >
              <i class="bi bi-floppy" style="padding-right: 3px"></i> Save
            </button>
          </div>
        </div>
      </div>
    </div>

    <!--Form Modal Quantity-->
    <div
      class="modal fade"
      ref="formModalQuantity"
      tabindex="-2"
      aria-hidden="true"
      data-bs-keyboard="false"
      data-bs-backdrop="static"
      data-bs-focus="false"
    >
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
                <input
                  type="text"
                  :class="['form-control', { 'is-invalid': errors.order }]"
                  v-model="form.inventory"
                />
                <span v-if="errors.inventory" class="invalid-feedback">
                  {{ errors.inventory[0] }}
                </span>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
              @click="closeModal2"
            >
              <i class="bi bi-x-lg"></i> Cancel
            </button>
            <button
              type="submit"
              class="btn btn-primary px-3"
              form="form2"
              :disabled="!form.inventory"
            >
              <i class="bi bi-floppy" style="padding-right: 3px"></i> Save
            </button>
          </div>
        </div>
      </div>
    </div>

    <button
      type="button"
      class="btn btn-primary"
      style="float: right"
      @click="openModal33"
    >
      <i class="bi bi-plus-circle"></i> Add New
    </button>

    <div class="pagetitle">
      <h1>Product</h1>
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
                      <label class="form-label">Name</label>
                      <input
                        type="text"
                        class="form-control"
                        v-model="filter.name"
                        placeholder="Search..."
                      />
                    </div>
                    <div class="col-lg-3 col-sm-6">
                      <label class="form-label">Status</label>
                      <select
                        class="form-select"
                        v-model="filter.product_category_id"
                      >
                        <option value="0">ALL</option>
                        <option
                          v-for="data in productCategoryList"
                          :key="data.id"
                          :value="data.id"
                        >
                          {{ data.name }}
                        </option>
                      </select>
                    </div>
                  </div>
                </div>
                <!-- <div class="col-md-2 align-self-end">
                  <div class="card"></div>
                  <button
                    type="button"
                    class="btn btn-secondary pt-1 mx-3"
                    style="float: right"
                    @click="clearFilter"
                  >
                    <i class="bi bi-trash3-fill"></i> Clear
                  </button>
                  <button
                    type="submit"
                    class="btn btn-secondary pt-1"
                    style="float: right"
                  >
                    <i class="bi bi-search"></i> Search
                  </button>
                </div> -->

                <div class="col-12 col-md-4 col-lg-2">
  <div class="d-flex flex-column flex-md-row justify-content-md-end gap-2 mt-2 mt-md-0">
    
    <button
      type="submit"
      class="btn btn-secondary w-100 w-md-auto"
    >
      <i class="bi bi-search"></i> Search
    </button>

    <button
      type="button"
      class="btn btn-secondary w-100 w-md-auto"
      @click="clearFilter"
    >
      <i class="bi bi-trash3-fill"></i> Clear
    </button>

    <button
      type="button"
      class="btn btn-secondary w-100 w-md-auto"
      data-bs-toggle="modal"
      data-bs-target="#exampleModal2"
      @click="productStore.getAllProducts();
"
    >
      <i class="bi bi-briefcase-fill"></i> 
    </button>

  </div>
</div>

                <div class="card-body">
  <h5 class="card-title">Stock Flags</h5>

  <button type="button" class="btn btn-danger btn-lg  mx-2 my-2" @click="veryLowStock">
    Out of Stock
  </button>

  <button type="button" class="btn btn-danger btn-lg  mx-2 my-2" @click="veryLowStock">
    Very Low Stock
  </button>

  <button type="button" class="btn btn-warning btn-lg mx-2 my-2" @click="lowStock">
    Low Stock
  </button>

  

  <!-- <button type="button" class="btn btn-success btn-lg">
    Enough Stock
  </button> -->

  <button type="button"
          class="btn btn-secondary btn-lg mx-2 my-2"
          data-bs-toggle="modal"
          data-bs-target="#exampleModal">
    <span class="text-danger">⚠</span>
    Stock Alert  <span class="bi bi-bell-fill ms-1 text-danger fs-4 blink"></span>
  </button>
</div>
              </div>
              <!-- 
    <div v-if="showDropdown" class="notification-dropdown">

<div v-if="store2?.unreadAlerts.length === 0">
  No new alerts
</div>

<div
  v-for="(item, index) in store2?.unreadAlerts"
  :key="index"
  class="notification-item"
>
  <strong>{{ item.name }}</strong>

  <p v-if="item.days_left !== undefined">
    Expires in {{ item.days_left }} days
  </p>

  <p v-else>
    Stock is very low ({{ item.inventory }})
  </p>

  <button @click="store2?.markAsRead(index)">
    ✔
  </button>
</div>

<button @click="store2?.clearAllAlerts()">
  Clear All
</button> -->

              <!-- </div> -->
            </form>
            <!-- <hr class="text-secondary" /> -->

            <div class="alert-container">
              <div class="alert-box bg-light border rounded p-2 mb-2">
                <div class="scroll-track">
                  <div
                    v-for="item in store2.allStockAlerts"
                    :key="item.id"
                    class="alert-item"
                  >
                    <strong>{{ item.name }}</strong>

                    <!-- STOCK -->
                    <span v-if="item.inventory !== undefined">

                      <span
                        v-if="item.inventory == 0"
                        class="text-danger blink-danger"
                      >
                        🔴 Out of Stock ({{ item.inventory }})
                      </span>
                      <span
                        v-if="item.stock_status === 'very_low'  && item.inventory != 0"
                        class="text-danger blink-danger"
                      >
                        🔴 Very Low Stock ({{ item.inventory }})
                      </span>

                      <span  v-if="item.stock_status === 'low' && item.inventory != 0"  class="text-warning">
                        🟡 Low Stock ({{ item.inventory }})
                      </span>
                    </span>

                    <!-- EXPIRY -->
                    <span
                      v-if="item.days_left !== undefined"
                      :class="[
                        item.expiry_status === 'expired'
                          ? 'text-danger blink-danger'
                          : '',
                        item.expiry_status === 'critical'
                          ? 'text-danger blink-danger bi bi-exclamation-triangle-fill text-danger'
                          : '',
                        item.expiry_status === 'warning' ? 'text-warning' : '',
                      ]"
                    >
                      -
                      <span v-if="item.days_left < 0">Expired</span>
                      <span v-else
                        >Expiry date: {{ dateFormat2(item.expiry_date) }} ({{
                          item.days_left
                        }}
                        days left)</span
                      >
                    </span>

                    <span
                      class="border-start border-4 border-secondary ps-2 mx-2"
                    ></span>
                  </div>
                </div>
              </div>
            </div>

        





            <!-- <hr class="text-secondary" /> -->

            <!-- Default Product -->
            <table class="table table-striped">
              <thead>
                <tr class="table-dark">
                  <th scope="col" width="50px">#</th>
                  <th scope="col" width="100px">Image</th>
                  <th
                    scope="col"
                    @click="sortData('name')"
                    style="cursor: pointer"
                  >
                    Name
                    <i
                      class="text-secondary"
                      :class="
                        filter.sortBy == 'products.name'
                          ? filter.orderBy == 'desc'
                            ? 'bi bi-sort-alpha-down-alt'
                            : 'bi bi-sort-alpha-down'
                          : 'bi bi-arrow-down-up'
                      "
                    ></i>
                  </th>
                  <th
                    scope="col"
                    @click="sortData('products.barcode')"
                    style="cursor: pointer"
                  >
                    Barcode
                    <i
                      class="text-secondary"
                      :class="
                        filter.sortBy == 'products.barcode'
                          ? filter.orderBy == 'desc'
                            ? 'bi bi-sort-alpha-down-alt'
                            : 'bi bi-sort-alpha-down'
                          : 'bi bi-arrow-down-up'
                      "
                    ></i>
                  </th>
                  <th
                    scope="col"
                    @click="sortData('products.category_name')"
                    style="cursor: pointer"
                  >
                    Category
                    <i
                      class="text-secondary"
                      :class="
                        filter.sortBy == 'product_categories.name'
                          ? filter.orderBy == 'desc'
                            ? 'bi bi-sort-alpha-down-alt'
                            : 'bi bi-sort-alpha-down'
                          : 'bi bi-arrow-down-up'
                      "
                    ></i>
                  </th>
                  <th
                    class="text-center"
                    scope="col"
                    @click="sortData('products.inventory')"
                    style="cursor: pointer"
                  >
                    Inventory

                    <i
                      class="text-secondary"
                      :class="
                        filter.sortBy == 'products.inventory'
                          ? filter.orderBy == 'desc'
                            ? 'bi bi-sort-alpha-down-alt'
                            : 'bi bi-sort-alpha-down'
                          : 'bi bi-arrow-down-up'
                      "
                    ></i>
                  </th>
                  <th
                    scope="col"
                    @click="sortData('products.unit_price')"
                    style="cursor: pointer"
                  >
                    Unit Price
                    <i
                      class="text-secondary"
                      :class="
                        filter.sortBy == 'products.unit_price'
                          ? filter.orderBy == 'desc'
                            ? 'bi bi-sort-alpha-down-alt'
                            : 'bi bi-sort-alpha-down'
                          : 'bi bi-arrow-down-up'
                      "
                    ></i>
                  </th>

                  <th
                    scope="col"
                    @click="sortData('products.expiry_date')"
                    style="cursor: pointer"
                  >
                    Expiry Date
                    <i
                      class="text-secondary"
                      :class="
                        filter.sortBy == 'products.expiry_date'
                          ? filter.orderBy == 'desc'
                            ? 'bi bi-sort-alpha-down-alt'
                            : 'bi bi-sort-alpha-down'
                          : 'bi bi-arrow-down-up'
                      "
                    ></i>
                  </th>

                  <th
                    scope="col"
                    @click="sortData('products.created_at')"
                    style="cursor: pointer"
                  >
                    Created Time
                    <i
                      class="text-secondary"
                      :class="
                        filter.sortBy == 'products.created_at'
                          ? filter.orderBy == 'desc'
                            ? 'bi bi-sort-alpha-down-alt'
                            : 'bi bi-sort-alpha-down'
                          : 'bi bi-arrow-down-up'
                      "
                    ></i>
                  </th>
                  <th scope="col" width="100px">Action</th>
                </tr>
              </thead>
              <tbody v-if="dataList?.data?.length > 0">
                <tr v-for="(d, index) in dataList.data" :key="d.id">
                  <th scope="row">{{ dataList?.from + index }}</th>

                  <td>
                    <a
                      :href="d?.image ? 'storage/' + d?.image : defaultImage"
                      target="_blank"
                    >
                      <img
                        :src="d?.image ? 'storage/' + d?.image : defaultImage"
                        style="height: 40px"
                        class="img-thumbnail"
                      />
                    </a>
                  </td>
                  <td>{{ d.name }}</td>
                  <td>
                    <!-- Button trigger modal -->
                    <button
                      type="button"
                      class="btn btn-primary btn-sm"
                      data-bs-toggle="modal"
                      data-bs-target="#exampleModal3"
                      @click="openPrintModal(d)"
                    >
                      <Barcode :code="String(d?.barcode)" />
                    </button>
                  </td>

                  <td>{{ d?.category_name }}</td>
                  <td class="text-center">
                    <button
                      type="button"
                      class="btn badge text-bg"
                      :class="[
                        d?.inventory <= d?.stock_alert_qty_very_low
                          ? 'btn-danger'
                          : d?.inventory <= d?.stock_alert_qty_low
                          ? 'btn-warning'
                          : 'btn-success',
                      ]"
                      @click="addQuatity(d)"
                    >
                      <span
                        style="
                          font-size: 20px;
                          color: whitesmoke !important;
                          margin-right: 0.4rem;
                        "
                      >
                        {{ d.inventory }}

                        <span v-if="d?.inventory <= 5"></span>
                        <span v-else-if="d?.inventory <= 10"></span>
                      </span>

                      <i
                        class="bi bi-pencil-fill text--accent-1"
                        style="font-size: 1.2rem"
                      ></i>
                    </button>
                  </td>
                  <td>{{ currencyFormat(d.unit_price) }}</td>
                  <td>{{ dateFormat2(d?.expiry_date) }}</td>

                  <td>{{ dateFormat(d?.created_at) }}</td>
                  <td>
                    <i
                      class="bi bi-trash3-fill pe-3 text-danger"
                      role="button"
                      @click="deleteData(d.id)"
                    ></i>
                    <i
                      class="bi bi-pencil-square text-success"
                      role="button"
                      @click="editData(d.id)"
                    ></i>
                  </td>
                </tr>
              </tbody>

              <tbody v-else>
                <tr>
                  <td colspan="10" class="shadow-none">No record found</td>
                </tr>
              </tbody>
            </table>
            Pagination
            <div class="d-flex justify-content-end">
              <nav v-if="dataList?.links?.length > 3">
                <ul class="pagination">
                  <li
                    :class="[
                      'page-item',
                      data.url ? '' : 'disabled',
                      data.active ? 'active' : '',
                    ]"
                    v-for="data in dataList?.links"
                    :key="data.id"
                  >
                    <span
                      class="page-link"
                      style="cursor: pointer"
                      v-html="data.label"
                      v-if="data.url && !data.active"
                      @click="
                        paginate(
                          data.url.substring(data.url.lastIndexOf('?page=') + 6)
                        )
                      "
                    ></span>
                    <span class="page-link" v-html="data.label" v-else></span>
                  </li>
                </ul>
              </nav>
            </div>
          </div>
        </div>
      </div>

      <!-- barcode Modal -->
      <div
        class="modal fade"
        id="exampleModal3"
        tabindex="-1"
        aria-labelledby="exampleModalLabel3"
        aria-hidden="true"
      >
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5" id="exampleModalLabel3">
                Print Barcodes
              </h1>
              <button
                type="button"
                class="btn-close"
                data-bs-dismiss="modal"
                aria-label="Close"
              ></button>
            </div>
            <div class="modal-body">
              <!-- Add inside your Quantity Modal -->
              <div class="col-12 mb-3">
                <label class="form-label">Print Quantity</label>
                <input
                  type="number"
                  class="form-control"
                  v-model.number="printQty"
                  min="1"
                />
              </div>
            </div>
            <div class="modal-footer">
              <button
                type="button"
                class="btn btn-secondary"
                data-bs-dismiss="modal"
              >
                Cancel
              </button>
              <button
                type="button"
                class="btn btn-primary"
                @click="confirmPrint"
              >
                Print Barcodes
              </button>
            </div>
          </div>
        </div>
      </div>

      <div id="barcode-table" class="print-area">
        <table>
          <tr v-for="(row, rowIndex) in printRows" :key="rowIndex">
            <td
              v-for="(p, colIndex) in row"
              :key="colIndex"
              class="barcode-cell"
            >
              <svg :id="'barcode-' + rowIndex + '-' + colIndex"></svg>
              <div class="product-name">{{ p.name }}</div>
            </td>
          </tr>
        </table>
      </div>
    </section>



<!-- Product Modal -->
<div class="modal fade" id="exampleModal2" tabindex="-1" aria-labelledby="exampleModalLabel2" aria-hidden="true">
  <div class="modal-dialog" style="max-width: 70%;">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5 text-danger" id="exampleModalLabel2">Product List</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" >

      <table class="table" id="alertTable2">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Product</th>
      <th scope="col">Category</th>
      <th scope="col">Inventory</th>
      <th scope="col">Unit Price</th>

      <th scope="col">Expiry Date</th>

    </tr>
  </thead>
  <tbody>
    <tr  v-for="(item, index) in productStore.products" :key="item.id">
      <th scope="row">{{ index + 1 }}</th>
      <td>{{ item.name }}</td>
      <td>{{ item.category.name }}</td>
      <td>{{ item.inventory }}</td>
      <td>{{ item.unit_price }}</td>
      <td>{{ dateFormat2(item.expiry_date) }}</td>


     
  
    </tr>
  </tbody>
</table>
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" @click="printTable2">Print</button>
      </div>
    </div>
  </div>
</div>



<!-- Alert Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" style="max-width: 70%;">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5 text-danger" id="exampleModalLabel">Stock Alert</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" >

      <table class="table" id="alertTable">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Product</th>
      <th scope="col">Inventory</th>
      <th scope="col">Stock Status</th>

      <th scope="col">Expiry Date</th>

    </tr>
  </thead>
  <tbody>
    <tr  v-for="(item, index) in store2.allStockAlerts" :key="item.id">
      <th scope="row">{{ index + 1 }}</th>
      <td>{{ item.name }}</td>
      <td>{{ item.inventory }}</td>
      <td> <span v-if="item.stock_status == 'low'   && item.inventory != 0" class="text-warning"> low Stock</span>
      <span v-if="item.stock_status == 'very_low' && item.inventory != 0" class="text-danger"> Very Low Stock</span> 
      <span v-if="item.inventory == 0" class="text-danger"> Out of Stock </span> 
      <span v-if="item.stock_status != 'very_low' && item.inventory != 0 && item.stock_status != 'low'" class="text-success">Enough Stock</span> 

      

    </td>
      <td>{{ dateFormat2(item.expiry_date) }}<span v-if="item.expiry_status  && item.expiry_status != 'expired'" class="text-info"> - {{ item.days_left }} days left</span> 
        <span v-if="item.expiry_status == 'expired'  && item.days_left < 0" class="text-danger">  Expired! Please remove from the stock</span>
        <span v-if="item.expiry_status == 'expires_today'  && item.days_left == 0" class="text-danger"> Expires today! Please remove from the stock or sell them</span>
 
      </td>
  
    </tr>
  </tbody>
</table>
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" @click="printTable">Print</button>
      </div>
    </div>
  </div>
</div>




  </div>
</template>
<script setup>
import Loading from "vue-loading-overlay";
import "vue-loading-overlay/dist/css/index.css";
import { onBeforeUnmount, onMounted, onUnmounted, ref, computed } from "vue";
import { Modal } from "bootstrap";
import {
  clearForm,
  currencyFormat,
  dateFormat,
  setFocus,
  dateFormat2,
} from "../../helper.js";
import ShareModal from "../share/Modal.vue";
import axios from "axios";
import Barcode from "./Barcode.vue";
import JsBarcode from "jsbarcode";
import { nextTick } from "vue";
import flatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";

import { useProductCategoryStore } from "@/store/productCategory";
import { useStocktore } from "@/store/stockalerts";

import {useProductStore} from "@/store/product"

import PrintSingleProductBarcode from "./PrintBarcode.vue";
import { Alert } from "bootstrap";
// import JsBarcode from "jsbarcode"

import Swal from "sweetalert2";

const props = defineProps({
  code: String,
});

const isLoading = ref(false);
const formModalQuantity = ref(null);
const formModalInstance2 = ref(null);

const formModal = ref(null);
const formModalInstance = ref(null);

const formModalQuantity3 = ref(null);
const formModalInstance3 = ref(null);

const messageBox = ref(null);
const autofocus = ref(null);
const productCategoryList = ref([]);
const defaultImage = "images/default.png";
const printRows = ref([]); // ✅ MUST be defined like this

const allProducts = ref([]);

//const showModalQuantity = ref(false)

const dateConfig = ref({
  wrap: true,
  altFormat: "d-M-Y",
  altInput: true,
  dateFormat: "Y-m-d",
  enableTime: false,
  defaultHour: "00",
  time_24hr: true,
});


const productModal = ref(null);

  const stock_alert_days = ref(60);
  const stock_alert_qty_very_low = ref(5);
  const stock_alert_qty_low = ref(10);


//const resetPge = ref(false);
const form = ref({
  id: null,
  name: null,
  barcode: null,
  expiry_date: new Date(),
  amount: null,
  product_category_id: null,
  unit_price: null,
  inventory: null,
  stock_alert_days: 60,
  stock_alert_qty_very_low:5,
  stock_alert_qty_low:10,
  image: null,
  image_preview: defaultImage,
  image_remove: null,
  category_name: null,
  // quantities:[],
});



const filter = ref({
  name: null,
  product_category_id: 0,

  sortBy: null,
  orderBy: null,
  page: 1,
});
const dataList = ref([]);

const stockalert = ref([]);
const errors = ref({});

computed(() => unreadAlertsCount);

//import { watch } from 'vue';

// watch(
//   () => [store.fetchAllAlerts],
//   ([stock, expiry]) => {

//     const allAlerts = [...stock, ...expiry];

//     allAlerts.forEach(item => {

//       if (!store.alertedIds.has(item.id) && isCritical(item)) {

//         store.alertedIds.add(item.id);

//         triggerFullAlert(item); // 🚨 main system
//       }

//     });

//   },
//   { deep: true }
// );

const openModal33 = () => {
    clearForm33();
  const modal = Modal.getOrCreateInstance(
        document.getElementById("exampleModal33")
    );
    modal.show();
  
  };


const openModal44  = () => {

  const modal = Modal.getOrCreateInstance(
        document.getElementById("exampleModal33")
    );
    modal.show();
  
  };


const closeModal33 = () => {
  const modal = Modal.getOrCreateInstance(
        document.getElementById("exampleModal33")
    );
    modal.hide();
  };

const setupModal = (modalRef, instanceRef) => {
  if (!modalRef.value) return;

  instanceRef.value = new Modal(modalRef.value);

  modalRef.value.addEventListener("shown.bs.modal", () => {
    setFocus(autofocus);
  });

  modalRef.value.addEventListener("hide.bs.modal", () => {
    document.activeElement?.blur();
  });

  modalRef.value.addEventListener("hidden.bs.modal", () => {
    clearForm(form.value);
    errors.value = {};
  });
};

onBeforeUnmount(() => {
  [formModal, formModalQuantity, formModalQuantity3].forEach((ref) => {
    if (ref.value) {
      ref.value.replaceWith(ref.value.cloneNode(true));
    }
  });
});

const isPageLoading = ref(true);

onMounted(async () => {


 productModal.value = await new Modal(
        document.getElementById("exampleModal33")
    );


  isPageLoading.value = true;

  // ✅ Setup modals
  setupModal(formModal, formModalInstance);
  setupModal(formModalQuantity, formModalInstance2);
  setupModal(formModalQuantity3, formModalInstance3);

  // store2.fetchAllAlerts();

  //   setInterval(() => {
  //     store2.fetchAllAlerts();
  //   });

  await Promise.all([
    getData(true),
    getProductCategoryList(),
    store2.getStockAlert(),
    store2.getAllStockAlerts(),
  ]);

  isPageLoading.value = false;
});

onUnmounted(() => {
  if (formModalInstance.value) {
    formModalInstance.value?.dispose();
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
};


// const  clearForm33 = () => {

//   form.value.name = null;
//   form.value.product_category_id = null;
//   form.value.barcode = null;
//   form.value.expiry_date = new Date();
//   form.value.amount  = null;
//   form.value.unit_price  = null;
//   form.value.inventory  = null;
//   form.value.image  = null;
//   form.value.image_preview  = null;
//   form.value.image_remove  = null;
  
// };

const clearForm33 = () => {
  form.value = {
    id: null,
    name: null,
    product_category_id: null,
    category_name: null,
    barcode: null,
    expiry_date: new Date().toISOString().split("T")[0],
    amount: null,
    unit_price: null,
    wholesales_price: null,
    inventory: null,
    stock_alert_days: 60,
    stock_alert_qty_very_low:5,
    stock_alert_qty_low: 10,
    image: null,
    image_preview: null,
    image_remove: null,
  };

  errors.value = {};
};






const printTable = () => {

  // console.log(2);

  const table = document.getElementById('alertTable');

  const table1 = "Stock Alerts";

  if (!table) {
    alert('Table not found');
    return;
  }

  const iframe = document.createElement('iframe');
  iframe.style.position = 'absolute';
  iframe.style.width = '0';
  iframe.style.height = '0';
  iframe.style.border = 'none';

  document.body.appendChild(iframe);

  const doc = iframe.contentWindow.document;

  doc.open();
  doc.write(`
    <html>
      <head>
        <title>Stock Alert</title>
        <style>
          body { font-family: Arial; padding: 20px; }
          h2 { text-align: center; }
          table { width: 100%; border-collapse: collapse; }
          th, td { border: 1px solid #000; padding: 8px; }
        </style>
      </head>
      <body>
        <h2>Stock Alert Report</h2>
        ${table.outerHTML}
      </body>
    </html>
  `);
  doc.close();

  iframe.contentWindow.focus();
  iframe.contentWindow.print();

  setTimeout(() => {
    document.body.removeChild(iframe);
  }, 1000);
};



const printTable2 = () => {

// console.log(2);

const table = document.getElementById('alertTable2');


if (!table) {
  alert('Table not found');
  return;
}

const iframe = document.createElement('iframe');
iframe.style.position = 'absolute';
iframe.style.width = '0';
iframe.style.height = '0';
iframe.style.border = 'none';

document.body.appendChild(iframe);

const doc = iframe.contentWindow.document;

doc.open();
doc.write(`
  <html>
    <head>
      <title>Product List</title>
      <style>
        body { font-family: Arial; padding: 20px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; }
      </style>
    </head>
    <body>
      <h2>Product List</h2>
      ${table.outerHTML}
    </body>
  </html>
`);
doc.close();

iframe.contentWindow.focus();
iframe.contentWindow.print();

setTimeout(() => {
  document.body.removeChild(iframe);
}, 1000);
};



// add or create


const closeModal2 = () => {
  form.value.inventory = null;
  errors.value = {};
  isLoading.value = false;
  formModalQuantity.value = false;
  formModalQuantity3.value = null;
  formModalInstance.value?.hide();

  formModalInstance2.value?.hide();
  formModalInstance3.value?.hide();
};

const generateBarcodes = () => {
  printRows.value.forEach((row, rowIndex) => {
    row.forEach((p, colIndex) => {
      const svg = document.getElementById(`barcode-${rowIndex}-${colIndex}`);
      if (svg) {
        JsBarcode(svg, p.barcode, {
          format: "CODE128",
          displayValue: true,
          width: 2,
          height: 50,
        });
      }
    });
  });
};

const printBarcodes = () => {
  // formModalInstance3.value?.hide();

  const printContents = document.getElementById("barcode-table").innerHTML;
  const originalContents = document.body.innerHTML;
  document.body.innerHTML = printContents;
  window.print();
  document.body.innerHTML = originalContents;

  window.location.reload();
};

const printQty = ref(1); // number of barcodes to print
const selectedProduct = ref(null); // product selected for printing

// Open quantity modal
const openPrintModal = (product) => {
  selectedProduct.value = product;
  printQty.value = 1;
  formModalQuantity3.value = new Modal(formModalQuantity.value);

  formModalInstance3.value.show(); // reuse quantity modal
};

// Confirm printing
const confirmPrint = () => {
  if (!selectedProduct.value) return;

  const qty = printQty.value || 1;
  const itemsToPrint = Array(qty).fill(selectedProduct.value);

  // Split into rows of 3
  const rows = [];
  for (let i = 0; i < itemsToPrint.length; i += 3) {
    rows.push(itemsToPrint.slice(i, i + 3));
  }
  printRows.value = rows;

  // Hide modal before printing
  // formModalInstance3.value.hide();

  // Wait for DOM to update, then generate barcodes
  setTimeout(() => {
    generateBarcodes();
    printBarcodes();
  }, 100);
};

const store = useProductCategoryStore();

const store2 = useStocktore();

const productStore = useProductStore();

const Toast = Swal.mixin({
  toast: true,
  position: "top-end",
  showConfirmButton: false,
  timer: 5000,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.onmouseenter = Swal.stopTimer;
    toast.onmouseleave = Swal.resumeTimer;
  },
});

const saveCategoryProduct = async (e) => {
  e.preventDefault();


  const result = await store.saveData(form.value);

  if (result.success) {
    // refresh category list
    await getProductCategoryList();

    form.value.product_category_id = "";

    // assign new category ID correctly

    form.value.product_category_id = result.id;

    // optional: clear input
    form.value.category_name = "";
  } else {
    errors.value = result.errors || {};
    setFocus(autofocus);
  }
};

const saveData = async (e) => {
  e.preventDefault();

  isLoading.value = true;
  errors.value = null;

  try {
    const { data } = await axios.post(
      "api/product/save",
      form.value,
      {
        headers: {
          "Content-Type": "multipart/form-data",
        },
      }
    );

    if (!data.success) {
      errors.value = data.errors || ["Unknown error occurred"];

      await Swal.fire({
        icon: "error",
        title: "Error",
        text: Array.isArray(errors.value)
          ? errors.value.join("\n")
          : errors.value,
      });

      setFocus(autofocus);
      return;
    }
    closeModal33();
   // getData(true);
    clearForm33();

    await Toast.fire({ icon: "success",title: "Product saved successfully!",});
  } catch (error) {
    console.error(error);

    let message = "An unexpected error occurred.";

    if (error.response) {
      if (error.response.status === 422) {
        errors.value = error.response.data.errors;

        message = Object.values(errors.value)
          .flat()
          .join("\n");
      } else if (error.response.status === 500) {
        message = "Internal server error. Please try again.";
      } else {
        message =
          error.response.data?.message ||
          "An unexpected server error occurred.";
      }
    } else if (error.request) {
      message = "Network error. Please check your internet connection.";
    } else {
      message = error.message;
    }

    Toast.fire({
  icon: "error",
  title: message,
});

    setFocus(autofocus);
  } finally {
    isLoading.value = false;
    getData(true);

  }
};

const addInventory = async () => {
  try {
    isLoading.value = true;
    errors.value = {};

    const response = await axios.post("/api/product/addQuantity", form.value);

    if (response.data.success) {
      getData(true); // refresh table
      closeModal2();
    } else {
      errors.value = response.data.errors || {};
    }
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      console.error(error);
    }
  } finally {
    isLoading.value = false;
  }
};



const addQuatity = (d) => {
  form.value.id = d.id;
  formModalQuantity.value = new Modal(formModalQuantity.value);
  formModalQuantity.value.show();
  // showModalQuantity.value = true;
};

// load data
const getData = (resetPge = false) => {
  isLoading.value = true;
  if (resetPge) {
    filter.value.page = 1;
    axios
      .post("api/product/list", filter.value)
      .then((response) => {
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
    filter.value.orderBy = filter.value.orderBy == "asc" ? "desc" : "asc";
  } else {
    filter.value.sortBy = field;
    filter.value.orderBy = "asc";
  }
  getData(true);
};

// edit
// const editData = (id) => {
//   form.value.category_name = null;
//   isLoading.value = true;
//   axios
//     .get("api/product/edit/" + id)
//     .then((response) => {

//       // alert(response.data.name)
//       Object.keys(form.value).forEach((key) => {
//         if (key in response.data) {
//           form.value[key] = response.data[key];
//         }
//       });
//       form.value.image_preview = form.value.image
//         ? "storage/" + form.value.image
//         : defaultImage;
//       form.value.image = null;
//       form.value.image_remove = null;
//       form.value.category_name = response.data.category_name;
//         openModal33();
//     })
//     .catch((ex) => {
//       console.log(ex);
//     })
//     .finally(() => {
//       isLoading.value = false;
//     });
// };



// delete
// const deleteData = (id) => {
//   messageBox.value.showModal(4, () => {
//     isLoading.value = true;
//     axios
//       .delete("api/product/delete/" + id)
//       .then(() => {
//         getData(true);
//       })
//       .catch((ex) => {
//         console.log(ex);
//       })
//       .finally(() => {
//         isLoading.value = false;
//       });
//   });
// };


const deleteData = async (id) => {
  const result = await Swal.fire({
    title: "Delete Product?",
    text: "You won't be able to undo this action!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Yes, delete it!",
    cancelButtonText: "Cancel",
  });

  if (!result.isConfirmed) return;

  isLoading.value = true;

  try {
    await axios.delete(`api/product/delete/${id}`);

    await Swal.fire({
      toast: true,
      position: "top-end",
      icon: "success",
      title: "Product deleted successfully!",
      showConfirmButton: false,
      timer: 2500,
      timerProgressBar: true,
    });

    getData(true);
  } catch (error) {
    console.error(error);

    Swal.fire({
      icon: "error",
      title: "Delete Failed",
      text:
        error.response?.data?.message ||
        "An error occurred while deleting the product.",
    });
  } finally {
    isLoading.value = false;
  }
};

const editData = async (id) => {
  isLoading.value = true;

  try {
    const { data } = await axios.get(`api/product/edit/${id}`);

    Object.assign(form.value, data);

    form.value.image_preview = data.image
      ? `storage/${data.image}`
      : defaultImage;

    form.value.image = null;
    form.value.image_remove = null;

    openModal44();
  } catch (error) {
    console.error(error);

    Swal.fire({
      icon: "error",
      title: "Error",
      text:
        error.response?.data?.message ||
        "Unable to load product details.",
    });
  } finally {
    isLoading.value = false;
  }
};

// get product category list
const getProductCategoryList = () => {
  isLoading.value = true;
  axios
    .get("api/product/category-list")
    .then((response) => {
      productCategoryList.value = response.data;
    })
    .catch((ex) => {
      console.log(ex);
    })
    .finally(() => {
      isLoading.value = false;
    });
};

// upload file
const upload = () => {
  let acceptFileType = ["image/png", "image/jpg", "image/jpeg"];
  let input = document.createElement("input");
  input.type = "file";
  input.accept = ".png,.jpg,.jpeg";
  input.onchange = (_) => {
    let file = input.files[0];
    if (!acceptFileType.includes(file.type.toLocaleLowerCase())) {
      errors.value.image = "Accept file type: png, jpg, jpeg";
      return;
    } else if (file.size > 1048576) {
      errors.value.image = "File size must be less than 1mb";
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

<style scoped>
.alert-container {
  overflow: hidden;
}

.alert-box {
  height: 40px;
  overflow: hidden;
  position: relative;
}

.scroll-track {
  display: flex;
  gap: 30px;
  animation: scrollLeft 30s linear infinite;
}

.alert-item {
  white-space: nowrap;
}

@keyframes scrollLeft {
  0% {
    transform: translateX(100%);
  }
  100% {
    transform: translateX(-100%);
  }
}

.alert-box:hover .scroll-track {
  animation-play-state: paused;
}
.pulse-danger {
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.05);
    opacity: 0.7;
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.notification-bell {
  position: relative;
  cursor: pointer;
  font-size: 20px;
}

.badge2 {
  position: absolute;
  top: -5px;
  right: -10px;
  background: red;
  color: white;
  font-size: 12px;
  border-radius: 50%;
  padding: 3px 6px;
}

/* .notification-dropdown {
  position: absolute;
  right: 0;
  top: 40px;
  width: 300px;
  background: white;
  border: 1px solid #ddd;
  max-height: 400px;
  overflow-y: auto;
  z-index: 999;
}

.notification-item {
  padding: 10px;
  border-bottom: 1px solid #eee;
} */

/* .custom-alert-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
} */

/* .modal-content {
  background: white;
  padding: 20px;
  border-radius: 12px;
  width: 320px;
  text-align: center;
} */



@media print {
  body * {
    visibility: hidden;
  }

  #alertTable,
  #alertTable * {
    visibility: visible;
  }

  #alertTable {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
  }
}


@keyframes blink {
  0% { opacity: 1; }
  50% { opacity: 0.2; }
  100% { opacity: 1; }
}
.blink {
  animation: blink 1s infinite;
}

</style>