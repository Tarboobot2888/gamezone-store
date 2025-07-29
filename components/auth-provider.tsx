"use client"

import type React from "react"
import { createContext, useContext, useEffect, useState } from "react"
import type { User } from "@/types/auth"
import { authService } from "@/lib/auth"
import { FirebaseAuth } from "@/lib/firebase"

interface AuthContextType {
  user: User | null
  loading: boolean
  login: (email: string, password: string) => Promise<void>
  register: (name: string, email: string, password: string) => Promise<void>
  logout: () => Promise<void>
  refreshUser: () => Promise<void>
}

const AuthContext = createContext<AuthContextType | undefined>(undefined)

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    // Check for existing auth token
    const token = localStorage.getItem("auth_token")
    if (token) {
      refreshUser()
    } else {
      setLoading(false)
    }

    // Listen for Firebase auth state changes
    const unsubscribe = FirebaseAuth.onAuthStateChanged((firebaseUser) => {
      if (firebaseUser && !user) {
        // Sync with backend
        firebaseUser.getIdToken().then((token) => {
          authService
            .firebaseAuth(token)
            .then((response) => {
              localStorage.setItem("auth_token", response.token)
              setUser(response.user)
              setLoading(false)
            })
            .catch(() => {
              setLoading(false)
            })
        })
      }
    })

    return () => unsubscribe()
  }, [user])

  const login = async (email: string, password: string) => {
    try {
      const response = await authService.login(email, password)
      localStorage.setItem("auth_token", response.token)
      setUser(response.user)
    } catch (error) {
      throw error
    }
  }

  const register = async (name: string, email: string, password: string) => {
    try {
      const response = await authService.register(name, email, password)
      localStorage.setItem("auth_token", response.token)
      setUser(response.user)
    } catch (error) {
      throw error
    }
  }

  const logout = async () => {
    try {
      await authService.logout()
      localStorage.removeItem("auth_token")
      setUser(null)
      // Also sign out from Firebase
      await FirebaseAuth.signOut()
    } catch (error) {
      // Even if API call fails, clear local state
      localStorage.removeItem("auth_token")
      setUser(null)
    }
  }

  const refreshUser = async () => {
    try {
      const userData = await authService.getUser()
      setUser(userData)
    } catch (error) {
      localStorage.removeItem("auth_token")
      setUser(null)
    } finally {
      setLoading(false)
    }
  }

  const value = {
    user,
    loading,
    login,
    register,
    logout,
    refreshUser,
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
