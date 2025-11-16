import { api } from '../../lib/http';
import type { GiftType } from '../types';

/**
 * Fetch the list of available gift types.
 */
export const listGiftTypes = async (): Promise<GiftType[]> => {
  const res = await api('/public/gift-types');
  return normalizePaginated<GiftType>(res).data;
};

/**
 * Create a gift for a specific pet.
 */
export const createGift = (payload: { petId: number | string; gift_type_id: number }) =>
  api(`/pets/${payload.petId}/gifts`, { method: 'POST', body: payload });

/**
 * Export the receipt for a specific gift.
 */
export const exportReceipt = (giftId: number | string) => api(`/gifts/${giftId}/receipt`);
