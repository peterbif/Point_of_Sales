<script setup>
import { ref } from "vue";

const visible = ref(false);
const message = ref("");
let callback = null;

const showModal = (msg, cb) => {
  console.log("MODAL OPEN:", msg); // ✅ debug
  message.value = msg;
  callback = cb;
  visible.value = true;
};

const confirm = async () => {
  console.log("CONFIRM CLICKED"); // ✅ debug
  if (callback) await callback();
  close();
};

const close = () => {
  visible.value = false;
};

defineExpose({ showModal });
</script>

<template>
  <div v-if="visible" style="position:fixed;top:0;left:0;width:100%;height:100%;background:#0008;">
    <div style="background:#fff;padding:20px;margin:100px auto;width:300px;">
      
      <h3>WARNING</h3>

      <!-- ✅ MUST SHOW -->
      <p>{{ message }}</p>

      <button @click="confirm">YES</button>
      <button @click="close">NO</button>
    </div>
  </div>
</template>