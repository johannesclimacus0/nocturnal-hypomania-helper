import http from './http'

export interface RegisterData {
    name: string
    email: string
    password: string
    password_confirmation: string
}

export interface LoginData {
    email: string
    password: string
    remember: boolean
}

export interface LoginResult {
    two_factor: boolean
    redirect: string|null
}

export interface ForgotPasswordData {
    email: string
}

export interface ResetPasswordData {
    token: string
    email: string
    password: string
    password_confirmation: string
}

export interface User {
    id: number
    name: string
    email: string
    email_verified_at: string|null
}

export async function register(data: RegisterData): Promise<void> {
    await http.post('/register', data)
}

export async function login(data: LoginData): Promise<LoginResult> {
    const response = await http.post<LoginResult>('/login', data)

    return response.data
}

export async function logout(): Promise<void> {
    await http.post('/logout')
}

export async function resendVerificationEmail(): Promise<void> {
    await http.post('/email/verification-notification')
}

export async function forgotPassword(data: ForgotPasswordData): Promise<string> {
    const response = await http.post('/forgot-password', data)

    return response.data.message
}

export async function resetPassword(data: ResetPasswordData): Promise<string> {
    const response = await http.post('/reset-password', data)

    return response.data.message
}

export async function getCurrentUser(): Promise<User> {
    const response = await http.get<User>('/me')

    return response.data
}
