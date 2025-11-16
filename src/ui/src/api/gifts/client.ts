import { api } from '../../lib/http';

/**
 * Fetch the list of available gift types.
 */
export const listGiftTypes = () => api('/public/gift-types');

/**
 * Create a gift for a specific pet.
 */
export const createGift = (payload: { petId: number | string; gift_type_id: number }) =>
  api(`/pets/${payload.petId}/gifts`, { method: 'POST', body: payload });

/**
 * Export the receipt for a specific gift.
 */
export const exportReceipt = (giftId: number | string) => api(`/gifts/${giftId}/receipt`);
