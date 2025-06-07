'use client'

import { useAuth } from '@/lib/auth-context'
import { useRouter } from 'next/navigation'
import { useEffect, ReactNode } from 'react'

interface ProtectedRouteProps {
  children: ReactNode
  requireAuth?: boolean
  redirectTo?: string
  loadingComponent?: ReactNode
}

export function ProtectedRoute({ 
  children, 
  requireAuth = true, 
  redirectTo = '/auth/login',
  loadingComponent
}: ProtectedRouteProps) {
  const { isAuthenticated, isLoading } = useAuth()
  const router = useRouter()

  useEffect(() => {
    if (!isLoading) {
      if (requireAuth && !isAuthenticated) {
        router.push(redirectTo)
      } else if (!requireAuth && isAuthenticated) {
        // Redirect authenticated users away from auth pages
        router.push('/dashboard')
      }
    }
  }, [isAuthenticated, isLoading, requireAuth, router, redirectTo])

  // Show loading state while auth is initializing
  if (isLoading) {
    return loadingComponent || (
      <div className="min-h-screen flex items-center justify-center">
        <div className="flex items-center space-x-2">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <span className="text-gray-600">Carregando...</span>
        </div>
      </div>
    )
  }

  // Render children only if auth requirements are met
  if (requireAuth && !isAuthenticated) {
    return null // Will redirect to login
  }

  if (!requireAuth && isAuthenticated) {
    return null // Will redirect to dashboard
  }

  return <>{children}</>
}
