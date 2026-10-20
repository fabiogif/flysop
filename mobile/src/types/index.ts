export interface LocationCoords {
  latitude: number;
  longitude: number;
  address?: string;
  neighborhood?: string;
  city?: string;
  state?: string;
  zip?: string;
}

export interface TypeOccurrence {
  id: number;
  name: string;
}

export interface StatusOccurrence {
  id: number;
  name: string;
  sort_order: number;
  is_terminal: boolean;
}

export interface OccurrenceImage {
  id: number;
  url: string;
  phase?: 'antes' | 'depois';
  latitude?: number;
  longitude?: number;
  captured_at?: string;
}

export interface Occurrence {
  id: number;
  protocol: string;
  title: string;
  description?: string;
  address?: string;
  neighborhood?: string;
  city?: string;
  state?: string;
  zip?: string;
  latitude?: number;
  longitude?: number;
  type_occurrences?: number;
  nameType?: string;
  status_occurrences_id: number;
  nameStatus?: string;
  clients_id: number;
  created_at: string;
  anexo?: string[];
}

export interface SurveyQuestion {
  id: number;
  type: 'text' | 'single_choice' | 'scale';
  prompt: string;
  options?: string[];
  required: boolean;
  sort_order: number;
}

export interface Survey {
  id: number;
  title: string;
  description?: string;
  public_token: string;
  is_active: boolean;
  questions?: SurveyQuestion[];
}

export interface Client {
  id: number;
  name: string;
  email?: string;
  phone?: string;
  username?: string;
  avatar_url?: string;
  bio?: string;
  website?: string;
}

export interface Manager {
  id: number;
  name: string;
  email: string;
  role: 'manager';
  issuing: { id: number; name: string } | null;
  notify_new_occurrences: boolean;
}

export interface OccurrenceHistoryEntry {
  status: string | null;
  note: string | null;
  changed_by: string | null;
  created_at: string;
}

export interface AppNotification {
  id: string;
  type: string;
  data: {
    occurrence_id?: number;
    protocol?: string;
    title?: string;
    type?: string;
    address?: string | null;
    author?: string | null;
    message?: string;
  };
  read_at: string | null;
  created_at: string;
}

export interface PublicProfile {
  id: number;
  name: string;
  username?: string;
  avatar_url?: string;
  bio?: string;
  website?: string;
  created_at?: string;
  occurrences_count: number;
}
