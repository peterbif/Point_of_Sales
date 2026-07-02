import { defineStore } from "pinia";
import axios from "axios";

export const useProductCategoryStore = defineStore("productCategory", {
  state: () => ({
    isLoading: false,
    errors: {},
  }),

  actions: {
    async saveData(form) {
      this.isLoading = true;
    
      try {
        const response = await axios[
          form.id > 0 ? "put" : "post"
        ]("api/product-category/save", form);
    
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
    }
  },
});