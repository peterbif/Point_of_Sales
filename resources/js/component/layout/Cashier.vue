<template>
  <div class="vl-parent">
    <loading v-model:active="isLoading" :is-full-page="true" />
    <ShareModal ref="messageBox"></ShareModal>

    <div
      class="modal fade"
      ref="formModal"
      tabindex="-1"
      aria-hidden="true"
      data-bs-keyboard="false"
      data-bs-backdrop="static"
      data-bs-focus="false"
    >
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
                  <input
                    type="text"
                    class="form-control"
                    :value="auth.user?.username"
                    disabled
                  />
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label required">Old Password</label>
                  <input
                    type="password"
                    :disabled="isLoading"
                    :class="[
                      'form-control',
                      { 'is-invalid': errors.old_password },
                    ]"
                    v-model="form.old_password"
                    ref="autofocus"
                  />
                  <span v-if="errors.old_password" class="invalid-feedback">
                    {{ errors.old_password[0] }}
                  </span>
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label required">New Password</label>
                  <input
                    type="password"
                    :disabled="isLoading"
                    :class="[
                      'form-control',
                      { 'is-invalid': errors.new_password },
                    ]"
                    v-model="form.new_password"
                  />
                  <span v-if="errors.new_password" class="invalid-feedback">
                    {{ errors.new_password[0] }}
                  </span>
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label required"
                    >New Password Confirmation</label
                  >
                  <input
                    type="password"
                    :disabled="isLoading"
                    :class="[
                      'form-control',
                      { 'is-invalid': errors.new_password_confirmation },
                    ]"
                    v-model="form.new_password_confirmation"
                  />
                  <span
                    v-if="errors.new_password_confirmation"
                    class="invalid-feedback"
                  >
                    {{ errors.new_password_confirmation }}
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
            >
              <i class="bi bi-x-lg"></i> Cancel
            </button>
            <button
              type="submit"
              class="btn btn-primary px-3"
              form="formPassword"
              :disabled="isLoading"
            >
              <i
                v-if="isLoading"
                class="spinner-border spinner-border-sm"
                role="status"
                aria-hidden="true"
              ></i>
              <i v-else class="bi bi-floppy" style="padding-right: 3px"></i>
              Save
            </button>
          </div>
        </div>
      </div>
    </div>

  
  <!-- invoice -->

    <div id="print_invoice" class="d-none">
      <div style="text-align: center">
        <div
          style="
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
          "
        >
          <img src="images/newlogo.jpeg"  width="60" />
          
          <div style="text-align: center; font-size: 14px;">
  
  <h6 style="font-size: 18px; margin-bottom: 5px;">
    Licores Districts
  </h6>

  <p style="margin: 0;">
    Heritage Plus Mall, 41 7th Day Road, Iyaganku, Ib
  </p>

  <p style="margin: 5px 0;">
    Tel: 08027315689, 0904999904
  </p>
  <p style="margin: 5px 0;">
    Instagram: @licores_districts
  </p>

</div>
      
        </div>

        <h1 style="margin: 0; font-size: 20px"> <span v-if="order[0]?.order_id">Receipt</span> <span v-else>Invoice</span></h1>
      </div>
      <hr style="margin-top: 0px; padding-top: 0px" />
      <table style="width: 100%; font-size: 12px">
        <thead>
          <tr>
            <!-- <td width="80px" style="text-align: right">:</td> -->
            <!-- <td style="text-align: left">{{ auth.user?.username }}</td> -->
            <td width="80px" style="text-align: right">Invoice #:</td>
            <td style="text-align: left">{{ transactionId }}</td>
          </tr>
          <tr>
            <td style="width: 60px; text-align: right">Cashier:</td>
            <td style="text-align: left; width: 100px">
              {{ auth.user?.username }}
            </td>
            <td style="width: 60px; text-align: right">Date:</td>
            <td style="text-align: left; width: 100px">
              {{ dateFormat(order?.updated_at) }}
            </td>
          </tr>
        </thead>
      </table>
      <table
        style="width: 100%; margin-top: 10px"
        border="0"
        cellspacing="0"
        cellpadding="2px"
      >
        <thead>
          <tr style="background: darkgray">
            <th width="20px">#</th>
            <th style="text-align: left">Description</th>
            <th style="width: 8%; text-align: center">Qty</th>
            <th style="width: 16%; text-align: right">U.P (&#8358;)</th>
            <th style="width: 12%; text-align: right">Disc (%)</th>
            <th style="width: 18%; text-align: right">Total (&#8358;)</th>
          </tr>
        </thead>
        <tbody>
          <tr style="font-size: 11px" v-for="(data, index) in order" :key="data.id">
            <td align="center">{{ index + 1 }}</td>
            <td align="left">{{ data.description }}</td>
            <td align="center">{{ data.qty }}</td>
            <td align="right">{{ numberFormat(data.unit_price) }}</td>
            <td align="right">{{ data.discount }}</td>
            <td align="right">
              {{
                numberFormat(
                  data.unit_price * data.qty * (1 - data.discount / 100)
                )
              }}
            </td>
          </tr>
        </tbody>
      </table>

 

      <table style="font-size: 14px; width: 100%">
        <tbody>
          <tr v-if="order && order?.discount > 0">
            <td style="text-align: right">Grand Total (&#8358;) :</td>
            <td style="text-align: right; width: 100px">
              {{ numberFormat(Number(grand_total), 2) }}
            </td>
          </tr>
          <tr v-if="order && order?.discount > 0">
            <td style="text-align: right">
              Discount ({{ order?.discount }}%) :
            </td>
            <td style="text-align: right">
              {{ numberFormat((order?.total * order?.discount) / 100, 2) }}
            </td>
          </tr>
          <!-- <tr>
                  <th style="text-align: right" v-if="total != 0">Total (&#8358;): </th>
                  <th style="text-align: right; width: 100px;">  <span v-if="total != 0">   {{ currencyFormat(Number(total)) }}</span></th>
                </tr>

                <tr>
                  <th style="text-align: right" v-if="total != 0">Discount (&#8358;): </th>
                  <th style="text-align: right; width: 100px;">  <span v-if="total != 0">    {{ currencyFormat(Number(total_discount_amount)) }}</span></th>
                </tr>

                                    
                <tr>
                  <td style="text-align: right" v-if="grand_total != 0">Grand Total (&#8358;):   </td>
                  <td style="text-align: right">  <span v-if="grand_total != 0">    {{ currencyFormat(Number(Number(grand_total))) }}</span></td>
                </tr> -->
        </tbody>
      </table>

      <table style="width: 100%; font-size: 13px; margin-top: 5px">
        <tbody>
        <tr>
          <td>SubTotal:</td>
          <td style="text-align: right">
            {{ currencyFormat(sub_total) }}
          </td>
        </tr>

        <tr>
          <td>Vat (vat)%:</td>
          <td style="text-align: right">
            {{ currencyFormat(vat_amount) }}
          </td>
        </tr>
      </tbody>
      </table>

      <hr style="border-top: 1px dashed #000; margin: 4px 0" />

      <table style="width: 100%; font-size: 14px; font-weight: bold">
        <tbody>
        <tr>
          <td>Grand Total:</td>
          <td style="text-align: right">
            {{ currencyFormat(grand_total) }}
          </td>
        </tr>
      </tbody>
      </table>
  
    </div>

    <div id="print_receipt" class="d-none">
      <!-- <div style="text-align: center">
        <img src="images/UCH.png" height="50px" width="50px" />
        <h3 style="display: block">
          Venture
          University College Hospital, Ibadan.  <br/> Tel: 069 868 768, 078 551  &nbsp; Email: pos@gmail.com
          115
        </h3>
        <h1 style="padding: 0px; margin: 0px; font-size: 30px">Receipt</h1>
      </div> -->

      <div style="text-align: center">
        <div
          style="
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
          "
        >
          <img src="images/newlogo.jpeg"  width="60" />

          <!-- <img src="images/newlogo.jpeg" height="70" width="80" /> -->
          

          <div style="text-align: center; font-size: 14px;">
  
  <h6 style="font-size: 18px; margin-bottom: 5px;">
    Licores Districts
  </h6>

  <p style="margin: 0;">
    Heritage Plus Mall, 41 7th Day Road, Iyaganku, Ib
  </p>

  <p style="margin: 5px 0;">
    Tel: 08027315689, 0904999904
  </p>
  <p style="margin: 5px 0;">
    Instagram: @licores_districts
  </p>

</div>
        </div>
        </div>
      
      <hr style="margin-top: 0px; padding-top: 0px" />
      <table style="width: 100%">
        <thead>
          <tr>
            <td width="80px" style="text-align: right">Mode of Payment:</td>
            <td style="text-align: left">{{ payment.mode_of_payment }}</td>
            <td width="80px" style="text-align: right">Receipt #:</td>
            <td style="text-align: left">{{ transactionId }}</td>
          </tr>
          <tr>
            <td style="width: 60px; text-align: right">Cashier:</td>
            <td style="text-align: left; width: 100px">
              {{ auth.user?.username }}
            </td>
            <td style="width: 60px; text-align: right">Date:</td>
            <td style="text-align: left; width: 100px">
              {{ dateFormat(order?.created_at) }}
            </td>
          </tr>
        </thead>
      </table>
      <table
        style="width: 100%; margin-top: 10px"
        border="0"
        cellspacing="0"
        cellpadding="2px"
      >
        <thead>
          <tr style="background: darkgray">
            <th width="20px">#</th>
            <th style="text-align: left">Description</th>
            <th style="width: 8%; text-align: center">Qty</th>
            <th style="width: 16%; text-align: right">U.P (&#8358;)</th>
            <th style="width: 12%; text-align: right">Disc (%)</th>
            <th style="width: 18%; text-align: right">Total (&#8358;)</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(data, index) in order?.order_details" :key="data.id">
            <td align="center">{{ index + 1 }}</td>
            <td align="left">{{ data?.description }}</td>
            <td align="center">{{ data?.qty }}</td>
            <td align="right">{{ numberFormat(data?.unit_price) }}</td>
            <td align="right">{{ data?.discount }}</td>
            <td align="right">
              {{
                numberFormat(
                  data?.unit_price * data?.qty * (1 - data?.discount / 100)
                )
              }}
            </td>
          </tr>
          <!-- <tr>
                  <th style="text-align: right; " >Total (&#8358;): </th>
                  <th style="text-align: right; width: 100px;">   <span> {{ currencyFormat(Number(receiptData?.total)) }} </span></th>
                </tr>

                <tr>
                  <th style="text-align: right ;" >Discount (&#8358;): </th>
                  <th style="text-align: right ; width: 100px;">   <span>   {{ currencyFormat(Number(receiptData?.total_discount)) }} </span></th>
                </tr>
 
                
                <tr>
                  <td style="text-align: right;" >Grand Total (&#8358;):   </td>
                  <td style="text-align: right ;">     <span> {{ currencyFormat(Number(Number(receiptData?.grand_total))) }} </span></td>
                </tr> -->
        </tbody>
      </table>

      <table style="width: 100%; font-size: 13px; margin-top: 5px">
        <tbody>
        <tr>
          <td>Sub Total:</td>
          <td style="text-align: right">
            {{ currencyFormat(receiptData?.sub_total) }}
          </td>
        </tr>

        <tr>
          <td>Vat ({{ receiptData?.vat }})%:</td>
          <td style="text-align: right">
            {{ currencyFormat(receiptData?.vat_amount) }}
          </td>
        </tr>
        </tbody>
      </table>

      <hr style="border-top: 1px dashed #000; margin: 4px 0" />

      <table style="width: 100%; font-size: 14px; font-weight: bold">
        <tbody>
        <tr>
          <td>Grand Total:</td>
          <td style="text-align: right">
            {{ currencyFormat(receiptData?.grand_total) }}
          </td>
        </tr>
        </tbody>
      </table>

      <table style="width: 100%; font-size: 13px; margin-top: 5px">
        <tbody>
        <tr>
          <td>Paid:</td>
          <td style="text-align: right">
            {{ currencyFormat(receiptData?.receive_amount) }}
          </td>
        </tr>

        <tr>
          <td>Change:</td>
          <td style="text-align: right">
            {{
              currencyFormat(
                receiptData?.receive_amount - receiptData?.grand_total
              )
            }}
          </td>
        </tr>
        </tbody>
      </table>
      <!-- <table style="width: 100%;">
        <tbody>
          <tr v-if="order && order.discount > 0">
            <td style="text-align: right">Grand Total (&#8358;) :</td>
            <td style="text-align: right; width: 100px;">{{ numberFormat(grand_total, 2) }}</td>
          </tr>
          <tr v-if="order && order.discount > 0">
            <td style="text-align: right">
              Discount ({{ order.discount }}%) :
            </td>
            <td style="text-align: right;">
              {{ numberFormat(Number(total_discount_amount), 2) }}
            </td>
          </tr>
          <tr>
            <th style="text-align: right">Total ($) :</th>
            <th style="text-align: right; width: 100px;">{{ numberFormat(Number(total), 2) }}</th>
          </tr>
          <tr v-if="order?.receive_amount > 0">
            <td style="text-align: right">Receive Amount($) :</td>
            <td style="text-align: right">{{ numberFormat(order?.receive_amount, 2) }}</td>
          </tr>
          <tr v-if="order?.receive_amount - order?.summary?.net_amount">
            <td style="text-align: right">Change ($) :</td>
            <td style="text-align: right">{{
              numberFormat(order?.receive_amount - order?.summary?.net_amount, 2) }}</td>
          </tr>
        </tbody>
      </table> -->
      <hr />
      <div style="text-align: center">
        <i style="font-size: 12px">Thank you, see you again!</i><br />
      </div>
    </div>

    <!-- <div class="fade modal" ref="tableModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header py-1 text-bg-secondary">
            <h4 class="modal-title" style="font-weight: bold">Select Table</h4>
          </div>
          <div class="modal-body">
            <div class="row row-cols-3 row-cols-sm-4 row-cols-xl-5 g-2">
              <div v-for="data in arrayTable">
                <div class="p-3 fs-2 text-center fw-bold w-100" :class="getStatus(data.status)" style="cursor: pointer"
                  @click="selectTable(data.id)">
                  {{ data.name }}
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <div class="px-2 py-1 text-bg-secondary" style="text-align: right">Free</div>
            <div class="px-2 py-1 text-bg-danger" style="text-align: right">Busy</div>
            <div class="px-2 py-1 text-bg-success" style="text-align: right">Printed</div>
          </div>
        </div>
      </div>
    </div> -->
    <div
      class="fade modal"
      ref="paymentModal"
      tabindex="-1"
      aria-hidden="true"
      data-bs-keyboard="false"
    >
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header py-1 text-bg-secondary">
            <h4 class="modal-title" style="font-weight: bold">Make Payment</h4>
          </div>
          <div class="modal-body p-2" style="background: white">
            <table
              style="width: 100%"
              cellspacing="0"
              cellpadding="2px"
              class="table mb-0"
            >
              <thead>
                <tr class="table-dark">
                  <th width="20px">S/N</th>
                  <th style="text-align: left">Description</th>
                  <th style="width: 8%; text-align: center">Qty</th>
                  <th style="width: 16%; text-align: right">U.P (&#8358;)</th>
                  <th style="width: 15%; text-align: right">Disc (%)</th>
                  <th style="width: 18%; text-align: right">Total (&#8358;)</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(data, index) in order" :key="data.id">
                  <td align="center">{{ index + 1 }}</td>
                  <td align="left">{{ data?.description }}</td>
                  <td align="center">{{ data?.qty }}</td>
                  <td align="right">{{ numberFormat(data?.unit_price) }}</td>
                  <td align="center">{{ data?.discount }}</td>
                  <td align="right">
                    {{
                      numberFormat(
                        data?.unit_price *
                          data?.qty *
                          (1 - data?.discount / 100)
                      )
                    }}
                  </td>
                </tr>
              </tbody>
            </table>
            <table
              class="mt-2"
              style="font-size: 14px; width: 100%"
              cellpadding="5px"
            >
              <tbody>
                <tr v-if="order && order.discount > 0">
                  <td style="text-align: right">
                    Discount ({{ order?.discount }}%) :
                  </td>
                  <td style="text-align: right">
                    {{ numberFormat((order?.total * order?.discount) / 100) }}
                  </td>
                </tr>
                <!-- <tr>
                  <th style="text-align: right" v-if="total != 0">
                    Total (&#8358;):
                  </th>
                  <th style="text-align: right; width: 100px">
                    <span v-if="total != 0">
                      {{ numberFormat(Number(total), 2) }}</span
                    >
                  </th>
                </tr> -->

                <!-- <tr>
                  <th style="text-align: right" v-if="total != 0">
                    Discount (&#8358;):
                  </th>
                  <th style="text-align: right; width: 100px">
                    <span v-if="total != 0">
                      {{ numberFormat(Number(total_discount_amount), 2) }}</span
                    >
                  </th>
                </tr> -->

              

                <tr>
                  <th style="text-align: right" v-if="total != 0">
                    Sub Total (&#8358;):
                  </th>
                  <th style="text-align: right; width: 100px">
                    <span v-if="total != 0">
                      {{ numberFormat(Number(sub_total), 2) }}</span
                    >
                  </th>
                </tr>

                
                <tr>
                  <th style="text-align: right" v-if="total != 0">
                    Vat Amount (&#8358;):
                  </th>
                  <th style="text-align: right; width: 100px"  v-if="total != 0">
                <span style="margin-right: 10px !important;">{{ (vat) }} %</span> <span v-if="total != 0">
                      {{ numberFormat(Number(vat_amount), 2) }}</span
                    >
                  </th>
                </tr>

                <tr>
                  <td style="text-align: right" v-if="grand_total != 0">
                    Grand Total (&#8358;):
                  </td>
                  <td style="text-align: right">
                    <span v-if="grand_total != 0">
                      {{ numberFormat(Number(Number(grand_total), 2)) }}</span
                    >
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="modal-footer" style="display: block">
            <div class="row">
              <div class="col-md-8">
                <label class="form-label required">Receive Amount</label>
                <span v-if="receive_amount_error" class="text-danger">
                  {{ receive_amount_error }}
                </span>
                <div class="row">
                  <div class="col-md-6">
                    <div class="input-group mb-3">
                      <span class="input-group-text">(&#8358;)</span>
                      <input
                        type="number"
                        class="form-control"
                        step="0.01"
                        v-model="payment.receive_amount"
                        ref="autofocus1"
                      />
                      <div class="input-group-append">
                        <button
                          class="btn btn-success"
                          style="border-radius: 0px"
                          @click="
                            receive_amount >=
                              numberFormat(Number(grand_total), 2)
                          "
                        >
                          <i class="bi bi-check-lg"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="text-end align-text-bottom pt-lg-4">
                  <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                  >
                    Cancel</button
                  >&nbsp;
                  <button
                    type="button"
                    class="btn btn-primary"
                    @click="confirmPayment()"
                    :disabled="isLoading"
                  >
                    {{ isLoading ? "Processing" : "Confirm" }}
                  </button>
                </div>
              </div>
            </div>
            <!-- <div class="row">
              <div class="col-md-12  text-danger" v-if="errorMsg">
                {{ errorMsg }}
              </div>
            </div> -->
          </div>
        </div>
      </div>
    </div>
    <!-- <header id="header" class="header fixed-top d-flex align-items-center"> -->
      <header id="header" class="header fixed-top d-flex align-items-center justify-content-between py-2 py-md-3 px-3 px-md-4 w-100">        
      <div class="d-flex align-items-center justify-content-between">
        <a href="/" class="logo d-flex align-items-center">
          <!-- <img
            src="images/favicon.webp"
            height="150"
            width="130"
            alt="POS Syetem"
          /> -->
          <h3 class="fw-bold d-flex align-items-center gap-2">
  <i class="bi bi-shop text-primary"></i>
  Smart<span class="text-success">Store</span>
</h3>        </a>
      </div>

      <div class="alert-container mx-3">
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
                        v-if="item.stock_status === 'very_low' && item.inventory != 0"
                        class="text-danger blink-danger"
                      >
                        🔴 Very Low Stock ({{ item.inventory }})
                      </span>

                      <span      v-if="item.stock_status === 'low' && item.inventory != 0"  class="text-warning">
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

            <nav class="header-nav ms-auto d-flex align-items-center gap-3">

<!-- USER -->
<ul class="d-flex align-items-center mb-0">
  <li class="nav-item dropdown pe-3">
    <a
      class="nav-link nav-profile d-flex align-items-center pe-0"
      href="#"
      data-bs-toggle="dropdown"
    >
      <i class="bi bi-person-fill" style="font-size: 35px"></i>
      <span class="d-none d-md-block dropdown-toggle ps-2 text-capitalize">
        {{ auth?.user?.username }}
      </span>
    </a>

    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
      <li>
        <button
          class="dropdown-item d-flex align-items-center"
          @click="openModal"
        >
          <i class="bi bi-shield-lock"></i>
          <span>Change Password</span>
        </button>
      </li>

      <li><hr class="dropdown-divider" /></li>

      <li>
        <button
          type="submit"
          class="dropdown-item d-flex align-items-center"
          @click="auth.logout()"
        >
          <i class="bi bi-box-arrow-right"></i>
          <span>Sign Out</span>
        </button>
      </li>
    </ul>
  </li>
</ul>

<!-- NETWORK STATUS -->
<div class="d-flex align-items-center small">
  <span v-if="!network.isOnline" class="text-danger">
    🔴 Offline
  </span>

  <span v-else-if="!network.isReachable" class="text-warning">
    🟡 No internet
  </span>

  <span v-else class="text-success">
    🟢 Online
  </span>
</div>

</nav>
    </header>

 


    <main id="main" class="main ms-0 pt-3">
      <div class="row">
        <div :class="styleOrder" v-if="showOrder">
          <div class="card mb-0" style="height: 88vh">
            <ul class="nav nav-tabs nav-fill">
              <!-- <li class="nav-item">
                <span class="nav-link menu-item active" style="cursor: pointer" @click="menuByCateId(0)"
                  id="cate_0">ALL</span>
              </li> -->
              <!-- <li class="nav-item" v-for="data in categoryList" >
                <span class="nav-link menu-item" style="cursor: pointer" @click="menuByCateId(data.id)"
                  :id="'cate_' + data.id">{{ data.name }}</span>
              </li> -->
              <li class="nav-item d-flex align-items-center w-100">
                <!-- 75% -->
                <!-- <div style="flex: 3" class="me-2">
                  <input
                    type="text"
                    class="form-control form-control-lg"
                    :placeholder="searchProduct2"
                    v-model="search"
                    @input="handleSearch"
                    @keyup.enter="handleSearch"
                    style="background: lightyellow; height: 50px !important"
                  />
                </div> -->

              <div style="flex: 3" class="me-2">
                <input
                  type="text"
                  class="form-control form-control-lg"
                  :placeholder="searchProduct2"
                  v-model="search"
                  @keydown="handleSearch"
                  ref="searchInput"
                  style="background: lightyellow; height: 50px !important"
                />
              </div>

                <!-- 25% -->
                <div style="flex: 1">
                  <button
                    class="btn btn-md btn-primary w-100"
                    @click="createOrder"
                  >
                    Create Order
                  </button>
                </div>
              </li>
            </ul>
            <div v-if="pList" class="card-body p-1" style="overflow-y: scroll">
              <div
                class="row row-cols-2 row-cols-sm-3 row-cols-lg-4 row-cols-xl-5 g-1"
              >
                <div
                  class="col"
                  v-for="data in productList" :key="data.id"
                  style="cursor: pointer"
                  @click="addToOrder(data.id)"
                >
                  <div
                    class="card h-80 mb-0"
                    :style="
                      data?.inventory && data?.inventory > 0
                        ? 'background: lightcyan'
                        : 'background: white'
                    "
                    :disable="data?.inventory < 0"
                  >

                  <!-- <p>{{ data.image }}</p> -->
                    <div
                      class="card-img-top"
                      :style="getProductImage(data.image)"
                    ></div>
                    <div
                      class="card-body"
                      style="font-size: 23px; padding: 3px"
                    >
                      <p class="card-text text-center mb-1">{{ data?.name }}</p>
                      <span
                        style="float: right"
                        :class="[
                          'badge',
                          data.inventory === 0
                            ? 'text-bg-danger'
                            : data.inventory > 0 && data.inventory < 5
                            ? 'text-bg-warning'
                            : 'text-bg-success',
                        ]"
                        v-if="data.inventory !== undefined"
                        :title="
                          data.inventory === 0
                            ? 'Out of stock'
                            : data.inventory < 5
                            ? 'Low stock warning!'
                            : ''
                        "
                      >
                        {{ data.inventory }}
                      </span>

                      <p
                        class="card-text text-center mb-1"
                        style="color: blueviolet; font-size: 20px"
                      >
                        up- {{ currencyFormat(data.unit_price) }}
                      </p>
                      <p
                        class="card-text text-center mb-1"
                        style="color: blueviolet; font-size: 20px"
                      >
                        wp - {{ currencyFormat(data.wholesales_price) }}
                      </p>
                    
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="pList2" class="card-body p-1" style="overflow-y: scroll">
              <div
                class="row row-cols-2 row-cols-sm-3 row-cols-lg-4 row-cols-xl-5 g-1"
              >
                <div
                  class="col"
                  v-for="data in productList2" :key="data.id"
                  style="cursor: pointer"
                  @click="addCompletedOrders(data.id)"
                >
                  <div
                    class="card h-80 mb-0"
                    
                  >
          
                    <div
                      class="card-body"
                      style="font-size: 18px; padding: 2px"
                    >
                      <p class="card-text text-center mb-1"> Receipt #: {{ data?.transaction_id }}</p>

                      <p
                        class="card-text text-center mb-1"
                        style="color: blueviolet; font-size: 20px"
                      >
                        Total: {{ currencyFormat(data.total) }}
                      </p>
                  
                      <p
                        class="card-text text-center mb-1"
                        style="color: blueviolet; font-size: 18px"
                      >
                       Discount:  {{ currencyFormat(data.total_discount) }}
                      </p>
                      
                    
    
                      <p> <span>
                       Grand Total: {{ currencyFormat(data.grand_total) }}
                      </span>
                    </p>
                      
                    </div>
                  </div>
                </div>
              </div>
            </div>


            <div v-if="pList3" class="card-body p-1" style="overflow-y: scroll">
              <div
                class="row row-cols-2 row-cols-sm-3 row-cols-lg-4 row-cols-xl-5 g-1"
              >
                <div
                  class="col"
                  v-for="data in productList3" :key="data.id"
                  style="cursor: pointer"
                  @click="getDraftedOrders(data)"
                >
                  <div
                    class="card h-80 mb-0"
                    
                  >
          
                    <div
                      class="card-body"
                      style="font-size: 17px; padding: 1px"
                    >
                      <span class="card-text text-center mb-1">Receipt #: {{ data?.transaction_id }}</span><br/>
                      
                     <span> </span> <span v-for="dat in data?.items" :key="dat.id"> <span>
                      {{ (dat.product.name) }} - {{ currencyFormat(dat.subtotal) }}<br/>
                      
                      </span>
                    </span>
                      
                      <p
                        class="card-text text-center mb-1"
                        style="color: blueviolet; font-size: 20px"
                      >
                        Grand total: {{ currencyFormat(data.grand_total) }}
                      </p>

                      
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        
        <div :class="styleOrder2">
          <div class="card mb-0" style="height: 88vh">
             

<div class="d-flex align-items-center gap-3">

  <button type="button"   class="btn btn-success d-flex align-items-center justify-content-center px-1 py-1 my-1 rounded-3 shadow-sm"  data-bs-toggle="modal" data-bs-target="#staticBackdrop">


  <i class="bi bi-calculator fs-1"></i>

</button>
                <div class="d-none d-md-flex align-items-center gap-2">
                  <i class="bi bi-alarm-fill text-secondary fs-6"></i>
                  <span class="text-secondary small">
                    {{ auth?.user?.server_time }}
                  </span>
                </div>
            <!-- Price Mode Switch -->

                <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
    <label class="form-check form-switch mb-0">
        <input
            class="form-check-input"
            type="checkbox"
            v-model="useWholesalePrice"
          
        >
        <span class="ms-2 fw-bold">
            {{ useWholesalePrice ? 'Wholesale Price' : 'Unit Price' }}
        </span>
    </label>
</div>

  </div>





         
            <div class="card-body p-0 cashier-menu" style="overflow-y: scroll">
              <table class="table">
                <thead>
                  <tr class="table-dark">
                    <th
                      style="width: 10px; font-weight: 500; font-size: 20px; padding: 5px !important"
                      class="pb-1"
                    >
                      <input
                        type="checkbox"
                        :checked="
                          checkList &&
                          checkList.length == order?.order_detail_temps.length
                        "
                        v-if="
                          order?.order_detail_temps &&
                          order?.order_detail_temps.length > 0
                        "
                        :indeterminate="
                          checkList &&
                          checkList.length > 0 &&
                          checkList.length < order?.order_detail_temps.length
                        "
                        style="width: 18px; height: 18px; margin-top: 3px"
                        @change="checkAll($event)"
                      />
                    </th>
                    <th style="padding: 5px !important">Description</th>
                    <th style="width: 50px; padding: 5px !important">QTY</th>
                    <th
                      class="text-end"
                      style="width: 90px; padding: 5px !important"
                    >
                      U.P (&#8358;)
                    </th>
                    <th
                      class="text-end"
                      style="width: 70px; padding: 5px !important"
                    >
                      DC(%)
                    </th>
                    <th
                      class="text-end"
                      style="width: 100px; padding: 5px !important"
                    >
                      Total (&#8358;) <span></span>
                    </th>
                    <th style="width: 10px; padding: 5px !important"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="data in order" :key="data.id">
                    <td class="pb-0" style="padding: 5px !important">
                      <input
                        type="checkbox"
                        :id="data?.id"
                        :value="data?.id"
                        v-model="checkList"
                        style="width: 18px; height: 18px; margin-top: 3px"
                      />
                    </td>
                    <td style="padding: 5px !important">
                      {{ data?.description }}
                    </td>
                    <td style="padding: 5px !important">
                      <input
                        type="number"
                        min="1"
                        style="
                          border: none;
                          appearance: none;
                          background: #e9ecef;
                        "
                        class="form-control p-0 text-center"
                        @input="updateQty($event, data?.id)"
                        v-model="data.qty"
                      />
                    </td>
                    <td class="text-end" style="padding: 5px !important">
                      {{ numberFormat(data?.unit_price, 2) }}
                    </td>
                    <td style="padding: 0 10px !important" class="text-end">
                      <input
                        type="number"
                        min="0"
                        style="
                          border: none;
                          appearance: none;
                          background: #e9ecef;
                        "
                        class="form-control p-0 text-center"
                        v-model="data.discount"
                        @input="updateDetailDiscount($event, data?.id)"
                      />
                    </td>
                    <td class="text-end" style="padding: 5px !important">
                      {{
                        numberFormat(
                          data?.unit_price *
                            data?.qty *
                            (1 - data?.discount / 100),
                          2
                        )
                      }}
                    </td>
                    <td style="padding: 5px !important">
                      <i
                        class="bi bi-trash"
                        style="color: red; cursor: pointer"
                        @click="deleteData(data.id)"
                      ></i>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
         
         



            <div
              class="card-footer p-1 text-dark"
              style="background: whitesmoke"
              v-if="order"
            >
            <div class="d-flex justify-content-between">
  <span class="fw-bold"><span class="text-danger"></span> </span>
  <span class="fw-bold mx-5" v-if="order?.length > 0">
   Sub Total:  <span class="text-danger"> {{ currencyFormat(sub_total) }} </span>
  </span>
</div>

            <div
              class="card-footer p-1 text-dark"
              style="background: whitesmoke"
              v-if="order"
            >
            <div class="d-flex justify-content-between">
  <span class="fw-bold">VAT: <span class="text-danger">{{ vat }}%</span> </span>
  <span class="fw-bold mx-5" v-if="order?.length > 0">
   Vat Amount:  <span class="text-danger"> {{ currencyFormat(vat_amount) }} </span>
  </span>
</div>
</div>

              <table class="table mb-0" style="background: whitesmoke">
                <tbody>
                  <tr>
                    <td>
                      <div
                        class="card-footer p-2 text-dark"
                        style="background: whitesmoke"
                        v-if="order"
                      >
                        <div
                          class="d-flex align-items-center justify-content-between flex-wrap gap-3"
                        >
                          <!-- Discount -->
                          <div class="d-flex align-items-center">
                            <label class="me-2 mb-0">Discount (%) :</label>
                            <input
                              type="number"
                              min="0"
                              class="form-control text-center"
                              style="width: 80px"
                              v-model="total_discount"
                              @input="updateOrderDiscount($event)"
                              :disabled="isOrderEmpty"
                            />
                          </div>

                          <!-- Payment Mode -->
                          <div class="d-flex align-items-center">
                            <label class="me-2 mb-0">Mode of Payment :</label>

                            <select
                              class="form-select"
                              style="width: 150px"
                              v-model="payment.mode_of_payment"
                              :disabled="isOrderEmpty"
                            >
                              <option
                                v-for="mode in modes_of_payment"
                                :key="mode"
                                :value="mode"
                              >
                                {{ mode }}
                              </option>
                            </select>
                          </div>

                          <!-- Total -->
                          <div class="d-flex align-items-center">
                            <strong class="me-2">Total (₦):</strong>
                            <strong class="text-danger">
                              {{ numberFormat(Number(grand_total), 2) }}
                            </strong>
                          </div>
                        </div>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="card-header p-2 d-block d-lg-block">
              <div class="row row-cols-4 g-1">
                <div>
                  <button
                    class="btn btn-secondary w-100"
                    @click="draftedOrders"
                    title="Search Drated Orders"
                  >
                    Processing Orders
                  </button>
                </div>
                <div>
                  <button
                    
                    title="previous order"
                    class="btn btn-primary w-100"
                    @click="getCompletedOrders()"
                  >
                    Completed Orders
                  </button>
                </div>
                <div>
                  <button
                    class="btn btn-warning w-100"
                    title="Print"
                    :disabled="order?.length == 0"
                    @click="printInvoice()"
                  >
                    Print  <span v-if="order[0]?.order_id">Receipt</span> <span v-else>Invoice</span>
                  </button>
                </div>
                <div>
                  <button
                    class="btn btn-success w-100"
                    title="Payment"
                    :disabled="order?.length == 0"
                    @click="makePayment"
                  >
                    Process Order
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

      
        
      </div>
    </main>




<!--  calculator Modal -->
<div class="modal fade" id="staticBackdrop" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Calculator</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-0">
        <div class="calculator-box w-auto ">
          <Calculator />
        </div>
      </div>

    </div>
  </div>
</div>

  </div>
</template>
<script setup>
import Loading from "vue-loading-overlay";
import "vue-loading-overlay/dist/css/index.css";
import {
  onMounted,
  onUnmounted,
  ref,
  watch,
  computed,
  reactive,
  nextTick,
} from "vue";
import {
  clearForm,
  currencyFormat,
  dateFormat,
  numberFormat,
  setFocus,
  dateFormat2
} from "../../helper.js";
import { Modal } from "bootstrap";
import ShareModal from "../share/Modal.vue";
import printJS from "print-js";
import { useAuthStore } from "@/store/auth";
import debounce from "lodash/debounce";
//import { nanoid } from 'nanoid';
import { customAlphabet } from "nanoid";
import { useStocktore } from "@/store/stockalerts";

import { useVatStore } from "@/store/vat";


import { useNetWorkStore } from '@/store/network';

import Calculator from '../share/Calculator.vue';




const network = useNetWorkStore();


const store2 = useStocktore();

const store = useVatStore();


const vat = computed(() => store.vatValue);


const searchInput = ref(null);


//  alert(vat)

const showNow = ref(false);

const showCalculator = () => {
    showNow.value = !showNow.value;
}


const formModalInstance = ref(null);
const formModal = ref(null);
const autofocus = ref(null);
const autofocus1 = ref(null);
const messageBox = ref(null);
const auth = useAuthStore();
const categoryList = ref([]);
const productList = ref([]);

const productList2 = ref([]);
const productList3 = ref([]);

const productListBase = ref([]);
const arrayTable = ref([]);
const search = ref("");

const searchCompletedOrder = ref(false);
const searchDraftedOrder = ref(false);
const searchProduct = ref(true);


const categoryId = ref(0);
const tableModal = ref(null);
const tableModalInstance = ref(null);
const paymentModal = ref(null);
const paymentModalInstance = ref(null);
const deleteModal = ref(null);
const deleteModalInstance = ref(null);
const table_id = ref(0);
const old_cate_id = ref(0);
const checkList = ref([]);
const isLoading = ref(false);
const errors = ref({});
const receive_amount_error = ref(null);
const order = ref([]);

const showOrder = ref(true);

// const mode_of_payment = ref("Cash");

const modes_of_payment = ref(["Cash", "Transfer", "POS"]);

// const grand_total = ref(0);

const net_amount = ref(0);

const receiptData = ref(null);

const total_discount = ref(0);

const useWholesalePrice = ref(false);


const pList =  ref(true); 
const pList2 =  ref(false); 
const pList3  =ref(false);
const searchProduct2 = ref("Search Product .......")

//  const total_discount = ref(0);

// const styleOrder = ref("col-xl-8 col-lg-8 col-md-8")

// const styleOrder2 = ref("")

const styleOrder = "col-xl-7 col-lg-6";
const styleOrder2 = "col-xl-5 col-lg-6";


const transactionId = ref("");
const form = ref({
  old_password: null,
  new_password: null,
  new_password_confirmation: null,
});

// const receive_amount = ref(0);

const isOrderEmpty = computed(() => {
  return !Array.isArray(order.value) || order.value.length === 0;
});


// const switchPriceMode = () => {

// // useWholesalePrice.value = !useWholesalePrice.value;

// if (!order.value || order.value.length === 0) {
//     return;
// }

// order.value.forEach(item => {
//     item.unit_price = useWholesalePrice.value
//         ? item.wholesales_price
//         : item.unit_price;
// });

// };

watch(useWholesalePrice, (isWholesale) => {

if (!order.value?.length) return;

order.value.forEach(item => {
  item.unit_price = useWholesalePrice.value
            ? Number(item.product?.wholesales_price ?? item.unit_price)
            : Number(item.product?.unit_price ?? item.unit_price);

});

});

onMounted( async () => {

  searchInput.value?.focus();

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

  if (tableModal.value) {
    tableModalInstance.value = new Modal(tableModal.value);
    tableModal.value.addEventListener("hide.bs.modal", () => {
      document.activeElement?.blur();
    });
  }

  if (deleteModal.value) {
    deleteModalInstance.value = new Modal(deleteModal.value);
    deleteModal.value.addEventListener("hide.bs.modal", () => {
      document.activeElement?.blur();
    });
  }

  if (paymentModal.value) {
    paymentModalInstance.value = new Modal(paymentModal.value);
    paymentModal.value.addEventListener("shown.bs.modal", () => {
      setFocus(autofocus1);
    });
    paymentModal.value.addEventListener("hide.bs.modal", () => {
      document.activeElement?.blur();
    });
    paymentModal.value.addEventListener("hidden.bs.modal", () => {
      clearForm(form.value);
      receive_amount_error.value = null;
    });
  }

  await Promise.all([
    store2.getAllStockAlerts(),
    store.fetchVat(),
    network.init()

  ]);


  // getData();
  // table_id.value = sessionStorage.getItem("table_id");
  // selectTable(table_id.value);
});

onUnmounted(() => {
  if (formModalInstance.value) {
    formModalInstance.value.dispose();
  }
  if (tableModalInstance.value) {
    tableModalInstance.value.dispose();
  }
  if (deleteModalInstance.value) {
    deleteModalInstance.value.dispose();
  }
  if (paymentModalInstance.value) {
    paymentModalInstance.value.dispose();
  }
});


const order_id = ref(null);

const vat_amount2 = ref(null);

const total_discount_amount = computed(() => {
  if (!Array.isArray(order.value)) return 0;

  return order.value.reduce((sum, item) => {
    const lineTotal = item.qty * item.unit_price;
    const discountAmount = lineTotal * (item.discount / 100);
    return sum + discountAmount;
  }, 0);
});

const total = computed(() => {
  if (!Array.isArray(order.value)) return 0;




  return order.value.reduce((sum, item) => {
    return sum + item.qty * item.unit_price;
  }, 0);
});




const total_discount_percent = computed(() => {
  if (total.value === 0) return 0;

  return (total_discount_amount.value / total.value) * 100;
});


const sub_total = computed(() => {
  if (!Array.isArray(order.value)) return 0;

  return order.value.reduce((total, item) => {
    const qty = Number(item.qty) || 0;
    const price = Number(item.unit_price) || 0;
    const discount = Number(item.discount) || 0;

    const lineTotal = qty * price;
    const discountAmount = lineTotal * (discount / 100);

    return total + (lineTotal - discountAmount);
  }, 0);
});

// const vat_amount = computed(() => {
//   const subtotal = sub_total.value;

//   if(order_id.value){
//     return vat_amount2.value;
//   }
//   return subtotal * ((Number(vat.value) || 0) / 100);
// });

const vat_amount = computed(() => {
  const subtotal = Number(sub_total.value) || 0;

  if (order_id.value) {
    return Number(vat_amount2.value) || 0;
  }

  const vatRate = Number(vat.value) || 0;
  const vatAmount = subtotal * (vatRate / 100);

  return Number(vatAmount.toFixed(2));
});


// const grand_total = computed(() => {
//   const subtotal = sub_total.value;

//   const vatRate = Number(vat.value) || 0;
//   const vatAmount = subtotal * (vatRate / 100);

//   if(order_id.value){
//     return Number((sub_total.value) +  (vat_amount2.value));
//   }

//   return Number((subtotal + vatAmount).toFixed(2));
// });

const grand_total = computed(() => {
  const subtotal = Number(sub_total.value) || 0;
  const vatRate = Number(vat.value) || 0;

  const vatAmount = order_id.value
    ? Number(vat_amount2.value) || 0
    : subtotal * (vatRate / 100);

  return Number((subtotal + vatAmount).toFixed(2));
});


// let typingTimer = null;



// const handleSearch = (event) => {
//   // If Enter key pressed (barcode scanner usually sends Enter)
//   if (event?.key === "Enter") {
//     clearTimeout(typingTimer);
//     if (search.value) fetchProducts();
//     return;
//   }

//   // Manual typing debounce
//   clearTimeout(typingTimer);

//   typingTimer = setTimeout(() => {
//     if (search.value) fetchProducts();

//     // Optional: handle other search types
//     if (searchCompletedOrder.value) searchCompletedOrders();
//     // if (searchDraftedOrder.value) searchDraftedOrders();
//   }, 2000);
// };

let typingTimer = null;
let lastKeyTime = 0;
let buffer = '';

const SCAN_THRESHOLD = 50;  // ms (scanner is VERY fast)


// const handleSearch = (e) => {
//   const now = Date.now();

//   // Detect fast input (scanner)
//   if (now - lastKeyTime < SCAN_THRESHOLD) { 
//     buffer += e.key;
//   } else {
//     buffer = e.key;
//   }

//   lastKeyTime = now;

//   // 🔥 SCANNER → Enter key triggers instant search
//   if (e.key === 'Enter') {
//     e.preventDefault();

//     const scanned = buffer.replace('Enter', '').trim();

//     if (scanned) {
//       search.value = scanned;
//       fetchProducts(); // 🚀 instant
//     }

//     buffer = '';
//     return;
//   }

//   // 🧍 HUMAN → debounce typing
//   clearTimeout(typingTimer);

//   typingTimer = setTimeout(() => {
//     if (search.value) {
//       fetchProducts();
//     }
//   }, 400); // faster UX
// };


const handleSearch = (e) => {
  const now = Date.now();
  const key = e.key;

  // 🔥 Ignore modifier keys
  if (['Shift', 'Control', 'Alt', 'Meta'].includes(key)) return;

  // 🔥 Detect fast typing (scanner)
  if (now - lastKeyTime < SCAN_THRESHOLD) {
    buffer += key;
  } else {
    buffer = key;
  }

  if(pList2.value){
    searchCompletedOrders();
    }


  lastKeyTime = now;

  // ✅ SCANNER MODE (Enter triggers scan)
  if (key === 'Enter') {
    e.preventDefault();

    const scanned = buffer
      .replace(/Enter/g, '')  // clean safety
      .replace(/\s+/g, '')    // remove hidden spaces
      .trim();

    buffer = '';

    if (!scanned) return;

    search.value = scanned;

    // 🚀 instant fetch
    fetchProducts(true); // pass scanner flag
  
    return;
  }

  // 🧠 HUMAN MODE → debounce
  clearTimeout(typingTimer);

  typingTimer = setTimeout(() => {
    const value = search.value?.trim();

    if (value && value.length >= 2) {
      fetchProducts(false);
    }
  }, 300);
};

// const handleBarcodeScan = () => {
//   if (!search.value) return;

//   // Optional: clear any pending typing timer
//   clearTimeout(typingTimer);

//   // Fetch products immediately for barcode
//   fetchProducts();
// };

const createOrder = () => {
  // const nanoid = customAlphabet('1234567890abcdef', 12)

  // transactionId.value  = nanoid()
  searchProduct2.value = "Search Product ....."

  order_id.value = null;
  useWholesalePrice.value = false;
 
  pList.value =  true; 
  pList2.value =  false; 
  pList3.value =  false; 

  searchCompletedOrder.value = false;
  searchDraftedOrder.value = false;
  searchProduct.value = true;


  productList2.value = [];
  

  const now = new Date();
  const datePart = now.toISOString().slice(0, 10).replace(/-/g, "");
  const randomPart = Math.floor(Math.random() * 10000)
    .toString()
    .padStart(4, "0");

  transactionId.value = `ORD-${datePart}-${randomPart}`;

  order.value = [];

  // alert(transactionId.value)
};

// load data
//

// const fetchProducts = async () => {
//   if (!search.value) {
//     productList.value = [];
//     return;
//   }

//   isLoading.value = true;

//   try {
//     const response = await axios.post("api/cashier", {
//       search: search.value,
//     });

//     if (response.data.success) {
//       productList.value = response.data.products;
//     }
//   } catch (ex) {
//     console.log(ex);
//   } finally {
//     isLoading.value = false;
//   }
// };



// const fetchProducts = async () => {
//   if (!search.value) {
//     productList.value = [];
//     return;
//   }

//   isLoading.value = true;

//   try {
//     const response = await axios.post("api/cashier", {
//       search: search.value,
//       isBarcode: /^\d+$/.test(search.value), // true if all digits
//     });

//     if (response.data.success) {
//       productList.value = response.data.products;
//     }
//   } catch (ex) {
//     console.log(ex);
//   } finally {
//     isLoading.value = false;
//   }
// };


            // if($request->useWholesalePrice){
            //     $price =  $product->wholesales_price;
            // }else{

            //     $price =  $product->unit_price;

            // }

const fetchProducts = async (isScanner = false) => {
  const query = search.value?.trim();

  if (!query) {
    productList.value = [];
    return;
  }

  isLoading.value = true;

  try {
    const response = await axios.post("api/cashier", {
      search: query,
      isBarcode: /^\d{6,}$/.test(query), // stricter: min 6 digits
    });

    if (!response.data?.success) return;

    const products = response.data.products || [];

    // 🔥 SCANNER MODE → auto add instantly
    if (isScanner && products.length === 1) {
      addToCart(products[0]);   // 👉 your function

      // reset
      search.value = '';
      productList.value = [];

      playBeep(); // optional 🔊
      return;
    }

    // 🧠 TYPING MODE → show list
    productList.value = products;

  } catch (ex) {
    console.error("Search error:", ex);
  } finally {
    isLoading.value = false;
  }
};

// add or create
const openModal = () => {
  formModalInstance.value.show();
};

// submit form
const changePassword = () => {
  isLoading.value = true;
  axios
    .post("api/auth/change-password", form.value)
    .then((response) => {
      if (response.data.success) {
        formModalInstance.value.hide();
        messageBox.value.showModal(
          1,
          null,
          null,
          "Your password has been changed successfully"
        );
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

const menuByCateId = (id) => {
  categoryId.value = id;
  filterMenu();
  if (id != old_cate_id.value) {
    document
      .getElementById("cate_" + old_cate_id.value)
      .classList.remove("active");
    document.getElementById("cate_" + id).classList.add("active");
    old_cate_id.value = id;
  }
};

// Filter Menu by its category
const filterMenu = () => {
  if (categoryId.value == 0) productList.value = productListBase.value;
  else
    productList.value = productListBase.value.filter(
      (v) => v.product_category_id == categoryId.value
    );
  if (search.value) {
    productList.value = productList.value.filter((v) =>
      v.name.toLowerCase().includes(search.value.toLowerCase())
    );
  }
};

// get table status
const getStatus = (status) => {
  if (status == 2) return "text-bg-secondary";
  else if (status == 1) return "text-bg-danger";
  else return "text-bg-success";
};

// show table list
const showTable = (id = 0) => {
  arrayTable.value = [];
  isLoading.value = true;
  axios
    .get("api/cashier/show-table/" + id)
    .then((response) => {
      if (response.data.success) {
        Object.assign(arrayTable.value, response.data.data);
        tableModalInstance.value.show();
      }
    })
    .catch((ex) => {
      console.log(ex);
    })
    .finally(() => {
      isLoading.value = false;
    });
};

// select table
const selectTable = (id) => {
  axios
    .post("api/cashier/select-table", {
      old_table_id: table_id.value,
      new_table_id: id,
      ids: JSON.stringify(checkList.value),
    })
    .then((response) => {
      if (response.data.success) {
        table_id.value = id;
        order.value = response.data.data;
        sessionStorage.setItem("table_id", id);
        checkList.value = [];
        tableModalInstance.value.hide();
      }
    })
    .catch((ex) => {
      console.log(ex);
    });
};

// // add product to order list
// const addToOrder = (id) => {
//   if (!transactionId.value) {
//     messageBox.value.showModal(2, null, null, 'click Create New Order');
//     return;
//   }

//   axios.post("api/cashier/add-to-order", {
//     transactionId: transactionId.value,

//     product_id: id
//   })
//     .then((response) => {
//       if (response.data.success) {
//         order.value = response.data.data;
//         // grand_total.value = response.data.summary.grand_total;
//         // total_discount.value = response.data.summary.total_discount;
//         // net_amount.value = response.data.summary.net_amount;

//       }
//     })
//     .catch((ex) => {
//       console.log(ex);
//     });
// };

const addToOrder = (id) => {
  if (!transactionId.value) {
    messageBox.value.showModal(2, null, null, "Click Create New Order");
    return;
  }

  axios
    .post("api/cashier/add-to-order", {
      transactionId: transactionId.value,
      product_id: id,
      useWholesalePrice: useWholesalePrice.value,
    })
    .then((response) => {
      const res = response.data;

      if (!res.success) {
        messageBox.value.showModal(2, null, null, res.message);
        return;
      }

      order.value = res.data;
    })
    .catch((error) => {

      if (error.response) {
        messageBox.value.showModal(
          2,
          null,
          null,
          error.response.data.message || "An error occurred."
        );
      } else {
        messageBox.value.showModal(
          2,
          null,
          null,
          "Unable to connect to the server."
        );
      }

      console.error(error);
    });
};


const getProductImage = (image) => {
  if (image)
    return (
      "background: url('storage/"+image + "') no-repeat center; height:80px"
    );
  else
    return "background: url('./images/default.png') no-repeat center; height:80px";
};

// delete
const deleteData = (id) => {
  messageBox.value.showModal(4, () => {
    axios
      .delete("api/cashier/delete-order/" + id + "/" + transactionId.value)
      .then((response) => {
        if (response.data.success) {
          order.value = response.data.data;
        }
      })
      .catch((ex) => {
        console.log(ex);
      });
  });
};

// update order quantity
const updateQty = (e, id) => {
  axios
    .post("api/cashier/update-order-qty", {
      id: id,
      qty: e.target.value,
      transaction_id: transactionId.value,
    })
    .then((response) => {
      if (response.data.success) {
        order.value = response.data.data;
      }
    })
    .catch((ex) => {
      console.log(ex);
    });
};


//search orders




// const addCompletedOrders = async (id) => {
//   try {

//     if (!id) return;

//     isLoading.value = true;

//   const response = await axios.post("api/cashier/addCompletedOrders", {
//      id
//   });

//     const res = response.data;

//     if (!res.success) {
//       console.warn(res.message || "Unable to load completed order");
//       return;
//     }

//     order.value = res.data;

//     if(order.value){

//       order_id.value = res?.data[0]?.order_id;

//       vat_amount2.value = res.data[0]?.order?.vat_amount;

//       // alert(vat_amount2)

//      // alert(order_id.value)
//     }

//   } catch (error) {

//     console.error("Add completed order error:", error);

//   } finally {

//     isLoading.value = false;

//   }
// };


// const addCompletedOrders = async (id) => {
//   if (!id) return;

//   isLoading.value = true;

//   try {
//     const response = await axios.post("api/cashier/addCompletedOrders", { id });
//     const res = response.data;

//      const orderData = res.data;

//     transactionId.value = orderData[0]?.transaction_id;

//     if (!res?.success) {
//       console.warn(res?.message || "Unable to load completed order");
      
//       // Reset state to avoid stale data
//       order.value = null;
//       order_id.value = null;
//       vat_amount2.value = 0;
//       return;
//     }

//     const data = res.data;

//     // Normalize: support both array and object response
//     const record = Array.isArray(data) ? data[0] : data;

//     if (!record) {
//       console.warn("No completed order data found");

//       order.value = null;
//       order_id.value = null;
//       vat_amount2.value = 0;
//       return;
//     }

//     order.value = data;

//     order_id.value = record.order_id ?? null;
//     vat_amount2.value = Number(record?.order?.vat_amount) || 0;

//   } catch (error) {
//     console.error("Add completed order error:", error);

//     // Optional: reset on error
//     order.value = null;
//     order_id.value = null;
//     vat_amount2.value = 0;

//   } finally {
//     isLoading.value = false;
//   }
// };


const resetOrderState = () => {
  order.value = null;
  order_id.value = null;
  transactionId.value = null;
  vat_amount2.value = 0;
};

const addCompletedOrders = async (id) => {
  if (!id) return;

  isLoading.value = true;

  try {
    const { data: res } = await axios.post(
      "api/cashier/addCompletedOrders",
      { id }
    );

    if (!res?.success) {
      console.warn(res?.message || "Unable to load completed order");
      resetOrderState();
      return;
    }

    // Normalize response (array or object)
    const payload = Array.isArray(res.data) ? res.data : [res.data];
    const record = payload[0];

    if (!record) {
      console.warn("No completed order data found");
      resetOrderState();
      return;
    }

    // Assign values
    order.value = payload;
    transactionId.value = record.transaction_id ?? null;
    order_id.value = record.order_id ?? null;
    vat_amount2.value = Number(record?.order?.vat_amount ?? 0);

  } catch (error) {
    console.error("Add completed order error:", error);
    resetOrderState();
  } finally {
    isLoading.value = false;
  }
};

const searchCompletedOrders = () => {
  pList.value =  false; 
  pList2.value =  true; 
  pList3.value = false;
  transactionId.value = null;
  order.value = [];
  productList.value = [];
  searchProduct.value = "Search ....Orders";
  axios
    .post("api/cashier/searchCompletedOrders",{
      search:search.value
    } )
    .then((response) => {
      if (response.data.success) {
        productList2.value = response.data.data;
      }
    })
    .catch((ex) => {
      console.log(ex);
    });
};


//get completed orders
const getCompletedOrders = () => {
  pList.value =  false; 
  pList2.value =  true; 
  pList3.value =  false; 

  transactionId.value = null;
  order.value = [];
  productList.value = [];
  searchProduct2.value = "Search ....Receipt Orders";
  searchCompletedOrder.value = true;
  searchDraftedOrder.value = false;
  searchProduct.value = false;

  axios
    .get("api/cashier/completedOrders")
    .then((response) => {
      if (response.data.success) {
        productList2.value = response.data.data;

    

      }
    })
    .catch((ex) => {
      console.log(ex);
    });
};


const draftedOrders = () => {
  order_id.value = null;
  pList.value =  false; 
  pList2.value =  false; 
  pList3.value =  true; 
  transactionId.value = null;
  order.value = [];
  productList.value = [];
  productList2.value = [];
  searchCompletedOrder.value = false;
  searchDraftedOrder.value = true;
  searchProduct.value = false;

  axios .get("api/cashier/draftedOrders")
    .then((response) => {
      if (response?.data?.success) {

const orderData = response.data.data;

productList3.value = orderData;

transactionId.value = orderData[0]?.transaction_id;


// alert(transactionId.value)

}
    })
    .catch((ex) => {
      console.log(ex);
    });
};




// const searchDraftedOrders = (data) =>{

// // axios .get("api/cashier/searchDraftedOrders")
// //   .then((response) => {
// //     if (response.data.success) {
//       order.value = data.items ?? [];
//       transactionId.value = data.items?.[0].transaction_id ?? null;

//   //   }
//   // })
//   // .catch((ex) => {
//   //   console.log(ex);
//   // });
// };


const getDraftedOrders = (data) =>{

  // axios .get("api/cashier/searchDraftedOrders")
  //   .then((response) => {
  //     if (response.data.success) {
        order.value = data?.items ?? [];
        transactionId.value = data.items?.[0].transaction_id ?? null;
    //   }
    // })
    // .catch((ex) => {
    //   console.log(ex);
    // });
};


const updateDetailDiscount = (e, id) => {
  e.preventDefault();

  axios
    .post("api/cashier/update-detail-discount", {
      id: id,
      discount: e.target.value,
      transaction_id: transactionId.value,
    })
    .then((response) => {
      if (response.data.success) {
        order.value = response.data.data;
      }
    })
    .catch((ex) => {
      console.log(ex);
    });
};

// update main order discount
const updateOrderDiscount = (e) => {
  e.preventDefault();

  axios
    .post("api/cashier/update-order-discount", {
      discount: e.target.value,
      transaction_id: transactionId.value,
    })
    .then((response) => {
      if (response.data.success) {
        order.value = response.data.data;
      }
    })
    .catch((ex) => {
      console.log(ex);
    });
};

// check all order items
const checkAll = (e) => {
  if (e.target.checked) {
    order.value.order_detail_temps.forEach((v) => {
      if (v.id) checkList.value.push(v.id);
    });
  } else {
    checkList.value = [];
  }
};

// print order invoice
const printInvoice = () => {
  printJS({
    printable: "print_invoice",
    type: "html",
    scanStyles: false,
    style: "#print_invoice{ display: block !important; }",
  });
};

// make payment
const makePayment = (e) => {
  e?.preventDefault();
  paymentModalInstance.value.show();
};

const printReceipt = () => {
  printJS({
    printable: "print_receipt",
    type: "html",
    scanStyles: false,
    style: "#print_receipt{ display:block !important; }",
  });

  // clear order after printing
  // order.value = null;
};

// confirm payment
// const confirmPayment = () => {
//   isLoading.value = true;
//   axios.post("api/cashier/confirm-payment", {formData})
//     .then((response) => {
//       if (response.data.success) {
//         order.value = response.data.data;
//         paymentModalInstance.value.hide();
//         setTimeout(() => {
//           printJS({
//             printable: "print_receipt",
//             type: "html",
//             scanStyles: false,
//             style: "#print_receipt{ display: block !important; }"
//           });
//           order.value = null;
//         }, 10);
//       } else {
//         if (response.data.errors && response.data.errors.receive_amount)
//           receive_amount_error.value = response.data.errors.receive_amount[0];
//         setFocus(autofocus1);
//       }
//     })
//     .catch((ex) => {
//       console.log(ex);
//       setFocus(autofocus1);
//     })
//     .finally(() => {
//       isLoading.value = false;
//     });
// };

const payment = reactive({
  mode_of_payment: "Cash",
  receive_amount: 0,
});

const formData = computed(() => ({
  transaction_id: transactionId.value,
  discount: total_discount_percent.value,
  total_discount: total_discount_amount.value,
  total: total.value,
  grand_total: grand_total.value,
  ...payment,
}));

const confirmPayment = async (e) => {
  try {
    e?.preventDefault();

    isLoading.value = true;

    const response = await axios.post(
      "api/cashier/confirm-payment",
      formData.value
    );

    const res = response.data;

  
    if (!res.success) {
      // validation errors
      if (res.errors?.receive_amount) {
        return  receive_amount_error.value = res.errors.receive_amount[0];
      }

      order.value  = [];
      transactionId.value = null;

      // backend general message
      if (res.message) {
        payment_error.value = res.message;
        alert(res.message); // or show toast
      }

      setFocus(autofocus1);
      return;
    }

    if (!res.data) return;

    order.value = res.data;

    receiptData.value = {
      items: res.data,
      total: res.data.total,
      grand_total: res.data.grand_total,
      discount: res.data.discount,
      total_discount: res.data.total_discount,
      receive_amount: res.data.receive_amount,
      mode_of_payment: res.data.mode_of_payment,
      sub_total: res.sub_total,       // ✅ now valid
      vat_amount: res.vat_amount,   
      vat:res.vat,  // ✅ now valid
    };

    paymentModalInstance.value?.hide();

    await nextTick();

    printReceipt();

    transactionId.value = null;
    order.value = [];
    receive_amount.value = 0;
    total_discount_percent.value = 0;
    createOrder();
  } catch (error) {
    // catch server 500 errors
    if (error.response?.data?.message) {
      alert(error.response.data.message);
    }

    console.error("Payment error:", error);
    setFocus(autofocus1);
  } finally {
    isLoading.value = false;
  }
};

// Watch
watch(
  () => search.value,
  (newValue, oldValue) => {
    filterMenu();
  }
);
</script>

<style scoped>
.receipt-summary td,
.receipt-summary th {
  text-align: right;
}


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
  gap: 50px;
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

.notification-dropdown {
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
}

.calculator-box {
  width: 100%;
}

.calculator-box > * {
  width: 100% !important;
  max-width: 100% !important;
}

.calculator {
  max-width: 100% !important;
  width: 100%;
}

@media print {
  .btn, .no-print {
    display: none !important;
  }

  .table {
    border-collapse: collapse !important;
  }

  .table td, .table th {
    border: 1px solid #000 !important;
  }
}

</style>