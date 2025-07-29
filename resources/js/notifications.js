// Real-time notifications using Laravel Echo and Pusher
import Echo from "laravel-echo"
import Pusher from "pusher-js"
import bootstrap from "bootstrap" // Import bootstrap

window.Pusher = Pusher

window.Echo = new Echo({
  broadcaster: "pusher",
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
  forceTLS: true,
  auth: {
    headers: {
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
    },
  },
})

// Notification manager
class NotificationManager {
  constructor() {
    this.notifications = []
    this.unreadCount = 0
    this.init()
  }

  init() {
    this.setupUserChannel()
    this.setupAdminChannel()
    this.createNotificationContainer()
    this.loadUnreadCount()
  }

  setupUserChannel() {
    if (window.authUserId) {
      window.Echo.private(`user.${window.authUserId}`)
        .listen("OrderStatusChanged", (e) => {
          this.addNotification({
            type: "order",
            title: "Order Update",
            message: e.message,
            data: e.order,
            timestamp: new Date(),
          })
        })
        .listen("SupportTicketReply", (e) => {
          this.addNotification({
            type: "support",
            title: "Support Ticket Reply",
            message: e.message,
            data: e.ticket,
            timestamp: new Date(),
          })
        })
    }
  }

  setupAdminChannel() {
    if (window.isAdmin) {
      window.Echo.channel("admin-dashboard")
        .listen("NewOrder", (e) => {
          this.addNotification({
            type: "admin-order",
            title: "New Order",
            message: `New order #${e.order.order_number} received`,
            data: e.order,
            timestamp: new Date(),
          })
        })
        .listen("NewSupportTicket", (e) => {
          this.addNotification({
            type: "admin-support",
            title: "New Support Ticket",
            message: `New ticket #${e.ticket.ticket_number} created`,
            data: e.ticket,
            timestamp: new Date(),
          })
        })
    }
  }

  addNotification(notification) {
    this.notifications.unshift(notification)
    this.unreadCount++
    this.updateNotificationBadge()
    this.showToastNotification(notification)
    this.updateNotificationDropdown()
    this.saveToLocalStorage()
  }

  showToastNotification(notification) {
    const toast = document.createElement("div")
    toast.className = "toast align-items-center text-bg-primary border-0"
    toast.setAttribute("role", "alert")
    toast.setAttribute("aria-live", "assertive")
    toast.setAttribute("aria-atomic", "true")

    toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${notification.title}</strong><br>
                    ${notification.message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `

    const container = document.getElementById("toast-container")
    if (container) {
      container.appendChild(toast)
      const bsToast = new bootstrap.Toast(toast, { delay: 5000 })
      bsToast.show()

      toast.addEventListener("hidden.bs.toast", () => {
        toast.remove()
      })
    }
  }

  updateNotificationBadge() {
    const badges = document.querySelectorAll(".notification-badge")
    badges.forEach((badge) => {
      if (this.unreadCount > 0) {
        badge.textContent = this.unreadCount > 99 ? "99+" : this.unreadCount
        badge.style.display = "inline-block"
      } else {
        badge.style.display = "none"
      }
    })
  }

  updateNotificationDropdown() {
    const dropdown = document.getElementById("notificationDropdown")
    if (!dropdown) return

    const list = dropdown.querySelector(".notification-list")
    if (!list) return

    list.innerHTML = ""

    if (this.notifications.length === 0) {
      list.innerHTML = `
                <div class="dropdown-item text-center text-muted py-3">
                    <i class="fas fa-bell-slash fa-2x mb-2"></i><br>
                    No notifications
                </div>
            `
      return
    }

    this.notifications.slice(0, 10).forEach((notification) => {
      const item = document.createElement("div")
      item.className = "dropdown-item notification-item"
      item.innerHTML = `
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-${this.getNotificationIcon(notification.type)} text-primary"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="fw-bold">${notification.title}</div>
                        <div class="text-muted small">${notification.message}</div>
                        <div class="text-muted small">${this.formatTimestamp(notification.timestamp)}</div>
                    </div>
                </div>
            `

      item.addEventListener("click", () => {
        this.handleNotificationClick(notification)
      })

      list.appendChild(item)
    })

    // Add "View All" link
    if (this.notifications.length > 10) {
      const viewAll = document.createElement("div")
      viewAll.className = "dropdown-item text-center border-top"
      viewAll.innerHTML = `<a href="/dashboard/notifications" class="text-decoration-none">View All Notifications</a>`
      list.appendChild(viewAll)
    }
  }

  handleNotificationClick(notification) {
    // Mark as read and navigate to relevant page
    this.markAsRead(notification)

    switch (notification.type) {
      case "order":
        window.location.href = `/dashboard/orders/${notification.data.id}`
        break
      case "support":
        window.location.href = `/dashboard/support-tickets/${notification.data.id}`
        break
      case "admin-order":
        window.location.href = `/admin/orders/${notification.data.id}`
        break
      case "admin-support":
        window.location.href = `/admin/support-tickets/${notification.data.id}`
        break
    }
  }

  markAsRead(notification) {
    notification.read = true
    this.unreadCount = Math.max(0, this.unreadCount - 1)
    this.updateNotificationBadge()
    this.saveToLocalStorage()
  }

  markAllAsRead() {
    this.notifications.forEach((notification) => {
      notification.read = true
    })
    this.unreadCount = 0
    this.updateNotificationBadge()
    this.saveToLocalStorage()
  }

  getNotificationIcon(type) {
    const icons = {
      order: "shopping-bag",
      support: "headset",
      "admin-order": "shopping-cart",
      "admin-support": "ticket-alt",
    }
    return icons[type] || "bell"
  }

  formatTimestamp(timestamp) {
    const now = new Date()
    const diff = now - new Date(timestamp)
    const minutes = Math.floor(diff / 60000)
    const hours = Math.floor(diff / 3600000)
    const days = Math.floor(diff / 86400000)

    if (minutes < 1) return "Just now"
    if (minutes < 60) return `${minutes}m ago`
    if (hours < 24) return `${hours}h ago`
    if (days < 7) return `${days}d ago`
    return new Date(timestamp).toLocaleDateString()
  }

  createNotificationContainer() {
    if (!document.getElementById("toast-container")) {
      const container = document.createElement("div")
      container.id = "toast-container"
      container.className = "toast-container position-fixed bottom-0 end-0 p-3"
      container.style.zIndex = "9999"
      document.body.appendChild(container)
    }
  }

  saveToLocalStorage() {
    try {
      localStorage.setItem("notifications", JSON.stringify(this.notifications.slice(0, 50)))
      localStorage.setItem("unreadCount", this.unreadCount.toString())
    } catch (error) {
      console.error("Error saving notifications to localStorage:", error)
    }
  }

  loadFromLocalStorage() {
    try {
      const saved = localStorage.getItem("notifications")
      if (saved) {
        this.notifications = JSON.parse(saved)
      }

      const unreadCount = localStorage.getItem("unreadCount")
      if (unreadCount) {
        this.unreadCount = Number.parseInt(unreadCount, 10) || 0
      }
    } catch (error) {
      console.error("Error loading notifications from localStorage:", error)
    }
  }

  async loadUnreadCount() {
    try {
      const response = await fetch("/api/notifications/unread-count")
      const data = await response.json()
      this.unreadCount = data.count || 0
      this.updateNotificationBadge()
    } catch (error) {
      console.error("Error loading unread count:", error)
    }
  }
}

// Initialize notification manager when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  window.notificationManager = new NotificationManager()
})

// Export for use in other modules
export default NotificationManager
