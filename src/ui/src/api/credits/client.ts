import { http } from '../../lib/http';

/**
 * Fetch the list of credit purchases for the authenticated user.
 */
export const listPurchases = () => http('/credits/purchases');

/**
 * Purchase credits for the authenticated user.
 */
export const purchase = (payload: { amount_credits: number }) =>
  http('/credits/purchase', { method: 'POST', body: payload });
