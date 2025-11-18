// Centralized route path constants and helpers
export const PATHS = {
  ROOT: '/',
  DISCOVER: '/discover',
  PUBLIC: {
    PET_DETAIL: (slug: string | ':slug' = ':slug') => `/pet/${slug}`,
  },
  AUTH: {
    ROOT: '/auth',
    SIGNIN: '/auth/signin',
    SIGNUP: '/auth/signup',
    VERIFY: '/auth/verify',
  },
  DASHBOARD: {
    ROOT: '/dashboard',
    PETS: '/dashboard/pets',
    PETS_NEW: '/dashboard/pets/new',
    PET_DETAIL: (id: string | number | ':id' = ':id') => `/dashboard/pets/${id}`,
    PET_SETTINGS: (id: string | number | ':id' = ':id') => `/dashboard/pets/${id}/settings`,
    APPOINTMENTS: '/dashboard/appointments',
    GIFTS: '/dashboard/gifts',
    ACCOUNT: '/dashboard/account',
    ADMIN: {
      GIFT_TYPES: '/dashboard/admin/gift-types',
    },
  },
  DEV: '/dev',
} as const;

export type AppPaths = typeof PATHS;
