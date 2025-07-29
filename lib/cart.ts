import axios from "axios"

const API_URL = process.env.NEXT_PUBLIC_API_URL

const api = axios.create({
  baseURL: `${API_URL}/api/v1`,
  headers: {
    "Content-Type": "application/json",
  },
  withCredentials: true,
})

// Add auth token to requests
api.interceptors.request.use((config) => {
  const token = localStorage.getItem("auth_token")
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

export const cartService = {
  async getCart() {
    const response = await api.get("/cart")
    return response.data.data
  },

  async addItem(serviceId: number, quantity: number, customerInfo: any) {
    const response = await api.post("/cart", {
      service_id: serviceId,
      quantity,
      customer_info: customerInfo,
    })
    return response.data.data
  },

  async updateItem(itemId: number, quantity: number) {
    const response = await api.patch(`/cart/${itemId}`, { quantity })
    return response.data.data
  },

  async removeItem(itemId: number) {
    await api.delete(`/cart/${itemId}`)
  },

  async clearCart() {
    await api.delete("/cart/clear")
  },
}
