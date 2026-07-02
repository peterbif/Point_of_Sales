<template>
    <div>
      <!-- Product selection -->
      <div v-for="product in products" :key="product.id" class="product-item">
        <span>{{ product.name }}</span>
        <input
          type="number"
          v-model.number="quantities[product.id]"
          min="1"
          placeholder="Qty"
        />
        <button @click="printProduct(product)">Print</button>
      </div>
  
      <!-- Hidden table for printing -->
      <div id="barcode-table" style="display:none;">
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
    </div>
  </template>
  
  <script setup>
  import { ref } from "vue";
  import JsBarcode from "jsbarcode";
  
  // Props: your product list
  defineProps({
    products: {
      type: Array,
      required: true,
    },
  });
  
  // Store quantities for each product
  const quantities = ref({});
  
  // Rows to print
  let printRows = ref([]);
  
  // Print function
  const printProduct = (product) => {
    const qty = quantities.value[product.id] || 1;
    const itemsToPrint = Array(qty).fill(product);
  
    // Split into rows of 3 columns
    const rows = [];
    for (let i = 0; i < itemsToPrint.length; i += 3) {
      rows.push(itemsToPrint.slice(i, i + 3));
    }
    printRows.value = rows;
  
    // Wait for DOM to update
    setTimeout(() => {
      generateBarcodes();
      printBarcodes();
    }, 100);
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
    const printContents = document.getElementById("barcode-table").innerHTML;
    const originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    window.location.reload();
  };
  </script>
  
  <style>
  .product-item {
    margin-bottom: 10px;
  }
  .barcode-cell {
    width: 33%;
    text-align: center;
    padding: 10px;
    vertical-align: top;
  }
  .product-name {
    font-size: 12px;
    margin-top: 5px;
  }
  </style>