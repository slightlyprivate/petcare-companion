// Shared constants for the BFF

export const API_PREFIX = "/api";

// Headers we forward from the client to the upstream API
export const PASS_THROUGH_HEADERS = ["accept", "content-type", "user-agent"];

// Cookie names
export const COOKIE_TOKEN = "pc_token";
export const COOKIE_LOGGED_IN = "pc_logged_in";

// CSRF header names (accepted by middleware)
export const CSRF_HEADER_PRIMARY = "x-csrf-token";
export const CSRF_HEADER_FALLBACK = "x-xsrf-token";
