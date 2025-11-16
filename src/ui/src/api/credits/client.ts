import { api } from '../../lib/http';
import type { Paginated as PaginatedType } from '../../lib/fetch';
import { normalizePaginated } from '../../lib/fetch';
import type { CreditPurchase } from '../types';

/**
 * Fetch the list of credit purchases for the authenticated user.
 */
export const listPurchases = async (): Promise<PaginatedType<CreditPurchase>> => {
  const res = await api('/credits/purchases');
  return normalizePaginated<CreditPurchase>(res);
};

/**
 * Purchase credits for the authenticated user.
 */
export const purchase = (payload: { amount_credits: number }) =>
  api('/credits/purchase', { method: 'POST', body: payload });
