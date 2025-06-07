interface ApiResponse<T = any> {
  status: 'success' | 'error'
  data?: T
  error?: string
  message?: string
  errors?: Record<string, string | string[]>
}

interface RequestConfig extends RequestInit {
  requireAuth?: boolean
}

class ApiClient {
  private baseURL: string

  constructor() {
    this.baseURL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api'
  }

  private getAuthToken(): string | null {
    if (typeof window === 'undefined') return null
    return localStorage.getItem('auth_token')
  }

  private async request<T>(
    endpoint: string, 
    options: RequestConfig = {}
  ): Promise<ApiResponse<T>> {
    const { requireAuth = true, ...requestOptions } = options
    
    const url = `${this.baseURL}${endpoint}`
    
    const config: RequestInit = {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...requestOptions.headers,
      },
      credentials: 'include',
      ...requestOptions,
    }

    // Add authorization header if required and token exists
    if (requireAuth) {
      const token = this.getAuthToken()
      if (token) {
        (config.headers as Record<string, string>)['Authorization'] = `Bearer ${token}`
      }
    }

    try {
      const response = await fetch(url, config)
      const data = await response.json()

      if (!response.ok) {
        // Handle 401 Unauthorized - token expired or invalid
        if (response.status === 401 && requireAuth) {
          // Clear invalid auth data
          localStorage.removeItem('auth_token')
          localStorage.removeItem('user')
          
          // Redirect to login if we're in browser
          if (typeof window !== 'undefined') {
            window.location.href = '/auth/login'
          }
        }

        return {
          status: 'error',
          error: data.error || 'request_failed',
          message: data.message || 'Something went wrong',
          errors: data.errors || {},
        }
      }

      return {
        status: 'success',
        data: data.data || data,
      }
    } catch (error) {
      console.error('API request failed:', error)
      
      return {
        status: 'error',
        error: 'network_error',
        message: 'Network error. Please check your connection.',
      }
    }
  }

  // HTTP Methods
  async get<T>(endpoint: string, options?: RequestConfig): Promise<ApiResponse<T>> {
    return this.request<T>(endpoint, { ...options, method: 'GET' })
  }

  async post<T>(
    endpoint: string, 
    data?: any, 
    options?: RequestConfig
  ): Promise<ApiResponse<T>> {
    return this.request<T>(endpoint, {
      ...options,
      method: 'POST',
      body: data ? JSON.stringify(data) : undefined,
    })
  }

  async put<T>(
    endpoint: string, 
    data?: any, 
    options?: RequestConfig
  ): Promise<ApiResponse<T>> {
    return this.request<T>(endpoint, {
      ...options,
      method: 'PUT',
      body: data ? JSON.stringify(data) : undefined,
    })
  }

  async patch<T>(
    endpoint: string, 
    data?: any, 
    options?: RequestConfig
  ): Promise<ApiResponse<T>> {
    return this.request<T>(endpoint, {
      ...options,
      method: 'PATCH',
      body: data ? JSON.stringify(data) : undefined,
    })
  }

  async delete<T>(endpoint: string, options?: RequestConfig): Promise<ApiResponse<T>> {
    return this.request<T>(endpoint, { ...options, method: 'DELETE' })
  }

  // Auth specific methods
  async login(credentials: { email: string; password: string; remember: boolean }) {
    return this.post('/auth/login', credentials, { requireAuth: false })
  }

  async logout() {
    return this.post('/auth/logout')
  }

  async getUser() {
    return this.get('/auth/user')
  }

  async logoutAll() {
    return this.post('/auth/logout-all')
  }
}

// Export singleton instance
export const apiClient = new ApiClient()
export type { ApiResponse }
