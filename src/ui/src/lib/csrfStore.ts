let token: string | null = null;

/**
 * Get the current CSRF token.
 */
export function getCsrfToken(): string | null {
  return token;
}

/**
 * Set the CSRF token.
 */
export function setCsrfToken(t: string) {
  token = t;
}
