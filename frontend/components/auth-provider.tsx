"use client"

import type React from "react"
import { createContext, useContext, useEffect, useState } from "react"
import type { User } from "@/types/auth"
import { authService } from "@/lib/auth"

interface AuthContextType {
  user: User | null
  loading: boolean
  login: (email: string, password: string) => Promise<void>
  register: (name: string, email: string, password: string) => Promise<void>
  loginWithFirebase: (token: string) => Promise<void>
  logout: () => Promise<void>
  refreshToken: () => Promise<void>
}

const AuthContext = createContext<AuthContextType | undefined>(undefined)

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const initAuth = async () => {
      try {
        const token = localStorage.getItem("auth_token")
        if (token) {
          const userData = await authService.getUser()
          setUser(userData)
        }
      } catch (error) {
        localStorage.removeItem("auth_token")
      } finally {
        setLoading(false)
      }
    }

    initAuth()
  }, [])

  const login = async (email: string, password: string) => {
    const response = await authService.login(email, password)
    localStorage.setItem("auth_token", response.token)
    setUser(response.user)
  }

  const register = async (name: string, email: string, password: string) => {
    const response = await authService.register(name, email, password)
    localStorage.setItem("auth_token", response.token)
    setUser(response.user)
  }

  const loginWithFirebase = async (token: string) => {
    const response = await authService.firebaseAuth(token)
    localStorage.setItem("auth_token", response.token)
    setUser(response.user)
  }

  const logout = async () => {
    try {
      await authService.logout()
    } catch (error) {
      // Continue with logout even if API call fails
    } finally {
      localStorage.removeItem("auth_token")
      setUser(null)
    }
  }

  const refreshToken = async () => {
    const response = await authService.refreshToken()
    localStorage.setItem("auth_token", response.token)
  }

  const value = {
    user,
    loading,
    login,
    register,
    loginWithFirebase,
    logout,
    refreshToken,
  }

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth() {
  const context = useContext(AuthContext)
  if (context === undefined) {
    throw new Error("useAuth must be used within an AuthProvider")
  }
  return context
}
