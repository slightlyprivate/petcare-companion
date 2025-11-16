import { api } from '../../lib/http';
import { normalizePaginated, type Paginated as PaginatedType } from '../../lib/fetch';
import type { Gift, GiftType } from '../types';

/**
 * Fetch the list of available gift types.
 */
export const listGiftTypes = (): Promise<GiftType[]> => api('/public/gift-types');

/**
 * Create a gift for a specific pet.
 */
export const createGift = (payload: { petId: number | string; gift_type_id: number }) =>
  api(`/pets/${payload.petId}/gifts`, { method: 'POST', body: payload });

/**
 * Export the receipt for a specific gift.
 */
export const exportReceipt = (giftId: number | string) => api(`/gifts/${giftId}/receipt`);

/**
 * List gifts for a given pet (paginated or array response supported).
 */
export const listByPet = async (petId: number | string): Promise<PaginatedType<Gift>> => {
  const res = await api(`/pets/${petId}/gifts`);
  return normalizePaginated<Gift>(res);
};
