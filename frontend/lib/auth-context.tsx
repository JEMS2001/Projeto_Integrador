'use client'

import React, { createContext, useContext, useEffect, useState, ReactNode } from 'react'
import CookieManager from '@/lib/cookie-manager'

interface User {
  id: number
  nome: string
  email: string
  tipo: 'membro' | 'empresa'
  empresa_id?: number
  empresa?: {
    id: number
    nome: string
    email: string
  }
}

interface AuthContextType {
  user: User | null
  token: string | null
  isLoading: boolean
  isAuthenticated: boolean
  login: (token: string, userData: User, remember?: boolean) => void
  logout: () => Promise<void>
  refreshUser: () => Promise<void>
}

const AuthContext = createContext<AuthContextType | undefined>(undefined)

interface AuthProviderProps {
  children: ReactNode
}

export function AuthProvider({ children }: AuthProviderProps) {
  const [user, setUser] = useState<User | null>(null)
  const [token, setToken] = useState<string | null>(null)
  const [isLoading, setIsLoading] = useState(true)

  const isAuthenticated = !!token && !!user  // Initialize auth state from storage on mount
  useEffect(() => {
    const initializeAuth = () => {
      try {
        // Check cookies first (preferred for remember me)
        let storedToken = CookieManager.getAuthToken()
        let storedUser = localStorage.getItem('user')

        // Fallback to localStorage if cookies are not available
        if (!storedToken) {
          storedToken = localStorage.getItem('auth_token') ?? undefined
        }

        if (storedToken && storedUser) {
          setToken(storedToken)
          setUser(JSON.parse(storedUser))
        } else if (storedToken) {
          // We have token but no user data, try to fetch user
          setToken(storedToken)
          // refreshUser will be called after token is set
        }
      } catch (error) {
        console.error('Error initializing auth state:', error)
        // Clear invalid stored data
        CookieManager.clearAllAuthCookies()
        localStorage.removeItem('auth_token')
        localStorage.removeItem('user')
      } finally {
        setIsLoading(false)
      }
    }

    initializeAuth()
  }, [])

  // Refresh user data when token is available but user is not
  useEffect(() => {
    if (token && !user && !isLoading) {
      refreshUser()
    }
  }, [token, user, isLoading])

  const login = (authToken: string, userData: User, remember: boolean = false) => {
    try {
      setToken(authToken)
      setUser(userData)
      
      // Store in cookies with appropriate expiration
      CookieManager.setAuthToken(authToken, remember)
      
      // Also store in localStorage as fallback
      localStorage.setItem('auth_token', authToken)
      localStorage.setItem('user', JSON.stringify(userData))
    } catch (error) {
      console.error('Error saving auth data:', error)
    }
  }
  const logout = async () => {
    try {
      // Call logout API if token exists
      if (token) {
        await fetch(`${process.env.NEXT_PUBLIC_API_URL}/auth/logout`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
        }).catch(error => {
          // Log but don't throw - allow logout to continue even if API call fails
          console.warn('Logout API call failed:', error)
        })
      }
    } finally {
      // Always clear local state and storage
      setUser(null)
      setToken(null)
      
      // Clear all authentication data
      CookieManager.clearAllAuthCookies()
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
    }
  }

  const refreshUser = async () => {
    if (!token) return

    try {
      const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/auth/user`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      })

      if (response.ok) {
        const data = await response.json()
        if (data.data) {
          setUser(data.data)
          localStorage.setItem('user', JSON.stringify(data.data))
        }
      } else if (response.status === 401) {
        // Token is invalid, logout user
        await logout()
      }
    } catch (error) {
      console.error('Error refreshing user data:', error)
    }
  }

  const contextValue: AuthContextType = {
    user,
    token,
    isLoading,
    isAuthenticated,
    login,
    logout,
    refreshUser,
  }

  return (
    <AuthContext.Provider value={contextValue}>
      {children}
    </AuthContext.Provider>
  )
}

export function useAuth() {
  const context = useContext(AuthContext)
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider')
  }
  return context
}
