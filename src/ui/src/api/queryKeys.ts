/**
 * Query keys for React Query
 */
export const qk = {
  auth: {
    me: ['auth', 'me'] as const,
  },
  user: {
    prefs: ['user', 'prefs'] as const,
  },
  pets: {
    all: ['pets', 'public'] as const,
    detail: (id: number | string) => ['pets', 'public', id] as const,
    mine: ['pets', 'mine'] as const,
  },
  appts: {
    byPet: (petId: number | string) => ['appts', 'byPet', petId] as const,
  },
  gifts: {
    types: ['gifts', 'types'] as const,
  },
  credits: {
    purchases: ['credits', 'purchases'] as const,
  },
  caregivers: {
    byPet: (petId: number | string) => ['caregivers', 'byPet', petId] as const,
    invitations: ['caregivers', 'invitations'] as const,
  },
  activities: {
    all: ['activities'] as const,
    byPet: (petId: number | string, params?: Record<string, unknown>) =>
      ['activities', 'byPet', petId, params] as const,
  },
  routines: {
    all: ['routines'] as const,
    byPet: (petId: number | string) => ['routines', 'byPet', petId] as const,
    today: (petId: number | string) => ['routines', 'today', petId] as const,
  },
};
