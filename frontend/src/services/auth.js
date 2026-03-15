import { ref } from 'vue'
import api from './api'
import { profileService } from './profileService'
import { 
  getToken, 
  setToken, 
  clearToken, 
  getStudentSession, 
  setStudentSession, 
  clearStudentSession,
  getStoredUser,
  setStoredUser,
  clearStoredUser,
  getStoredRole,
  setStoredRole,
  clearStoredRole
} from './storage'

export { getToken, setToken, clearToken, getStudentSession, setStudentSession, clearStudentSession }

export const getUser = () => getStoredUser()
export const setUser = (user) => {
  if (!user) return
  setStoredUser(user)
  authUser.value = user
  
  const role = resolveUserRole(user)
  if (role) {
    setUserRole(role)
    userRole.value = role
  }
}
export const clearUser = () => {
  clearStoredUser()
  authUser.value = null
  userRole.value = ''
}

export const normalizeRole = (role) => {
  return typeof role === 'string' ? role.trim().toLowerCase() : ''
}

export const resolveUserRole = (user) => {
  if (!user) return ''

  const roleValue =
    typeof user.role === 'string'
      ? user.role
      : typeof user.role === 'object' && user.role
        ? user.role.name
        : ''

  const directRole = normalizeRole(roleValue)
  if (directRole) return directRole

  if (Array.isArray(user.roles) && user.roles.length) {
    const firstRole = user.roles[0]
    return normalizeRole(typeof firstRole === 'string' ? firstRole : firstRole?.name)
  }

  if (Array.isArray(user.permissions)) {
    const hasTeacherPermission = user.permissions.some((permission) => {
      const name = typeof permission === 'string' ? permission : permission?.name
      return normalizeRole(name).includes('teacher')
    })

    if (hasTeacherPermission) return 'teacher'
  }

  return ''
}

export const getUserRole = () => {
  const storedRole = normalizeRole(getStoredRole())
  if (storedRole) return storedRole

  const roleFromUser = resolveUserRole(getUser())
  if (roleFromUser) {
    setStoredRole(roleFromUser)
  }
  return roleFromUser
}

export const setUserRole = (role) => {
  const normalizedRole = normalizeRole(role)
  if (!normalizedRole) {
    clearStoredRole()
    return
  }
  setStoredRole(normalizedRole)
}

export const clearUserRole = () => {
  clearStoredRole()
}

// Reactive state declarations
export const authUser = ref(getUser())
export const userRole = ref(getUserRole())

export const clearAllAuthData = () => {
  clearToken()
  clearStudentSession()
  clearUser()
  clearUserRole()
}

export const logout = async () => {
  try {
    await api.post('/auth/logout')
  } catch (error) {
    console.warn('Logout API call failed, proceeding with local cleanup:', error)
  } finally {
    clearAllAuthData()
  }
}

export const hasSession = () => Boolean(getToken() || getStudentSession())

// Student Profile helpers
export const getStudentProfile = () => {
  const user = getUser()
  if (!user) return { name: 'Student', id: 'N/A', email: 'N/A', avatar: '/default-avatar.png' }
  return {
    name: user.name || 'Student',
    id: user.id || user.student_id || 'N/A',
    email: user.email || user.student_email || 'N/A',
    avatar: user.avatar || user.profile_picture || 'https://api.dicebear.com/7.x/avataaars/svg?seed=student'
  }
}

export const studentProfile = ref(getStudentProfile())

export const updateProfile = async (name, avatar) => {
  try {
    const payload = { name }
    if (avatar && avatar.startsWith('data:')) {
      // It's a base64 image, convert to file or handle separately
      payload.avatar = avatar
    }
    const response = await profileService.updateProfile(payload)
    
    // Update local user data
    const currentUser = getUser()
    if (currentUser) {
      const updatedUser = { ...currentUser, name, avatar }
      setUser(updatedUser)
      // Update the reactive ref
      studentProfile.value = getStudentProfile()
    }
    
    return response
  } catch (error) {
    console.error('Failed to update profile:', error)
    throw error
  }
}
