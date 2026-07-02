import { defineStore } from 'pinia'
import router from '../router'
import axios from '@/api/axios'
import { useToast } from "vue-toastification";
import { toast } from 'vue3-toastify';


export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        warning: null,
        token: localStorage.getItem('token') || null,

    
    }),

    actions: {
        // async login(credentials) {
        //     const response = await axios.post('/api/login', credentials)

        //     this.token = response.data.token;
        //     this.user  = response.data.user;
        //     this.warning = response.data.warning; // 👈 capture warning

        //     localStorage.setItem('token', this.token)
        //     axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`

        //     router.push('/')
        // },

        async login(credentials) {
            const response = await axios.post('/api/login', credentials)
        
            this.token   = response.data.token;
            this.user    = response.data.user;
            this.warning = response.data.warning || null;
        
            localStorage.setItem('token', this.token)
            axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
        
            // 🔥 SHOW WARNING AS TOAST
            if (this.warning) {
                toast.warning(this.warning, {
                    position: "top-right",
                    autoClose: 5000,
                    theme: "colored",
                });
            }
        
            router.push('/')
        },

        async getUser() {
            try {
                const response = await axios.get('/api/auth/user')
                this.user = response.data
            } catch {
                this.logout()
            }
        },

        async logout() {

            const toast = useToast();
            await axios.post('/api/auth/logout')

            this.user = null
            this.token = null

            localStorage.removeItem('token')
            delete axios.defaults.headers.common['Authorization']

            router.push('/login')

            toast.warning("You just logged out")
        },

        async saveData(form) {
            this.isLoading = true;
          
            try {
              const response = await axios.put(
                `api/reset-date/edit/${form.id}`,
                form
              );
          
              if (response.data.success) {
                return {
                  success: true,
                  id: response.data.id,
                };
              }
          
              this.errors = response.data.errors;
          
              return {
                success: false,
                errors: response.data.errors,
              };
          
            } catch (error) {
              console.error(error);
          
              return {
                success: false,
                errors: error.response?.data?.errors || {},
              };
          
            } finally {
              this.isLoading = false;
            }
          }
    }
})
