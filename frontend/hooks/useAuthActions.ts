import { useAuth } from '@/lib/auth-context'
import { useRouter } from 'next/navigation'
import { useCallback } from 'react'

interface LoginCredentials {
  email: string
  password: string
  remember: boolean
}

interface LoginResponse {
  token: string
  user: {
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
}

export function useAuthActions() {
  const { login: contextLogin, logout: contextLogout, refreshUser } = useAuth()
  const router = useRouter()

  const login = useCallback(async (credentials: LoginCredentials): Promise<void> => {
    const response = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/auth/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      credentials: 'include',
      body: JSON.stringify(credentials),
    })

    const data = await response.json()

    if (!response.ok) {
      // Return structured error for caller to handle
      throw {
        status: response.status,
        message: data.message || 'Login failed',
        errors: data.errors || {},
      }
    }    // Extract token and user from response
    const { token, user } = data as LoginResponse
    
    // Save to context with remember preference
    contextLogin(token, user, credentials.remember)
    
    return Promise.resolve()
  }, [contextLogin])

  const logout = useCallback(async (): Promise<void> => {
    await contextLogout()
    router.push('/auth/login')
  }, [contextLogout, router])

  const redirectToLogin = useCallback(() => {
    router.push('/auth/login')
  }, [router])

  const redirectToDashboard = useCallback(() => {
    router.push('/dashboard')
  }, [router])

  return {
    login,
    logout,
    refreshUser,
    redirectToLogin,
    redirectToDashboard,
  }
}
