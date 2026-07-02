import { defineStore } from "pinia";
import axios from "axios";
import { useToast } from "vue-toastification";

const toast = useToast();

export const useStocktore = defineStore("stockstore", {
  state: () => ({
    isLoading: false,
    stockAlerts:[],
    expiryAlerts:[],
    alertedIds: new Set(), // 👈 prevents repeat alerts
    stockLoading: false,
    expiryLoading: false,
     errors: {},
     showAlertModal: false,
      selectedAlert: null,
        audio: null,
       unreadAlerts: [],   // 👈 new
       allStockAlerts:[],
       stockAlert:{},

  }),

  actions: {
    async saveData(form) {
      this.isLoading = true;
    
      try {
        const response = await axios[
          form.id > 0 ? "put" : "post"
        ]("api/stock-alerts/save", form);
    
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

    async fetchAllAlerts() {
      this.stockLoading = true;
      this.expiryLoading = true;
    
      try {
        const [stockRes, expiryRes] = await Promise.all([
          axios.get("/api/stock-alerts/stock-alerts"),
          axios.get("/api/stock-alerts/expiry-alerts")
        ]);
    
        this.stockAlerts = stockRes.data.data || [];
        this.expiryAlerts = expiryRes.data.data || [];


    // 🔥 CRITICAL: trigger alerts
    // this.handleAlerts(this.stockAlerts);
    // this.handleAlerts(this.expiryAlerts);
    
      } catch (e) {
        console.error(e);
      } finally {
        this.stockLoading = false;
        this.expiryLoading = false;
      }
    },



    


    async getStockAlert() {
      this.stockLoading = true;
      this.expiryLoading = true;
    
      try {
        const [stockAlert] = await Promise.all([
          axios.get("/api/stock-alerts/stockAlert"),
          
        ]);
    
        this.stockAlert = stockAlert.data.data || [];
    
    
      } catch (e) {
        console.error(e);
      } finally {
        this.stockLoading = false;
        this.expiryLoading = false;
      }
    },

    async getAllStockAlerts() {
      this.stockLoading = true;
      this.expiryLoading = true;
    
      try {
        const [allStockAlerts] = await Promise.all([
          axios.get("/api/stock-alerts/getAllAlerts"),
          
        ]);
    
        this.allStockAlerts = allStockAlerts.data.data || [];
      


    // 🔥 CRITICAL: trigger alerts
    // this.handleAlerts(this.stockAlerts);
    // this.handleAlerts(this.expiryAlerts);
    
      } catch (e) {
        console.error(e);
      } finally {
        this.stockLoading = false;
        this.expiryLoading = false;
      }
    },

    showToast(item) {
      const toast = useToast();
    
      toast.error(
        `${item.name} ${
          item.days_left !== undefined
            ? `expires in ${item.days_left} days`
            : `stock is critically low (${item.inventory})`
        }`,
        {
          timeout: 5000,
          position: "top-right"
        }
      );
    },

    showModal(item) {
      this.selectedAlert = item;
      this.showAlertModal = true;
    },

    
    clearAllAlerts() {
      this.unreadAlerts = [];
    },

    initAudio() {
      if (!this.audio) {
        this.audio = new Audio('/sounds/alert.mp3');
      }
    },
    
    playSound() {
      if (!this.audio) this.initAudio();
    
      this.audio.currentTime = 0;
      this.audio.play().catch(() => {});
    },

    // handleAlerts(items) {
    //   return items.filter(item =>
    //     !this.alertedIds.has(item.id) && this.isCritical(item)
    //   );
    // },

    triggerFullAlert(item) {
      this.playSound();
      this.showToast(item);
      this.showModal(item);
    },

    getAllAlerts() {
      return [...this.stockAlerts, ...this.expiryAlerts]
        .sort((a, b) => {
          const aCritical = this.isCritical(a) ? 1 : 0;
          const bCritical = this.isCritical(b) ? 1 : 0;
          return bCritical - aCritical;
        });
    },


    // async getStockAlerts() {
    //   this.stockLoading = true;
    //   this.expiryLoading = true;
    
    //   try {
    //     const [stockRes, expiryRes] = await Promise.all([
    //       axios.get("/api/stock-alerts/allStocksAlerts"),
    //     ]);
    
    //     this.stockAlerts = stockRes.data.data || [];
    //     this.expiryAlerts = expiryRes.data.data || [];
    
    //   } catch (e) {
    //     console.error(e);
    //   } finally {
    //     this.stockLoading = false;
    //     this.expiryLoading = false;
    //   }
    // },


   

    isCritical(item) {
      return (
        item.stock_status === 'very_low' ||
        item.expiry_status === 'critical' ||
        item.expiry_status === 'expired'
      );
    },


    // getUnreadCount() {
    //   return this.unreadAlerts.length;
    // },

    handleAlerts(items) {
      items.forEach(item => {
    
        if (!this.alertedIds.has(item.id) && this.isCritical(item)) {
    
          this.alertedIds.add(item.id);
    
          this.unreadAlerts.unshift(item); // 👈 add to bell list
    
          this.triggerFullAlert(item);
        }
    
      });
    }

    // async fetchExpiryAlerts() {
    //   this.isLoading = true;
    
    //   try {
    //     const res = await axios.get("/api/products/expiry-alerts");
    //     this.expiryAlerts = res.data.data;
    
    //     this.handleAlerts(this.expiryAlerts); // 👈 reuse same logic
    
    //   } catch (e) {
    //     console.error(e);
    //   } finally {
    //     this.isLoading = false;
    //   }
    // }


  },


  // getters: {
  //   getUnreadCount: (state) => state.unreadAlerts.length,
    
  //   allAlerts: (state) => [...state.stockAlerts, ...state.expiryAlerts],

  //   unreadCount: (state) => state.unreadAlerts.length,
  
  //   criticalAlerts: (state) =>
  //     state.unreadAlerts.filter(a =>
  //       a.stock_status === 'very_low' ||
  //       a.expiry_status === 'expired'
  //     )
  // },

  
});