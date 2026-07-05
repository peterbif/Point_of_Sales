import { defineStore } from "pinia";
import axios from "axios";

export const useVatStore = defineStore("Vat", {
  state: () => ({
    isLoading: false,
    vats:[],
    errors: {},
    vat:{},
  }),

  actions: {
    async saveData(form) {
      this.isLoading = true;
    
      try {
        const response = await axios[
          form.id > 0 ? "put" : "post"
        ]("api/vats/save", form);
    
        if (response.data.success) {
          return {
            success: true,
            id: response.data.id,   // ✅ FIXED
          };
        } else {
          this.errors = response.data.errors;
          return {
            success: false,
            errors: response.data.errors,
          };
        }
      } catch (error) {
        console.error(error);
        return {
          success: false,
          error,
        };
      } finally {
        this.isLoading = false;
      }
    },

    async fetchVat() {
      this.isLoading = true;
    
      try {
        const res = await axios.get("/api/vats/getVat");
    
        // Convert the string to a number
        this.vat = parseFloat(res?.data?.data) || 0;
    
        console.log("VAT loaded:", this.vat);
    
      } catch (e) {
        console.error("VAT fetch failed:", e);
        this.vat = 0;
      } finally {
        this.isLoading = false;
      }
    }
  },

  getters:{

    vatValue: (state) => state.vat ?? 0,

    
  }
});