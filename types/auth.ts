export interface User {
  id: number
  name: string
  email: string
  avatar?: string
  phone?: string
  preferred_language: string
  theme_preference: string
  two_factor_enabled: boolean
  email_verified_at?: string
  roles?: Role[]
  created_at: string
  updated_at: string
}

export interface Role {
  id: number
  name: string
  display_name: string
}

export interface LoginResponse {
  user: User
  token: string
  expires_at: string
}

export interface RegisterResponse {
  user: User
  token: string
  expires_at: string
}

export interface AuthContextType {
  user: User | null
  loading: boolean
  login: (email: string, password: string) => Promise<void>
  register: (name: string, email: string, password: string) => Promise<void>
  logout: () => Promise<void>
  refreshUser: () => Promise<void>
}
