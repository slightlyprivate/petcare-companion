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
    byPet: (petId: number | string) => ['gifts', 'byPet', petId] as const,
  },
  credits: {
    purchases: ['credits', 'purchases'] as const,
  },
};
