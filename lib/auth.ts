import axios from "axios"
import type { User, LoginResponse, RegisterResponse } from "@/types/auth"

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

// Handle token refresh on 401
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem("auth_token")
      window.location.href = "/auth/login"
    }
    return Promise.reject(error)
  },
)

export const authService = {
  async login(email: string, password: string): Promise<LoginResponse> {
    const response = await api.post("/auth/login", { email, password })
    return response.data.data
  },

  async register(name: string, email: string, password: string): Promise<RegisterResponse> {
    const response = await api.post("/auth/register", {
      name,
      email,
      password,
      password_confirmation: password,
    })
    return response.data.data
  },

  async firebaseAuth(firebaseToken: string): Promise<LoginResponse> {
    const response = await api.post("/auth/firebase", {
      firebase_token: firebaseToken,
    })
    return response.data.data
  },

  async logout(): Promise<void> {
    await api.post("/auth/logout")
  },

  async getUser(): Promise<User> {
    const response = await api.get("/auth/user")
    return response.data.data
  },

  async refreshToken(): Promise<{ token: string }> {
    const response = await api.post("/auth/refresh")
    return response.data.data
  },

  async forgotPassword(email: string): Promise<void> {
    await api.post("/auth/forgot-password", { email })
  },

  async resetPassword(token: string, email: string, password: string): Promise<void> {
    await api.post("/auth/reset-password", {
      token,
      email,
      password,
      password_confirmation: password,
    })
  },

  async verifyEmail(id: string, hash: string): Promise<void> {
    await api.get(`/auth/verify-email/${id}/${hash}`)
  },

  async resendVerification(): Promise<void> {
    await api.post("/auth/resend-verification")
  },
}
