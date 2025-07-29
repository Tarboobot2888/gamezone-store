import axios from "axios"
import Echo from "laravel-echo"
import Pusher from "pusher-js"

window.axios = axios

// Set up CSRF token for all requests
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest"

// Get CSRF token from meta tag
const token = document.head.querySelector('meta[name="csrf-token"]')
if (token) {
  window.axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content
} else {
  console.error("CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token")
}

// Set up Sanctum token if user is authenticated
const sanctumToken = localStorage.getItem("sanctum_token")
if (sanctumToken) {
  window.axios.defaults.headers.common["Authorization"] = `Bearer ${sanctumToken}`
}

// Laravel Echo setup for real-time features
if (typeof window.PUSHER_APP_KEY !== "undefined") {
  window.Pusher = Pusher

  window.Echo = new Echo({
    broadcaster: "pusher",
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    auth: {
      headers: {
        Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
      },
    },
  })
}

// Add request interceptor for authentication
axios.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("auth_token")
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  },
)

// Global error handling for axios
window.axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response) {
      switch (error.response.status) {
        case 401:
          // Unauthorized - redirect to login
          localStorage.removeItem("sanctum_token")
          if (window.location.pathname !== "/login") {
            window.location.href = "/login"
          }
          break
        case 403:
          // Forbidden
          if (window.showToast) {
            window.showToast("Access denied", "danger")
          }
          break
        case 404:
          // Not found
          if (window.showToast) {
            window.showToast("Resource not found", "danger")
          }
          break
        case 422:
          // Validation errors
          if (error.response.data.errors) {
            Object.values(error.response.data.errors).forEach((errorArray) => {
              errorArray.forEach((errorMessage) => {
                if (window.showToast) {
                  window.showToast(errorMessage, "danger")
                }
              })
            })
          }
          break
        case 429:
          // Too many requests
          if (window.showToast) {
            window.showToast("Too many requests. Please try again later.", "warning")
          }
          break
        case 500:
          // Server error
          if (window.showToast) {
            window.showToast("Server error. Please try again later.", "danger")
          }
          break
        default:
          if (window.showToast) {
            window.showToast("An error occurred. Please try again.", "danger")
          }
      }
    } else if (error.request) {
      // Network error
      if (window.showToast) {
        window.showToast("Network error. Please check your connection.", "danger")
      }
    }

    return Promise.reject(error)
  },
)

// Global error handler
window.addEventListener("error", (event) => {
  console.error("Global error:", event.error)

  // Don't show toast for script loading errors
  if (event.error && !event.filename) {
    return
  }

  if (window.showToast) {
    window.showToast("An unexpected error occurred", "error")
  }
})

// Handle unhandled promise rejections
window.addEventListener("unhandledrejection", (event) => {
  console.error("Unhandled promise rejection:", event.reason)

  if (window.showToast) {
    window.showToast("An error occurred while processing your request", "error")
  }
})

// Set up real-time notifications for authenticated users
document.addEventListener("DOMContentLoaded", () => {
  if (window.App && window.App.user) {
    // Listen for order status changes
    window.Echo.private(`App.Models.User.${window.App.user.id}`).notification((notification) => {
      if (window.showToast) {
        window.showToast(notification.message, "info")
      }

      // Update notification count
      if (window.updateNotificationCount) {
        window.updateNotificationCount()
      }

      // Play notification sound
      if (window.playNotificationSound) {
        window.playNotificationSound()
      }
    })

    // Listen for support ticket updates
    window.Echo.private(`support-tickets.${window.App.user.id}`).listen("TicketReplied", (e) => {
      if (window.showToast) {
        window.showToast(`New reply on ticket #${e.ticket.ticket_number}`, "info")
      }
    })

    // Listen for order updates
    window.Echo.private(`orders.${window.App.user.id}`).listen("OrderStatusChanged", (e) => {
      if (window.showToast) {
        window.showToast(`Order #${e.order.order_number} status: ${e.order.status}`, "info")
      }
    })
  }
})

// Utility functions for real-time features
window.updateNotificationCount = () => {
  fetch("/api/v1/user/notifications/unread-count", {
    headers: {
      Authorization: `Bearer ${localStorage.getItem("sanctum_token")}`,
      Accept: "application/json",
    },
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const badges = document.querySelectorAll(".notification-count")
        badges.forEach((badge) => {
          if (data.count > 0) {
            badge.textContent = data.count
            badge.style.display = "inline"
          } else {
            badge.style.display = "none"
          }
        })
      }
    })
    .catch((error) => console.error("Error updating notification count:", error))
}

window.playNotificationSound = () => {
  // Create and play notification sound
  const audio = new Audio("/sounds/notification.mp3")
  audio.volume = 0.3
  audio.play().catch((error) => {
    // Ignore autoplay policy errors
    console.log("Could not play notification sound:", error)
  })
}

// Service Worker registration for PWA features
if ("serviceWorker" in navigator) {
  window.addEventListener("load", () => {
    navigator.serviceWorker
      .register("/sw.js")
      .then((registration) => {
        console.log("ServiceWorker registration successful")
      })
      .catch((error) => {
        console.log("ServiceWorker registration failed: ", error)
      })
  })
}

// Firebase messaging for push notifications
import { initializeApp } from "firebase/app"
import { getMessaging, getToken, onMessage } from "firebase/messaging"

// Firebase configuration
const firebaseConfig = {
  apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
  authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
  projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
  storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
  messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
  appId: import.meta.env.VITE_FIREBASE_APP_ID,
}

// Initialize Firebase
const app = initializeApp(firebaseConfig)
const messaging = getMessaging(app)

// Request permission and get FCM token
if (window.App && window.App.user) {
  getToken(messaging, { vapidKey: import.meta.env.VITE_FIREBASE_VAPID_KEY })
    .then((currentToken) => {
      if (currentToken) {
        // Send token to server
        fetch("/api/v1/user/fcm-token", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${localStorage.getItem("sanctum_token")}`,
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
          },
          body: JSON.stringify({ token: currentToken }),
        }).catch((error) => console.error("Error sending FCM token:", error))
      }
    })
    .catch((err) => {
      console.log("An error occurred while retrieving token. ", err)
    })

  // Handle foreground messages
  onMessage(messaging, (payload) => {
    if (window.showToast) {
      window.showToast(payload.notification.body, "info")
    }

    if (window.playNotificationSound) {
      window.playNotificationSound()
    }
  })
}
