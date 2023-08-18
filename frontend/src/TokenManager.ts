class TokenManager {
    private static AUTH_TOKEN: string = "auth_token"

    public static getToken(): string|null {
        return localStorage.getItem(TokenManager.AUTH_TOKEN);
    }

    public static storeToken(token: string) {
        localStorage.setItem(TokenManager.AUTH_TOKEN, token)
    }

    public static clearToken() {
        localStorage.removeItem(TokenManager.AUTH_TOKEN)
    }
}

export default TokenManager