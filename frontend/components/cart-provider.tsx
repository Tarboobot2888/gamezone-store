"use client"

import type React from "react"
import { createContext, useContext, useEffect, useState } from "react"
import type { CartItem } from "@/types/cart"
import { cartService } from "@/lib/cart"
import { useAuth } from "./auth-provider"

interface CartContextType {
  items: CartItem[]
  total: number
  count: number
  loading: boolean
  addItem: (item: Omit<CartItem, "id" | "user_id">) => Promise<void>
  updateItem: (id: string, quantity: number) => Promise<void>
  removeItem: (id: string) => Promise<void>
  clearCart: () => Promise<void>
  refreshCart: () => Promise<void>
}

const CartContext = createContext<CartContextType | undefined>(undefined)

export function CartProvider({ children }: { children: React.ReactNode }) {
  const [items, setItems] = useState<CartItem[]>([])
  const [total, setTotal] = useState(0)
  const [count, setCount] = useState(0)
  const [loading, setLoading] = useState(false)
  const { user } = useAuth()

  const refreshCart = async () => {
    if (!user) {
      setItems([])
      setTotal(0)
      setCount(0)
      return
    }

    try {
      setLoading(true)
      const cartData = await cartService.getCart()
      setItems(cartData.items)
      setTotal(cartData.total)
      setCount(cartData.count)
    } catch (error) {
      console.error("Failed to fetch cart:", error)
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    refreshCart()
  }, [user])

  const addItem = async (item: Omit<CartItem, "id" | "user_id">) => {
    try {
      await cartService.addItem(item)
      await refreshCart()
    } catch (error) {
      console.error("Failed to add item to cart:", error)
      throw error
    }
  }

  const updateItem = async (id: string, quantity: number) => {
    try {
      await cartService.updateItem(id, { quantity })
      await refreshCart()
    } catch (error) {
      console.error("Failed to update cart item:", error)
      throw error
    }
  }

  const removeItem = async (id: string) => {
    try {
      await cartService.removeItem(id)
      await refreshCart()
    } catch (error) {
      console.error("Failed to remove cart item:", error)
      throw error
    }
  }

  const clearCart = async () => {
    try {
      await cartService.clearCart()
      await refreshCart()
    } catch (error) {
      console.error("Failed to clear cart:", error)
      throw error
    }
  }

  const value = {
    items,
    total,
    count,
    loading,
    addItem,
    updateItem,
    removeItem,
    clearCart,
    refreshCart,
  }

  return <CartContext.Provider value={value}>{children}</CartContext.Provider>
}

export function useCart() {
  const context = useContext(CartContext)
  if (context === undefined) {
    throw new Error("useCart must be used within a CartProvider")
  }
  return context
}
