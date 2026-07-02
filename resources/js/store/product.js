import { defineStore } from 'pinia'
import router from '../router'
import axios from '@/api/axios'
import { useToast } from "vue-toastification";
import { toast } from 'vue3-toastify';


export const useProductStore = defineStore('product', {
    state: () => ({
        products: [],

        productCategories:[],

        productSummary: [],

        stockAlerts: [],
        expiryAlerts: [],

        loading:false,
        stockLoading: false,
        expiryLoading: false

    
    }),

    actions: {
        async getAllProducts() {
            this.loading = true
      
            try {
              const response = await axios.get('/api/product/getAllProducts')
      
              this.products = response.data.data ?? response.data
      
            } catch (error) {
      
              toast.error("Failed to load products")
      
              console.error("Product Fetch Error:", error)
      
            } finally {
              this.loading = false
            }
          },




        async getAllProductCategories() {

            this.loading = true
      
            try {
              const response = await axios.get('/api/product/getAllProductCategories')
      
              this.productCategories = response.data.data ?? response.data
      
            } catch (error) {
      
              toast.error("Failed to load products")
      
              console.error("Product Fetch Error:", error)
      
            } finally {
              this.loading = false
            }
        },

        // async fetchStockAlerts() {
        //   this.stockLoading = true;
        //   try {
        //     const res = await axios.get("/api/products/stock-alerts");
        //     this.stockAlerts = res.data.data || [];
        //   } catch (e) {
        //     console.error(e);
        //   } finally {
        //     this.stockLoading = false;
        //   }
        // },
        
        // async fetchExpiryAlerts() {
        //   this.expiryLoading = true;
        //   try {
        //     const res = await axios.get("/api/products/expiry-alerts");
        //     this.expiryAlerts = res.data.data || [];
        //   } catch (e) {
        //     console.error(e);
        //   } finally {
        //     this.expiryLoading = false;
        //   }
        // }


      

    },
})