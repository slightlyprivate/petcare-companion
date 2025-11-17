// Shared list of BFF JSON endpoint prefixes that should be routed through
// the Node BFF and rewritten to the Laravel API under /api/*.
// Used by both the dev Vite proxy (UI) and the BFF server routing.
export const BFF_REWRITE_PREFIXES = ['/user', '/pets', '/appointments', '/credits', '/gifts'];
