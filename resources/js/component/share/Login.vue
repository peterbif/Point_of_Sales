<template>
    <section class="vh-100"
        style="background-image: url('images/bg.jpg');background-position: center; background-repeat: no-repeat; background-size: cover;">
        <div class="container py-5 h-100">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div> <img src="images/favicon.webp"
                    style="position: absolute; top: 30px; left: 600px; height: 600px;" /></div>
                
                <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                    <div class="card shadow-2-strong" style="border-radius: 1rem; background: lightsteelblue;">
                        <div class="card-body p-5">
                           
                            <h1 class="mb-4 text-center fw-bold">Sign In</h1>
                            <label class="form-label fw-semibold"><i class="bi bi-person-check"></i> Username</label>

                            <div class="form-outline mb-4">
                                <input type="text"
                                    :class="['form-control form-control-lg', { 'is-invalid': errors?.username }]"
                                    placeholder="Username" ref="autofocus" v-model="form.username"
                                    :disabled="processing" v-on:keyup.enter="login" />
                                                            <!-- Error Message -->
            
                            </div>



   <div 
  v-if="errors?.subscription"
  class="mt-3 p-3 rounded shadow text-white"
  style="background: linear-gradient(90deg, #ff416c, #ff4b2b);"
>
  <div class="fw-semibold mb-2">
    ⚠️ {{ errors.subscription[0] }}
  </div>

  <div class="d-flex flex-column gap-2">

    <!-- Yearly -->
    <div class="d-flex align-items-center">
      <i class="bi bi-hand-index-thumb-fill text-warning me-2 point-down"></i>
      <a href="/documents/yearly_sub.pdf" target="_blank" class="text-white text-decoration-underline">
        Pay Yearly Subscription
      </a>
    </div>

    <!-- One-off -->
    <div class="d-flex align-items-center">
      <i class="bi bi-hand-index-thumb-fill text-warning me-2 point-down"></i>
      <a href="/documents/one-off-payment.pdf" target="_blank" class="text-white text-decoration-underline">
        Pay One-off
      </a>
    </div>

  </div>
</div>



<div 
  v-if="errors?.login"
  class="mt-3 p-3 rounded shadow text-white"
  style="background: linear-gradient(90deg, #ff416c, #ff4b2b);"
>
  <div class="fw-semibold mb-2">
    ⚠️ {{ errors.login[0] }}
  </div>


</div>



                            <div class="mb-4">
    <label class="form-label fw-semibold"> <i class="bi bi-lock"></i>   Password</label>

    <div class="input-group input-group-lg">
        <input 
            :type="showPassword ? 'text' : 'password'"
            :class="['form-control', { 'is-invalid': errors?.password }]"
            placeholder="Enter your password"
            v-model="form.password"
            :disabled="processing"
            @keyup.enter="login"
        />

        <!-- Toggle Button -->
        <button 
            class="btn btn-outline-secondary"
            type="button"
            @click="togglePassword"
            :disabled="processing"
            tabindex="-1"
        >
            <i :class="showPassword ? 'bi bi-eye' : 'bi bi-eye-slash'"></i>
        </button>

        <!-- Validation -->
        <!-- <div v-if="errors?.login" class="invalid-feedback d-block">
            {{ errors.login[0] }}
        </div> -->
    </div>
</div>

                         
                            <div class="d-grid mb-2">
                                <button class="btn btn-primary btn-lg" type="button" :disabled="processing"
                                    @click="login">
                                    <span v-show="processing" class="spinner-border spinner-border-sm" role="status"
                                        aria-hidden="true"></span>

                                        <VueSpinnerPie v-if="spin" size="50" color="#34c0eb" /> 
                                    Login
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { onMounted, reactive, ref } from "vue";
import { useRouter } from 'vue-router'
import { setFocus } from "../../helper";
import { useAuthStore } from '@/store/auth'

import { useToast } from "vue-toastification";
import { useNetWorkStore } from '@/store/network'


const processing = ref(false);
const form = reactive({
    username: null,
    password: null

});
const errors = ref(null);

const spin = ref(false);
const router = useRouter();
const autofocus = ref(null);


const toast = useToast();

const auth = useAuthStore()


const showPassword = ref(false);


// const network = useNetWorkStore()

const togglePassword = () => {

    return showPassword.value = !showPassword.value;
}
// const login = () => {
//     processing.value = true;
//     axios.get('/sanctum/csrf-cookie')
//         .then(() => {
//             spin.value = true;
//             axios.post('/login', form).then((response) => {
//                 errors.value = ""
//                 if (response.data.success) {
//                     router.push('/');
//                     spin.value = false;
//                 } else {
//                     errors.value = response.data.errors;
//                     setFocus(autofocus);
//                     spin.value = true;
//                 }
//             }).catch(response => {
//                 console.log(response);
//             }).finally(() => {
//                 processing.value = false;
                
//             })
//         });
// }

// const login = async () => {
//     processing.value = true;
//     spin.value = true;
//     errors.value = '';

//     try {
//         // 1. Fetch CSRF cookie first
//         // await axios.get('/sanctum/csrf-cookie');

//         // 2. Send login request
//         const response = await axios.post('api/login', form);

//         if (response.data.success) {
//             router.push('/');
//         } else {
//             errors.value = response?.data?.errors;
//             setFocus(autofocus);
//         }

//     } catch (err) {
//         console.log(err);
//     } finally {
//         processing.value = false;
//         spin.value = false;
//     }
// }

const login = async () => {
    processing.value = true
    spin.value = true
    errors.value = ''

    try {
        await auth.login(form)
        toast.success("Successfully login", {
        timeout: 2000
      });    } catch (err) {
        errors.value = err?.response?.data?.errors || {
            login: ['Login failed'],
        }
        setFocus(autofocus)
    } finally {
        processing.value = false
        spin.value = false
    }
}


onMounted(() => {
    document.body.style.display = "block";
    autofocus.value.focus();
});
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

.point-down {
  display: inline-block;
  font-size: 22px;
  animation: pointDown 0.8s infinite alternate;
}

@keyframes pointDown {
  from { transform: translateY(0); }
  to { transform: translateY(6px); }
}
</style>
