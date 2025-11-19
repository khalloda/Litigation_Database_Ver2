
export type Language = 'en' | 'ar';
export type LanguageDirection = 'ltr' | 'rtl';

// Based on option_values where set_id=15
export type CaseStatus = 'closed' | 'active' | 'pending';
// Based on option_values where set_id=16
export type CaseImportance = 'critical' | 'urgent' | 'normal' | 'important' | 'grievance';

// Task Management Types
export type TaskStatus = 'todo' | 'in-progress' | 'completed';
export type TaskPriority = 'high' | 'medium' | 'low';

export interface Task {
  id: number;
  case_id: number;
  title: string;
  description?: string;
  dueDate: string;
  status: TaskStatus;
  priority: TaskPriority;
  parentId?: number;
}


// Based on `lawyers` table schema
export interface Lawyer {
  id: number;
  lawyer_name_ar: string;
  lawyer_name_en: string;
  lawyer_name_title?: string | null;
  title_id?: number | null;
  lawyer_email?: string | null;
  attendance_track: boolean;
  // Aggregated data
  cases?: Case[];
}

// Based on `contacts` table schema
export interface Contact {
  id: number;
  client_id: number;
  contact_name?: string | null;
  full_name?: string | null;
  job_title?: string | null;
  address?: string | null;
  city?: string | null;
  state?: string | null;
  country?: string | null;
  zip_code?: string | null;
  business_phone?: string | null;
  home_phone?: string | null;
  mobile_phone?: string | null;
  fax_number?: string | null;
  email?: string | null;
  web_page?: string | null;
  attachments?: string | null;
}

// Based on `power_of_attorneys` table schema
export interface PowerOfAttorney {
    id: number;
    client_id: number;
    client_print_name?: string | null;
    principal_name: string;
    year?: number | null;
    capacity?: string | null;
    authorized_lawyers?: string | null;
    issue_date?: string | null;
    inventory: boolean;
    issuing_authority?: string | null;
    letter?: string | null;
    poa_number?: number | null;
    principal_capacity?: string | null;
    copies_count?: number | null;
    serial?: string | null;
    notes?: string | null;
}

export type DocumentMovementStatus = 'checked_out' | 'checked_in' | 'archived' | 'transferred';

export interface DocumentMovement {
  id: number;
  document_id: number;
  date: string;
  from_location: string;
  to_location: string;
  status: DocumentMovementStatus;
  notes?: string | null;
  lawyer_id: number;
  // For detail view
  lawyer?: Lawyer;
}

// Based on `client_documents` table schema
export interface ClientDocument {
    id: number;
    client_id: number;
    matter_id?: number | null;
    client_name?: string | null;
    document_name?: string | null;
    document_type?: string | null;
    file_path?: string | null;
    file_size?: number | null;
    mime_type?: string | null;
    document_storage_type: 'physical' | 'digital' | 'both';
    mfiles_uploaded: boolean;
    mfiles_id?: string | null;
    responsible_lawyer?: string | null;
    movement_card: boolean;
    document_description?: string | null;
    deposit_date: string;
    document_date?: string | null;
    case_number?: string | null;
    pages_count?: string | null;
    notes?: string | null;
    // For detail view
    client?: Client;
    case?: Case;
    movements?: DocumentMovement[];
}

// Based on `clients` table schema
export interface Client {
  id: number;
  mfiles_id?: number | null;
  client_code?: string | null;
  client_name_ar: string;
  client_name_en?: string | null;
  client_print_name: string;
  status?: string | null;
  cash_or_probono?: string | null;
  client_start?: string | null;
  client_end?: string | null;
  contact_lawyer?: string | null;
  contact_lawyer_id?: number | null;
  logo?: string | null;
  power_of_attorney_location?: string | null;
  documents_location?: string | null;
  cash_or_probono_id?: number | null;
  status_id?: number | null;
  power_of_attorney_location_id?: number | null;
  documents_location_id?: number | null;
  // Aggregated data
  contacts?: Contact[];
  documents?: ClientDocument[];
  power_of_attorneys?: PowerOfAttorney[];
  cases?: Case[];
}

// Based on `opponents` table schema
export interface Opponent {
  id: number;
  opponent_name_ar?: string | null;
  opponent_name_en?: string | null;
  normalized_name?: string | null;
  is_active: boolean;
  description?: string | null;
  notes?: string | null;
  first_token?: string | null;
  last_token?: string | null;
  token_count?: number | null;
  latin_key?: string | null;
  // This will be added when linking in a case
  capacity?: string;
  // Aggregated data
  cases?: Case[];
}

export interface CaseOpponent extends Opponent {
    in_case_name?: string | null;
    capacity: string;
    capacity_note?: string | null;
}

// Based on the raw `cases` table from database.ts
export interface DBCase {
  id: number;
  client_id: number;
  client_in_case_name?: string | null;
  opponent_in_case_name?: string | null;
  contract_id?: number | null;
  engagement_letter_no?: string | null;
  matter_name_ar: string;
  matter_name_en: string;
  matter_description?: string | null;
  matter_status?: string | null;
  matter_status_id?: number | null;
  matter_category?: string | null;
  matter_category_id?: number | null;
  court_id?: number | null;
  circuit_name_id?: number | null;
  circuit_serial_id?: number | null;
  circuit_shift_id?: number | null;
  matter_circuit_legacy?: number | null;
  circuit_secretary?: number | null;
  court_floor?: number | null;
  court_hall?: number | null;
  matter_degree?: string | null;
  matter_degree_id?: number | null;
  matter_court_text?: string | null;
  matter_destination?: string | null;
  matter_destination_id?: number | null;
  matter_importance?: string | null;
  matter_importance_id?: number | null;
  matter_evaluation?: string | null;
  matter_start_date?: string | null;
  matter_end_date?: string | null;
  matter_asked_amount?: number | null;
  matter_judged_amount?: number | null;
  matter_shelf?: string | null;
  matter_partner?: string | null;
  matter_partner_id?: number | null;
  lawyer_a?: string | null;
  lawyer_b?: string | null;
  fee_letter?: number | null;
  allocated_budget?: string | null;
  team_id?: number | null;
  legal_opinion?: string | null;
  financial_provision?: string | null;
  current_status?: string | null;
  notes_1?: string | null;
  notes_2?: string | null;
  client_and_capacity?: string | null;
  client_capacity_id?: number | null;
  opponent_and_capacity?: string | null;
  opponent_id?: number | null;
  opponent_capacity_id?: number | null;
  client_branch?: string | null;
  matter_branch_id?: number | null;
  client_type?: string | null;
  client_type_id?: number | null;
  matter_select: boolean;
  client_capacity_note?: string | null;
  opponent_capacity_note?: string | null;
}

export interface Team {
  id: number;
  name_en: string;
  name_ar: string;
  description_en?: string;
  description_ar?: string;
  lawyer_ids: number[];
  // For detail view
  members?: Lawyer[];
}

// The rich, processed Case object used throughout the app
export interface Case {
  id: number;
  
  // Overview section
  matter_name_ar: string;
  matter_name_en: string;
  team: Team | null;
  case_start_date: string;
  case_end_date?: string | null;

  // Parties section
  client: Client;
  client_in_case_name?: string | null;
  client_capacity: string;
  client_capacity_note?: string | null;
  opponents: CaseOpponent[];
  lawyer_a?: Lawyer | null;
  lawyer_b?: Lawyer | null;
  partner: Lawyer;

  // Court & Circuit section
  court: Court | null;
  matter_destination?: string | null;
  circuit_name: string;
  circuit_serial: string;
  circuit_shift: string;
  circuit_secretary: string;
  court_floor: string;
  court_hall: string;

  // Status & Progress section
  case_degree: string;
  status: string;
  case_importance: string;
  case_category: string;
  current_status?: string | null;
  matter_evaluation?: string | null;

  // Financials section
  client_type?: string | null;
  allocated_budget?: string | null;
  case_asked_amount: number | null;
  case_judged_amount?: number | null;
  financial_provision?: string | null;
  fee_letter: number | null;
  contract_id?: number | null;

  // Documents & Hearings
  hearings: Hearing[];
  tasks: Task[];
  documents: ClientDocument[];
  
  // Meta & Audit
  matter_shelf?: string | null;
  client_branch?: string | null;
  matter_branch_id?: number | null;
  category_legacy?: string | null;
  degree_legacy?: string | null;
  status_legacy?: string | null;
  court_text_legacy?: string | null;
  circuit_legacy?: number | null;
  case_description: string;
  legal_opinion: string;
  notes_1?: string | null;
  notes_2?: string | null;
  engagement_letter_no?: string | null;
  // TODO: Add created_by, updated_by when user data is available
  created_at?: string; // Mocking with static date for now
  updated_at?: string; // Mocking with static date for now
  matter_select: boolean;
  
  // For compatibility with CaseCard
  case_number: string;
  case_name_en: string;
  case_name_ar: string;
}

// Based on `hearings` table schema
export interface Hearing {
  id: number;
  matter_id: number;
  lawyer_id?: number | null;
  date?: string | null;
  procedure?: string | null;
  court?: string | null;
  circuit?: string | null;
  destination?: string | null;
  decision?: string | null;
  short_decision?: string | null;
  last_decision?: string | null;
  next_hearing_date?: string | null; // Renamed from next_hearing for clarity
  report: boolean;
  notify_client: boolean;
  attendee?: string | null;
  attendee_1?: string | null;
  attendee_2?: string | null;
  attendee_3?: string | null;
  attendee_4?: string | null;
  next_attendee?: string | null;
  evaluation?: string | null;
  notes?: string | null;
  // For detail view
  case?: Case;
  lawyer?: Lawyer;
}

export interface Court {
  id: number;
  court_name_ar?: string | null;
  court_name_en?: string | null;
  is_active: boolean;
  cases?: Case[];
}

export interface DocumentAnalysisResult {
  summary: string;
  entities: {
    people: string[];
    dates: string[];
    locations: string[];
  };
  potential_arguments: string[];
}


export interface Translations {
  [key: string]: string | Translations;
}

// Representing the structure of option_values.sql
export interface OptionValue {
  id: number;
  set_id: number;
  code: string;
  label_en: string;
  label_ar: string;
}

// Representing the structure of option_sets.sql
export interface OptionSet {
    id: number;
    key: string;
    name_en: string;
    name_ar: string;
    description_en: string;
    description_ar: string;
}

export type Permission = 
  | 'case:create' | 'case:view' | 'case:edit' | 'case:delete'
  | 'client:create' | 'client:view' | 'client:edit' | 'client:delete'
  | 'document:create' | 'document:view' | 'document:edit' | 'document:delete'
  | 'user:manage' | 'roles:manage';

export interface Role {
  id: number;
  name_en: string;
  name_ar: string;
  description_en: string;
  description_ar: string;
  permissions: Permission[];
}

export interface PermissionGroup {
  groupKey: string;
  permissions: {
    key: Permission;
    description_en: string;
    description_ar: string;
  }[];
}

export interface User {
  id: number;
  name_en: string;
  name_ar: string;
  email: string;
  role_id: number;
  is_active: boolean;
  // For detail view
  role?: Role;
}
