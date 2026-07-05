
<template>
    <svg ref="barcodeRef"></svg>
  </template>
  
<script setup>
import { onMounted, ref, watch } from 'vue'
import JsBarcode from 'jsbarcode'

const props = defineProps({
  code: String
})

const barcodeRef = ref(null)

const generate = () => {
  if (props.code && barcodeRef.value) {
    JsBarcode(barcodeRef.value, props.code, {
      format: "CODE128",
      width: 2,
      height: 40,
      displayValue: true
    })
  }
}

onMounted(generate)
watch(() => props.code, generate)
</script>

