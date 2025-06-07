import Cookies from 'js-cookie'

// Cookie names constants
const COOKIE_NAMES = {
  AUTH_TOKEN: 'auth_token',
  REMEMBER_TOKEN: 'remember_token',
  USER_PREFERENCES: 'user_preferences',
} as const

// Cookie options for different types
const COOKIE_OPTIONS = {
  // Regular session cookies (expires when browser closes)
  session: {
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'lax' as const,
    path: '/',
  },
  // Remember me cookies (long-term)
  remember: {
    expires: 30, // 30 days
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'lax' as const,
    path: '/',
  },
  // Preferences cookies (medium-term)
  preferences: {
    expires: 365, // 1 year
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'lax' as const,
    path: '/',
  },
} as const

export class CookieManager {
  // Auth token management
  static setAuthToken(token: string, remember: boolean = false) {
    const options = remember ? COOKIE_OPTIONS.remember : COOKIE_OPTIONS.session
    Cookies.set(COOKIE_NAMES.AUTH_TOKEN, token, options)
  }

  static getAuthToken(): string | undefined {
    return Cookies.get(COOKIE_NAMES.AUTH_TOKEN)
  }

  static removeAuthToken() {
    Cookies.remove(COOKIE_NAMES.AUTH_TOKEN, { path: '/' })
  }

  // Remember token management (for "remember me" functionality)
  static setRememberToken(token: string) {
    Cookies.set(COOKIE_NAMES.REMEMBER_TOKEN, token, COOKIE_OPTIONS.remember)
  }

  static getRememberToken(): string | undefined {
    return Cookies.get(COOKIE_NAMES.REMEMBER_TOKEN)
  }

  static removeRememberToken() {
    Cookies.remove(COOKIE_NAMES.REMEMBER_TOKEN, { path: '/' })
  }

  // User preferences management
  static setUserPreferences(preferences: Record<string, any>) {
    try {
      const preferencesJson = JSON.stringify(preferences)
      Cookies.set(COOKIE_NAMES.USER_PREFERENCES, preferencesJson, COOKIE_OPTIONS.preferences)
    } catch (error) {
      console.error('Error setting user preferences cookie:', error)
    }
  }

  static getUserPreferences(): Record<string, any> | null {
    try {
      const preferencesJson = Cookies.get(COOKIE_NAMES.USER_PREFERENCES)
      return preferencesJson ? JSON.parse(preferencesJson) : null
    } catch (error) {
      console.error('Error parsing user preferences cookie:', error)
      return null
    }
  }

  static removeUserPreferences() {
    Cookies.remove(COOKIE_NAMES.USER_PREFERENCES, { path: '/' })
  }

  // Clear all auth-related cookies
  static clearAllAuthCookies() {
    this.removeAuthToken()
    this.removeRememberToken()
    // Keep user preferences as they're not auth-specific
  }

  // Check if cookies are available
  static areCookiesEnabled(): boolean {
    try {
      const testCookie = '__cookie_test__'
      Cookies.set(testCookie, 'test', { expires: 1 })
      const result = Cookies.get(testCookie) === 'test'
      Cookies.remove(testCookie)
      return result
    } catch {
      return false
    }
  }

  // Get all cookies (for debugging)
  static getAllCookies() {
    return Cookies.get()
  }
}

export default CookieManager
