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
  type User,
} from "firebase/auth"
import { getStorage, ref, uploadBytes, getDownloadURL, deleteObject } from "firebase/storage"

// Firebase configuration
const firebaseConfig = {
  apiKey: process.env.NEXT_PUBLIC_FIREBASE_API_KEY,
  authDomain: process.env.NEXT_PUBLIC_FIREBASE_AUTH_DOMAIN,
  projectId: process.env.NEXT_PUBLIC_FIREBASE_PROJECT_ID,
  storageBucket: process.env.NEXT_PUBLIC_FIREBASE_STORAGE_BUCKET,
  messagingSenderId: process.env.NEXT_PUBLIC_FIREBASE_MESSAGING_SENDER_ID,
  appId: process.env.NEXT_PUBLIC_FIREBASE_APP_ID,
}

// Initialize Firebase
const app = initializeApp(firebaseConfig)
const auth = getAuth(app)
const storage = getStorage(app)

// Providers
const googleProvider = new GoogleAuthProvider()
const githubProvider = new GithubAuthProvider()

// Configure providers
googleProvider.setCustomParameters({
  prompt: "select_account",
})

githubProvider.setCustomParameters({
  allow_signup: "true",
})

// Firebase Auth Manager
class FirebaseAuthManager {
  constructor() {
    this.auth = auth
  }

  onAuthStateChanged(callback: (user: User | null) => void) {
    return onAuthStateChanged(this.auth, callback)
  }

  async signInWithGoogle(): Promise<User> {
    try {
      const result = await signInWithPopup(this.auth, googleProvider)
      return result.user
    } catch (error: any) {
      console.error("Google sign-in error:", error)
      throw new Error(this.getErrorMessage(error))
    }
  }

  async signInWithGitHub(): Promise<User> {
    try {
      const result = await signInWithPopup(this.auth, githubProvider)
      return result.user
    } catch (error: any) {
      console.error("GitHub sign-in error:", error)
      throw new Error(this.getErrorMessage(error))
    }
  }

  async signInWithEmail(email: string, password: string): Promise<User> {
    try {
      const result = await signInWithEmailAndPassword(this.auth, email, password)
      return result.user
    } catch (error: any) {
      console.error("Email sign-in error:", error)
      throw new Error(this.getErrorMessage(error))
    }
  }

  async signUpWithEmail(email: string, password: string, displayName?: string): Promise<User> {
    try {
      const result = await createUserWithEmailAndPassword(this.auth, email, password)

      // Update profile with display name
      if (displayName) {
        await updateProfile(result.user, { displayName })
      }

      // Send email verification
      await sendEmailVerification(result.user)

      return result.user
    } catch (error: any) {
      console.error("Email sign-up error:", error)
      throw new Error(this.getErrorMessage(error))
    }
  }

  async signOut(): Promise<void> {
    try {
      await signOut(this.auth)
    } catch (error: any) {
      console.error("Sign-out error:", error)
      throw new Error("Error signing out")
    }
  }

  getCurrentUser(): User | null {
    return this.auth.currentUser
  }

  async getIdToken(): Promise<string | null> {
    const user = this.getCurrentUser()
    if (user) {
      return await user.getIdToken()
    }
    return null
  }

  private getErrorMessage(error: any): string {
    const errorMessages: Record<string, string> = {
      "auth/user-not-found": "No account found with this email address.",
      "auth/wrong-password": "Incorrect password.",
      "auth/email-already-in-use": "An account with this email already exists.",
      "auth/weak-password": "Password should be at least 6 characters.",
      "auth/invalid-email": "Invalid email address.",
      "auth/user-disabled": "This account has been disabled.",
      "auth/too-many-requests": "Too many failed attempts. Please try again later.",
      "auth/popup-closed-by-user": "Sign-in popup was closed.",
      "auth/cancelled-popup-request": "Sign-in was cancelled.",
      "auth/network-request-failed": "Network error. Please check your connection.",
    }

    return errorMessages[error.code] || error.message || "An error occurred during authentication."
  }
}

// Firebase Storage Manager
class FirebaseStorageManager {
  constructor() {
    this.storage = storage
  }

  async uploadFile(file: File, path: string): Promise<string> {
    try {
      const storageRef = ref(this.storage, path)
      const snapshot = await uploadBytes(storageRef, file)
      const downloadURL = await getDownloadURL(snapshot.ref)
      return downloadURL
    } catch (error) {
      console.error("Upload error:", error)
      throw new Error("Failed to upload file")
    }
  }

  async uploadAvatar(file: File, userId: string): Promise<string> {
    const path = `avatars/${userId}/${Date.now()}_${file.name}`
    return await this.uploadFile(file, path)
  }

  async uploadAccountImage(file: File, accountId: string): Promise<string> {
    const path = `accounts/${accountId}/${Date.now()}_${file.name}`
    return await this.uploadFile(file, path)
  }

  async uploadGameImage(file: File, gameId: string): Promise<string> {
    const path = `games/${gameId}/${Date.now()}_${file.name}`
    return await this.uploadFile(file, path)
  }

  async deleteFile(url: string): Promise<void> {
    try {
      const fileRef = ref(this.storage, url)
      await deleteObject(fileRef)
    } catch (error) {
      console.error("Delete error:", error)
      throw new Error("Failed to delete file")
    }
  }
}

// Initialize managers
export const FirebaseAuth = new FirebaseAuthManager()
export const FirebaseStorage = new FirebaseStorageManager()

// Export for use in other modules
export { auth, storage }
