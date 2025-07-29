// Admin-specific JavaScript functionality

document.addEventListener("DOMContentLoaded", () => {
  // Initialize admin dashboard features
  initializeAdminDashboard()
  initializeDataTables()
  initializeCharts()
  initializeAdminForms()
})

function initializeAdminDashboard() {
  // Dashboard statistics refresh
  const refreshButton = document.getElementById("refresh-stats")
  if (refreshButton) {
    refreshButton.addEventListener("click", () => {
      refreshDashboardStats()
    })
  }

  // Auto-refresh stats every 5 minutes
  setInterval(refreshDashboardStats, 300000)
}

function refreshDashboardStats() {
  fetch("/admin/api/stats", {
    headers: {
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
      Accept: "application/json",
    },
  })
    .then((response) => response.json())
    .then((data) => {
      // Update dashboard statistics
      updateStatCard("total-users", data.totalUsers)
      updateStatCard("total-orders", data.totalOrders)
      updateStatCard("total-revenue", data.totalRevenue)
      updateStatCard("pending-tickets", data.pendingTickets)
    })
    .catch((error) => {
      console.error("Error refreshing stats:", error)
    })
}

function updateStatCard(cardId, value) {
  const card = document.getElementById(cardId)
  if (card) {
    const valueElement = card.querySelector(".stat-value")
    if (valueElement) {
      valueElement.textContent = value
    }
  }
}

function initializeDataTables() {
  // Initialize DataTables for admin tables
  const tables = document.querySelectorAll(".admin-table")
  tables.forEach((table) => {
    if (window.DataTable !== "undefined") {
      new window.DataTable(table, {
        responsive: true,
        pageLength: 25,
        order: [[0, "desc"]],
        language: {
          search: "Search:",
          lengthMenu: "Show _MENU_ entries",
          info: "Showing _START_ to _END_ of _TOTAL_ entries",
          paginate: {
            first: "First",
            last: "Last",
            next: "Next",
            previous: "Previous",
          },
        },
      })
    }
  })
}

function initializeCharts() {
  // Initialize Chart.js charts
  const chartElements = document.querySelectorAll(".admin-chart")
  chartElements.forEach((element) => {
    const chartType = element.dataset.chartType
    const chartData = JSON.parse(element.dataset.chartData)

    if (window.Chart !== "undefined") {
      new window.Chart(element, {
        type: chartType,
        data: chartData,
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: "top",
            },
            title: {
              display: true,
              text: element.dataset.chartTitle,
            },
          },
        },
      })
    }
  })
}

function initializeAdminForms() {
  // Handle bulk actions
  const bulkActionForms = document.querySelectorAll(".bulk-action-form")
  bulkActionForms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      const selectedItems = this.querySelectorAll('input[name="selected[]"]:checked')
      if (selectedItems.length === 0) {
        e.preventDefault()
        showToast("Please select at least one item", "warning")
        return
      }

      const action = this.querySelector('select[name="action"]').value
      if (!action) {
        e.preventDefault()
        showToast("Please select an action", "warning")
        return
      }

      if (!confirm(`Are you sure you want to ${action} ${selectedItems.length} item(s)?`)) {
        e.preventDefault()
      }
    })
  })

  // Handle delete confirmations
  const deleteButtons = document.querySelectorAll(".btn-delete")
  deleteButtons.forEach((button) => {
    button.addEventListener("click", (e) => {
      if (!confirm("Are you sure you want to delete this item? This action cannot be undone.")) {
        e.preventDefault()
      }
    })
  })

  // Handle status changes
  const statusSelects = document.querySelectorAll(".status-select")
  statusSelects.forEach((select) => {
    select.addEventListener("change", function () {
      const itemId = this.dataset.itemId
      const itemType = this.dataset.itemType
      const newStatus = this.value

      updateItemStatus(itemType, itemId, newStatus)
    })
  })
}

function updateItemStatus(itemType, itemId, status) {
  fetch(`/admin/api/${itemType}/${itemId}/status`, {
    method: "PATCH",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
      Accept: "application/json",
    },
    body: JSON.stringify({ status: status }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showToast(data.message || "Status updated successfully", "success")
      } else {
        showToast(data.message || "Error updating status", "danger")
      }
    })
    .catch((error) => {
      console.error("Error updating status:", error)
      showToast("Error updating status", "danger")
    })
}

// Export functions for global use
window.refreshDashboardStats = refreshDashboardStats
window.updateItemStatus = updateItemStatus

// Declare showToast function
function showToast(message, type) {
  // Implementation of showToast function
  console.log(`Toast: ${message} (Type: ${type})`)
}
