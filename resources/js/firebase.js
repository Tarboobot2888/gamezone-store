import { initializeApp } from "firebase/app"
import {
  getAuth,
  signInWithPopup,
  GoogleAuthProvider,
  GithubAuthProvider,
  signInWithEmailAndPassword,
  createUserWithEmailAndPassword,
  signOut,
  onAuthStateChanged,
  sendEmailVerification,
  updateProfile,
} from "firebase/auth"
import { getStorage, ref, uploadBytes, getDownloadURL, deleteObject } from "firebase/storage"

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
const auth = getAuth(app)
const storage = getStorage(app)

// Providers
const googleProvider = new GoogleAuthProvider()
const githubProvider = new GithubAuthProvider()

// Firebase Auth Manager
class FirebaseAuthManager {
  constructor() {
    this.auth = auth
    this.currentUser = null
    this.init()
  }

  init() {
    onAuthStateChanged(this.auth, (user) => {
      this.currentUser = user
      this.handleAuthStateChange(user)
    })
  }

  async handleAuthStateChange(user) {
    if (user) {
      // User is signed in, sync with Laravel backend
      try {
        const idToken = await user.getIdToken()
        await this.syncWithBackend(idToken)
      } catch (error) {
        console.error("Error syncing with backend:", error)
      }
    }
  }

  async syncWithBackend(idToken) {
    try {
      const response = await fetch("/api/auth/firebase", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ idToken }),
      })

      const result = await response.json()

      if (result.success) {
        // Redirect if needed
        if (result.redirect) {
          window.location.href = result.redirect
        }
      } else {
        throw new Error(result.message)
      }
    } catch (error) {
      console.error("Backend sync error:", error)
      if (window.showToast) {
        window.showToast("Authentication sync failed", "danger")
      }
    }
  }

  async signInWithGoogle() {
    try {
      const result = await signInWithPopup(this.auth, googleProvider)
      if (window.showToast) {
        window.showToast("Successfully signed in with Google!", "success")
      }
      return result.user
    } catch (error) {
      console.error("Google sign-in error:", error)
      if (window.showToast) {
        window.showToast(this.getErrorMessage(error), "danger")
      }
      throw error
    }
  }

  async signInWithGitHub() {
    try {
      const result = await signInWithPopup(this.auth, githubProvider)
      if (window.showToast) {
        window.showToast("Successfully signed in with GitHub!", "success")
      }
      return result.user
    } catch (error) {
      console.error("GitHub sign-in error:", error)
      if (window.showToast) {
        window.showToast(this.getErrorMessage(error), "danger")
      }
      throw error
    }
  }

  async signInWithEmail(email, password) {
    try {
      const result = await signInWithEmailAndPassword(this.auth, email, password)
      if (window.showToast) {
        window.showToast("Successfully signed in!", "success")
      }
      return result.user
    } catch (error) {
      console.error("Email sign-in error:", error)
      if (window.showToast) {
        window.showToast(this.getErrorMessage(error), "danger")
      }
      throw error
    }
  }

  async signUpWithEmail(email, password, displayName) {
    try {
      const result = await createUserWithEmailAndPassword(this.auth, email, password)

      // Update profile with display name
      if (displayName) {
        await updateProfile(result.user, { displayName })
      }

      // Send email verification
      await sendEmailVerification(result.user)

      if (window.showToast) {
        window.showToast("Account created! Please check your email for verification.", "success")
      }
      return result.user
    } catch (error) {
      console.error("Email sign-up error:", error)
      if (window.showToast) {
        window.showToast(this.getErrorMessage(error), "danger")
      }
      throw error
    }
  }

  async signOut() {
    try {
      await signOut(this.auth)
      if (window.showToast) {
        window.showToast("Successfully signed out!", "success")
      }

      // Redirect to home or login page
      setTimeout(() => {
        window.location.href = "/"
      }, 1000)
    } catch (error) {
      console.error("Sign-out error:", error)
      if (window.showToast) {
        window.showToast("Error signing out", "danger")
      }
    }
  }

  getErrorMessage(error) {
    const errorMessages = {
      "auth/user-not-found": "No account found with this email address.",
      "auth/wrong-password": "Incorrect password.",
      "auth/email-already-in-use": "An account with this email already exists.",
      "auth/weak-password": "Password should be at least 6 characters.",
      "auth/invalid-email": "Invalid email address.",
      "auth/user-disabled": "This account has been disabled.",
      "auth/too-many-requests": "Too many failed attempts. Please try again later.",
      "auth/popup-closed-by-user": "Sign-in popup was closed.",
      "auth/cancelled-popup-request": "Sign-in was cancelled.",
    }

    return errorMessages[error.code] || error.message || "An error occurred during authentication."
  }
}

// Firebase Storage Manager
class FirebaseStorageManager {
  constructor() {
    this.storage = storage
  }

  async uploadFile(file, path) {
    try {
      const storageRef = ref(this.storage, path)
      const snapshot = await uploadBytes(storageRef, file)
      const downloadURL = await getDownloadURL(snapshot.ref)
      return downloadURL
    } catch (error) {
      console.error("Upload error:", error)
      throw error
    }
  }

  async uploadAvatar(file, userId) {
    const path = `avatars/${userId}/${Date.now()}_${file.name}`
    return await this.uploadFile(file, path)
  }

  async uploadAccountImage(file, accountId) {
    const path = `accounts/${accountId}/${Date.now()}_${file.name}`
    return await this.uploadFile(file, path)
  }

  async deleteFile(url) {
    try {
      const fileRef = ref(this.storage, url)
      await deleteObject(fileRef)
    } catch (error) {
      console.error("Delete error:", error)
      throw error
    }
  }
}

// Initialize managers
const authManager = new FirebaseAuthManager()
const storageManager = new FirebaseStorageManager()

// Make available globally
window.FirebaseAuth = authManager
window.FirebaseStorage = storageManager

// Export for use in other modules
export { authManager as FirebaseAuth, storageManager as FirebaseStorage }
