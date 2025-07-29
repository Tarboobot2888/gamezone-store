"use client"

import type React from "react"
import { createContext, useContext, useEffect, useState } from "react"
import type { CartItem } from "@/types/cart"
import { cartService } from "@/lib/cart"
import { useAuth } from "./auth-provider"
import { toast } from "sonner"

interface CartContextType {
  items: CartItem[]
  loading: boolean
  totalItems: number
  totalAmount: number
  addItem: (serviceId: number, quantity: number, customerInfo: any) => Promise<void>
  updateItem: (itemId: number, quantity: number) => Promise<void>
  removeItem: (itemId: number) => Promise<void>
  clearCart: () => Promise<void>
  refreshCart: () => Promise<void>
}

const CartContext = createContext<CartContextType | undefined>(undefined)

export function CartProvider({ children }: { children: React.ReactNode }) {
  const [items, setItems] = useState<CartItem[]>([])
  const [loading, setLoading] = useState(false)
  const { user } = useAuth()

  useEffect(() => {
    if (user) {
      refreshCart()
    } else {
      setItems([])
    }
  }, [user])

  const refreshCart = async () => {
    if (!user) return

    try {
      setLoading(true)
      const cartData = await cartService.getCart()
      setItems(cartData.items || [])
    } catch (error) {
      console.error("Failed to fetch cart:", error)
    } finally {
      setLoading(false)
    }
  }

  const addItem = async (serviceId: number, quantity: number, customerInfo: any) => {
    try {
      setLoading(true)
      await cartService.addItem(serviceId, quantity, customerInfo)
      await refreshCart()
      toast.success("Item added to cart")
    } catch (error: any) {
      toast.error(error.response?.data?.message || "Failed to add item to cart")
      throw error
    } finally {
      setLoading(false)
    }
  }

  const updateItem = async (itemId: number, quantity: number) => {
    try {
      setLoading(true)
      await cartService.updateItem(itemId, quantity)
      await refreshCart()
      toast.success("Cart updated")
    } catch (error: any) {
      toast.error(error.response?.data?.message || "Failed to update cart")
      throw error
    } finally {
      setLoading(false)
    }
  }

  const removeItem = async (itemId: number) => {
    try {
      setLoading(true)
      await cartService.removeItem(itemId)
      await refreshCart()
      toast.success("Item removed from cart")
    } catch (error: any) {
      toast.error(error.response?.data?.message || "Failed to remove item")
      throw error
    } finally {
      setLoading(false)
    }
  }

  const clearCart = async () => {
    try {
      setLoading(true)
      await cartService.clearCart()
      setItems([])
      toast.success("Cart cleared")
    } catch (error: any) {
      toast.error(error.response?.data?.message || "Failed to clear cart")
      throw error
    } finally {
      setLoading(false)
    }
  }

  const totalItems = items.reduce((sum, item) => sum + item.quantity, 0)
  const totalAmount = items.reduce((sum, item) => sum + item.price * item.quantity, 0)

  const value = {
    items,
    loading,
    totalItems,
    totalAmount,
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
