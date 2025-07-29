export interface User {
  id: string
  name: string
  email: string
  email_verified_at: string | null
  avatar?: string
  firebase_uid?: string
  roles: Role[]
  created_at: string
  updated_at: string
}

export interface Role {
  id: string
  name: string
  guard_name: string
}

export interface LoginResponse {
  user: User
  token: string
  token_type: string
}

export interface RegisterResponse {
  user: User
  token: string
  token_type: string
}

export interface AuthContextType {
  user: User | null
  loading: boolean
  login: (email: string, password: string) => Promise<void>
  register: (name: string, email: string, password: string) => Promise<void>
  loginWithFirebase: (token: string) => Promise<void>
  logout: () => Promise<void>
  refreshToken: () => Promise<void>
}
