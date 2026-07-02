import axios from 'axios'

axios.defaults.baseURL = '/'
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

const token = localStorage.getItem('token')
if (token) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
}

export default axios
