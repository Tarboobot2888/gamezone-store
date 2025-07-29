"use client"

import type React from "react"
import { createContext, useContext, useEffect, useState } from "react"
import { useAuth } from "./auth-provider"
import { pusherService } from "@/lib/pusher"
import { toast } from "sonner"

interface Notification {
  id: string
  type: "order" | "ticket" | "system"
  title: string
  message: string
  data?: any
  read: boolean
  createdAt: string
}

interface NotificationContextType {
  notifications: Notification[]
  unreadCount: number
  markAsRead: (id: string) => void
  markAllAsRead: () => void
  clearNotifications: () => void
}

const NotificationContext = createContext<NotificationContextType | undefined>(undefined)

export function NotificationProvider({ children }: { children: React.ReactNode }) {
  const [notifications, setNotifications] = useState<Notification[]>([])
  const { user } = useAuth()

  useEffect(() => {
    if (!user) {
      setNotifications([])
      return
    }

    // Subscribe to user-specific channel
    const channel = pusherService.subscribe(`private-user.${user.id}`)

    // Listen for order status changes
    channel.bind("order.status.changed", (data: any) => {
      const notification: Notification = {
        id: `order-${data.order_id}-${Date.now()}`,
        type: "order",
        title: "Order Status Updated",
        message: data.message,
        data: data,
        read: false,
        createdAt: data.timestamp,
      }

      setNotifications((prev) => [notification, ...prev])
      toast.success(data.message, {
        description: `Order #${data.order_number}`,
      })
    })

    // Listen for ticket replies
    channel.bind("ticket.replied", (data: any) => {
      if (data.is_admin_reply) {
        const notification: Notification = {
          id: `ticket-${data.ticket_id}-${Date.now()}`,
          type: "ticket",
          title: "Support Ticket Reply",
          message: `New reply on ticket #${data.ticket_number}`,
          data: data,
          read: false,
          createdAt: data.timestamp,
        }

        setNotifications((prev) => [notification, ...prev])
        toast.info("New support ticket reply", {
          description: `Ticket #${data.ticket_number}`,
        })
      }
    })

    // Listen for system notifications
    channel.bind("system.notification", (data: any) => {
      const notification: Notification = {
        id: `system-${Date.now()}`,
        type: "system",
        title: data.title,
        message: data.message,
        data: data,
        read: false,
        createdAt: data.timestamp,
      }

      setNotifications((prev) => [notification, ...prev])
      toast.info(data.title, {
        description: data.message,
      })
    })

    return () => {
      pusherService.unsubscribe(`private-user.${user.id}`)
    }
  }, [user])

  const markAsRead = (id: string) => {
    setNotifications((prev) =>
      prev.map((notification) => (notification.id === id ? { ...notification, read: true } : notification)),
    )
  }

  const markAllAsRead = () => {
    setNotifications((prev) => prev.map((notification) => ({ ...notification, read: true })))
  }

  const clearNotifications = () => {
    setNotifications([])
  }

  const unreadCount = notifications.filter((n) => !n.read).length

  const value = {
    notifications,
    unreadCount,
    markAsRead,
    markAllAsRead,
    clearNotifications,
  }

  return <NotificationContext.Provider value={value}>{children}</NotificationContext.Provider>
}

export function useNotifications() {
  const context = useContext(NotificationContext)
  if (context === undefined) {
    throw new Error("useNotifications must be used within a NotificationProvider")
  }
  return context
}
