import "./bootstrap"
import bootstrap from "bootstrap"
import Alpine from "alpinejs"

// Make Alpine available globally
window.Alpine = Alpine

// Start Alpine
Alpine.start()

// Global functions
const showToast = (message, type = "info", duration = 5000) => {
  const toastContainer = document.getElementById("toast-container")
  if (!toastContainer) return

  const toastId = "toast-" + Date.now()
  const toast = document.createElement("div")
  toast.id = toastId
  toast.className = `toast align-items-center text-white bg-${type === "error" ? "danger" : type} border-0`
  toast.setAttribute("role", "alert")
  toast.setAttribute("aria-live", "assertive")
  toast.setAttribute("aria-atomic", "true")

  toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `

  toastContainer.appendChild(toast)

  const bsToast = new bootstrap.Toast(toast, {
    autohide: true,
    delay: duration,
  })

  bsToast.show()

  toast.addEventListener("hidden.bs.toast", () => {
    toast.remove()
  })
}

window.showToast = showToast

window.showLoading = () => {
  const overlay = document.getElementById("loading-overlay")
  if (overlay) {
    overlay.style.display = "flex"
  }
}

window.hideLoading = () => {
  const overlay = document.getElementById("loading-overlay")
  if (overlay) {
    overlay.style.display = "none"
  }
}

const toggleTheme = () => {
  const currentTheme = localStorage.getItem("theme") || "light"
  const newTheme = currentTheme === "light" ? "dark" : "light"

  localStorage.setItem("theme", newTheme)
  document.documentElement.setAttribute("data-theme", newTheme)

  // Update Alpine.js data
  if (window.Alpine && window.Alpine.store) {
    window.Alpine.store("theme", newTheme)
  }

  // Reload stylesheets
  const themeLink = document.querySelector('link[href*="dark.scss"], link[href*="light.scss"]')
  if (themeLink) {
    const newHref = themeLink.href.replace(currentTheme, newTheme)
    themeLink.href = newHref
  }
}

window.toggleTheme = toggleTheme

const updateCartCount = async () => {
  try {
    const response = await fetch("/api/v1/cart/count", {
      headers: {
        Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
        Accept: "application/json",
      },
    })

    if (response.ok) {
      const data = await response.json()
      const count = data.count || 0

      // Update all cart badges
      const badges = document.querySelectorAll(".cart-count")
      badges.forEach((badge) => {
        badge.textContent = count
        badge.style.display = count > 0 ? "inline" : "none"
      })
    }
  } catch (error) {
    console.error("Error updating cart count:", error)
  }
}

window.updateCartCount = updateCartCount

const addToCart = async (type, id, quantity = 1) => {
  if (!window.App.user) {
    window.showToast("Please login to add items to cart", "warning")
    window.location.href = window.App.routes.login
    return
  }

  window.showLoading()

  try {
    const response = await fetch("/api/v1/cart", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": window.App.csrfToken,
        Accept: "application/json",
      },
      body: JSON.stringify({
        type: type,
        item_id: id,
        quantity: quantity,
      }),
    })

    const data = await response.json()

    if (data.success) {
      window.showToast("Item added to cart successfully!", "success")
      window.updateCartCount()
    } else {
      window.showToast(data.message || "Failed to add item to cart", "error")
    }
  } catch (error) {
    console.error("Error adding to cart:", error)
    window.showToast("An error occurred. Please try again.", "error")
  } finally {
    window.hideLoading()
  }
}

window.addToCart = addToCart

// Initialize on DOM ready
document.addEventListener("DOMContentLoaded", () => {
  // Initialize tooltips
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  tooltipTriggerList.map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl))

  // Initialize popovers
  const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
  popoverTriggerList.map((popoverTriggerEl) => new bootstrap.Popover(popoverTriggerEl))

  // Set initial theme
  const savedTheme = localStorage.getItem("theme") || "light"
  document.documentElement.setAttribute("data-theme", savedTheme)

  // Auto-hide alerts after 5 seconds
  const alerts = document.querySelectorAll(".alert:not(.alert-permanent)")
  alerts.forEach((alert) => {
    setTimeout(() => {
      const bsAlert = new bootstrap.Alert(alert)
      bsAlert.close()
    }, 5000)
  })

  // Handle newsletter form submissions
  const newsletterForms = document.querySelectorAll(".newsletter-form")
  newsletterForms.forEach((form) => {
    form.addEventListener("submit", async (e) => {
      e.preventDefault()

      const formData = new FormData(form)
      const submitBtn = form.querySelector('button[type="submit"]')
      const originalText = submitBtn.innerHTML

      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'
      submitBtn.disabled = true

      try {
        const response = await fetch("/api/v1/newsletter/subscribe", {
          method: "POST",
          body: formData,
          headers: {
            "X-CSRF-TOKEN": window.App.csrfToken,
          },
        })

        const data = await response.json()

        if (data.success) {
          window.showToast("Successfully subscribed to newsletter!", "success")
          form.reset()
        } else {
          window.showToast(data.message || "Subscription failed", "error")
        }
      } catch (error) {
        window.showToast("An error occurred. Please try again.", "error")
      } finally {
        submitBtn.innerHTML = originalText
        submitBtn.disabled = false
      }
    })
  })

  // Handle contact form submissions
  const contactForms = document.querySelectorAll(".contact-form")
  contactForms.forEach((form) => {
    form.addEventListener("submit", async (e) => {
      e.preventDefault()

      const formData = new FormData(form)
      const submitBtn = form.querySelector('button[type="submit"]')
      const originalText = submitBtn.innerHTML

      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...'
      submitBtn.disabled = true

      try {
        const response = await fetch("/api/v1/contact", {
          method: "POST",
          body: formData,
          headers: {
            "X-CSRF-TOKEN": window.App.csrfToken,
          },
        })

        const data = await response.json()

        if (data.success) {
          window.showToast("Message sent successfully!", "success")
          form.reset()
        } else {
          window.showToast(data.message || "Failed to send message", "error")
        }
      } catch (error) {
        window.showToast("An error occurred. Please try again.", "error")
      } finally {
        submitBtn.innerHTML = originalText
        submitBtn.disabled = false
      }
    })
  })

  // Initialize search functionality
  const searchInputs = document.querySelectorAll(".search-input")
  searchInputs.forEach((input) => {
    let searchTimeout
    input.addEventListener("input", function () {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => {
        window.performSearch(this.value)
      }, 500)
    })
  })

  // Initialize infinite scroll for product listings
  const productContainers = document.querySelectorAll(".product-container[data-infinite-scroll]")
  productContainers.forEach((container) => {
    let loading = false
    let page = 1

    window.addEventListener("scroll", () => {
      if (loading) return

      const { scrollTop, scrollHeight, clientHeight } = document.documentElement

      if (scrollTop + clientHeight >= scrollHeight - 1000) {
        loading = true
        window.loadMoreProducts(container, ++page).finally(() => {
          loading = false
        })
      }
    })
  })
})

// Search functionality
const performSearch = async (query) => {
  if (query.length < 2) return

  try {
    const response = await fetch(`/api/v1/search?q=${encodeURIComponent(query)}`)
    const data = await response.json()

    if (data.success) {
      window.displaySearchResults(data.data)
    }
  } catch (error) {
    console.error("Search error:", error)
  }
}

window.performSearch = performSearch

const displaySearchResults = (results) => {
  const resultsContainer = document.getElementById("search-results")
  if (!resultsContainer) return

  resultsContainer.innerHTML = results
    .map(
      (item) => `
        <div class="search-result-item">
            <img src="${item.image_url}" alt="${item.name}" class="search-result-image">
            <div class="search-result-content">
                <h6>${item.name}</h6>
                <p class="text-muted small">${item.description}</p>
                <span class="text-primary">$${item.price}</span>
            </div>
        </div>
    `,
    )
    .join("")
}

window.displaySearchResults = displaySearchResults

// Load more products for infinite scroll
const loadMoreProducts = async (container, page) => {
  try {
    const url = new URL(window.location.href)
    url.searchParams.set("page", page)

    const response = await fetch(url.toString(), {
      headers: {
        Accept: "application/json",
      },
    })

    const data = await response.json()

    if (data.success && data.data.length > 0) {
      const newItems = data.data.map((item) => window.createProductCard(item)).join("")
      container.insertAdjacentHTML("beforeend", newItems)
    }
  } catch (error) {
    console.error("Error loading more products:", error)
  }
}

window.loadMoreProducts = loadMoreProducts

const createProductCard = (item) => `
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card product-card h-100 border-0 shadow">
                <img src="${item.image_url || "/images/default-product.jpg"}" class="card-img-top" alt="${item.name}">
                <div class="card-body">
                    <h5 class="card-title fw-bold">${item.name}</h5>
                    <p class="card-text text-muted small">${item.description || ""}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="h6 text-primary mb-0">$${item.price}</span>
                        <button class="btn btn-gaming btn-sm" onclick="window.addToCart('${item.type}', ${item.id})">
                            <i class="fas fa-cart-plus me-1"></i>
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `

window.createProductCard = createProductCard

// Real-time notifications setup
if (window.App.user && typeof window.Echo !== "undefined") {
  window.Echo.private(`App.Models.User.${window.App.user.id}`).notification((notification) => {
    window.showToast(notification.message, notification.type || "info")

    // Update notification count
    const notificationBadges = document.querySelectorAll(".notification-badge")
    notificationBadges.forEach((badge) => {
      const currentCount = Number.parseInt(badge.textContent) || 0
      badge.textContent = currentCount + 1
      badge.style.display = "inline"
    })
  })
}

// Export functions for global use
window.GameZone = {
  showToast,
  showLoading,
  hideLoading,
  toggleTheme,
  updateCartCount,
  addToCart,
  performSearch,
}
