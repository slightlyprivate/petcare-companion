import { api } from '../../lib/http';

/**
 * Fetch the list of credit purchases for the authenticated user.
 */
export const listPurchases = () => api('/credits/purchases');

/**
 * Purchase credits for the authenticated user.
 */
export const purchase = (payload: { amount_credits: number }) =>
  api('/credits/purchase', { method: 'POST', body: payload });
