import { defineStore } from 'pinia'
import axios from '@/api/axios'
import { toast } from 'vue3-toastify'

export const useNetWorkStore = defineStore('network', {
    state: () => ({
        isOnline: navigator.onLine,
        isReachable: true,
        intervalId: null,
        _updateHandler: null
    }),

    actions: {

        // 🔹 ADD IT HERE
        updateBrowserStatus() {
            console.log('Network event fired:', navigator.onLine)
            this.isOnline = navigator.onLine

            if (!this.isOnline) {
                toast.error('You are offline')
            } else {
                toast.success('Back online')
            }
        },

        // // 🔹 YOUR CHECK FUNCTION
        // async checkConnection() {
        //     try {
        //         await axios.get('/ping')
        //         this.isReachable = true
        //     } catch (error) {
        //         this.isReachable = false
        //     }
        // },



        // 🔹 UPDATED INIT (VERY IMPORTANT)
        init() {
            // bind correctly using arrow function
            this._updateHandler = () => {
                this.updateBrowserStatus()
            }

            window.addEventListener('online', this._updateHandler)
            window.addEventListener('offline', this._updateHandler)

            // this.checkConnection()

            this.intervalId = setInterval(() => {
                // this.checkConnection()
            }, 10000)
        },

        destroy() {
            window.removeEventListener('online', this._updateHandler)
            window.removeEventListener('offline', this._updateHandler)

            if (this.intervalId) {
                clearInterval(this.intervalId)
            }
        }
    }
})