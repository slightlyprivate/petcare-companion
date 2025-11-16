/**
 * User of the PetCare Companion application.
 */
export interface User {
  id: number;
  email: string;
  role?: string;
}

/**
 * Public information about a pet.
 */
export interface Pet {
  id: number;
  name: string;
  species: string;
}

/**
 * Appointment for a pet.
 */
export interface Appointment {
  id: number;
  pet_id: number;
  title: string;
  scheduled_at: string;
}

/**
 * Gift type available for purchase with credits.
 */
export interface GiftType {
  id: number;
  name: string;
  cost_in_credits: number;
}

/**
 * Credit purchase made by a user.
 */
export interface CreditPurchase {
  id: number;
  amount_credits: number;
  status: string;
}

/**
 * Paginated response wrapper.
 */
export interface Paginated<T> {
  data: T[];
  meta?: {
    total: number;
    page: number;
    per_page: number;
  };
}
