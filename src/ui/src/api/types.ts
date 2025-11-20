/**
 * User of the PetCare Companion application.
 */
import type { Role } from '../constants/roles';

export interface User {
  id: string;
  email: string;
  role?: Role;
}

/**
 * Public information about a pet.
 */
export interface Pet {
  id: string;
  name: string;
  species: string;
  user_id?: string;
  breed?: string | null;
  birth_date?: string | null;
  owner_name?: string;
  age?: number | null;
  created_at?: string;
  updated_at?: string;
}

/**
 * Appointment for a pet.
 */
export interface Appointment {
  id: string;
  pet_id: string;
  title: string;
  scheduled_at: string;
}

/**
 * Gift type available for purchase with credits.
 */
export interface GiftType {
  id: string;
  name: string;
  cost_in_credits: number;
}

/**
 * Gift given to a pet.
 */
export interface Gift {
  id: string;
  pet_id: string;
  gift_type_id: string;
  created_at?: string;
}

/**
 * Credit purchase made by a user.
 */
export interface CreditPurchase {
  id: string;
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

/**
 * User notification preferences.
 * Shape may vary by backend; common flags included.
 */
export interface NotificationPreferences {
  email_enabled?: boolean;
  sms_enabled?: boolean;
  push_enabled?: boolean;
  [key: string]: unknown;
}

/**
 * Opaque export types used in Dev Playground displays.
 */
export type GiftReceipt = Record<string, unknown>;
export type PetReport = Record<string, unknown>;
