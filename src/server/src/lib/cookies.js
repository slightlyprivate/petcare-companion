import { config } from "./config.js";
import { COOKIE_LOGGED_IN, COOKIE_TOKEN } from "../constants.js";

// Cookie options for token status cookies
export function tokenCookieOptions() {
    return {
        httpOnly: true,
        secure: config.secureCookies,
        sameSite: config.sameSite || "lax",
        path: "/",
        maxAge: 7 * 24 * 60 * 60 * 1000,
    };
}

// Cookie options for logged-in status cookies
export function loggedInCookieOptions() {
    return {
        httpOnly: false,
        secure: config.secureCookies,
        sameSite: config.sameSite || "lax",
        path: "/",
        maxAge: 7 * 24 * 60 * 60 * 1000,
    };
}

// Export cookie names for external use
export const CookieNames = {
    TOKEN: COOKIE_TOKEN,
    LOGGED_IN: COOKIE_LOGGED_IN,
};
