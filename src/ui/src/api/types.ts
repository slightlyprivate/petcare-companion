/**
 * User of the PetCare Companion application.
 */
import type { Role } from '../constants/roles';

export interface User {
  id: number;
  email: string;
  role?: Role;
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
 * Gift given to a pet.
 */
export interface Gift {
  id: number;
  pet_id: number;
  gift_type_id: number;
  created_at?: string;
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
