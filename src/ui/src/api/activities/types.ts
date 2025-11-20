/**
 * Pet activity types and interfaces
 */

export interface PetActivity {
  id: string;
  pet_id: string;
  user_id?: string;
  type: string;
  description: string;
  media_url?: string | null;
  created_at: string;
  updated_at?: string;
}

export interface PetActivitiesResponse {
  data: PetActivity[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface CreateActivityPayload {
  petId: number | string;
  type: string;
  description: string;
  media_url?: string | null;
}

export interface ListActivitiesParams {
  per_page?: number;
  type?: string;
  date_from?: string;
  date_to?: string;
}
