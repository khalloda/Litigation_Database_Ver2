
import type { Case, Lawyer, Client, Opponent, Task, DBCase, Court, CaseOpponent, ClientDocument, Hearing, Role, Team, User } from '../types';
// Fix: Import all required mock data from database.ts
import { dbLawyers, dbClients, dbOpponents, dbOptionValues, dbContacts, dbClientDocuments, dbPowerOfAttorneys, dbCases, dbHearings, dbCourts, dbDocumentMovements, dbRoles, dbTeams, dbUsers } from './database';

export const mockTasks: Task[] = [
  { id: 1, case_id: 1116, title: 'Submit discovery documents for Toyota', dueDate: '2024-08-15', status: 'in-progress', priority: 'high', description: 'Finalize and submit all requested documents for case 983 / 11ق.' },
  { id: 2, case_id: 1116, title: 'Prepare for client meeting with Toyota', dueDate: '2024-08-10', status: 'todo', priority: 'medium', description: 'Create agenda and talking points for the upcoming meeting with Toyota Egypt.' },
  { id: 3, case_id: 573, title: 'Review EGX committee decision', dueDate: '2024-07-30', status: 'completed', priority: 'high', description: 'Analyze the decision from the exchange committee for the Masters case.' },
  { id: 4, case_id: 29, title: 'Draft appeal brief for Modern TV', dueDate: '2024-09-01', status: 'todo', priority: 'high', description: 'Begin drafting the primary appeal brief based on recent findings against Memes Egypt.' },
  { id: 5, case_id: 1309, title: 'Follow up on arbitration nullity case', dueDate: '2024-08-05', status: 'in-progress', priority: 'medium', description: 'Check status of the Damietta Port Authority arbitration case.' },
  { id: 6, case_id: 457, title: 'Archive closed criminal records case', dueDate: '2024-07-25', status: 'completed', priority: 'low', description: 'Digitize and archive all physical documents for the closed Hammad Abdullah case.' },
  { id: 7, case_id: 576, title: 'Prepare for heirs of Ezz El-Din hearing', dueDate: '2024-09-20', status: 'todo', priority: 'high', description: 'Prepare arguments for the upcoming appeal hearing.' },
  { id: 8, parentId: 4, case_id: 29, title: 'Research precedents', dueDate: '2024-08-15', status: 'todo', priority: 'high' },
  { id: 9, parentId: 4, case_id: 29, title: 'Outline arguments', dueDate: '2024-08-20', status: 'todo', priority: 'high' },
  { id: 10, parentId: 1, case_id: 1116, title: 'Get partner sign-off', dueDate: '2024-08-14', status: 'in-progress', priority: 'medium' },
];

// This is the new factory for mockCases, processing the raw data from the database
export const mockCases: Case[] = dbCases.map((dbCase: DBCase): Case | null => {
  const client = dbClients.find(c => c.id === dbCase.client_id);
  const opponent = dbOpponents.find(o => o.id === dbCase.opponent_id);
  const partner = dbLawyers.find(l => l.id === dbCase.matter_partner_id);
  const court = dbCase.court_id ? dbCourts.find(c => c.id === dbCase.court_id) : null;
  const team = dbCase.team_id ? dbTeams.find(t => t.id === dbCase.team_id) : null;
  
  // Find secondary lawyers by name string
  const lawyer_a = dbCase.lawyer_a ? dbLawyers.find(l => l.lawyer_name_ar === dbCase.lawyer_a || l.lawyer_name_en === dbCase.lawyer_a) : null;
  const lawyer_b = dbCase.lawyer_b ? dbLawyers.find(l => l.lawyer_name_ar === dbCase.lawyer_b || l.lawyer_name_en === dbCase.lawyer_b) : null;

  const getOptionLabel = (id: number | null | undefined, lang: 'en' | 'ar' = 'en', fallback: string = 'N/A'): string => {
      if (!id) return fallback;
      const option = dbOptionValues.find(o => o.id === id);
      if (!option) return fallback;
      const label = lang === 'ar' ? option.label_ar : option.label_en;
      return label || option.code || fallback;
  };
  
  if (!client || !partner) {
    // This case has missing required data (like a client or partner), so we skip it to avoid errors.
    return null;
  }
  
  const opponents: CaseOpponent[] = [];
  if (opponent) {
    const opponentCapacityOption = dbOptionValues.find(o => o.id === dbCase.opponent_capacity_id);
    opponents.push({
      ...opponent,
      in_case_name: dbCase.opponent_in_case_name,
      capacity: opponentCapacityOption ? opponentCapacityOption.label_en : 'N/A',
      capacity_note: dbCase.opponent_capacity_note
    });
  }
  
  const hearings = dbHearings.filter(h => h.matter_id === dbCase.id);
  const tasks = mockTasks.filter(t => t.case_id === dbCase.id);
  const documents = dbClientDocuments.filter(d => d.matter_id === dbCase.id);
  
  const statusOption = dbOptionValues.find(o => o.id === dbCase.matter_status_id);

  // Create the final, rich Case object for the UI
  return {
    id: dbCase.id,
    
    // Overview
    matter_name_ar: dbCase.matter_name_ar,
    matter_name_en: dbCase.matter_name_en,
    team: team || null,
    case_start_date: dbCase.matter_start_date || 'N/A',
    case_end_date: dbCase.matter_end_date,

    // Parties
    client: client,
    client_in_case_name: dbCase.client_in_case_name,
    client_capacity: getOptionLabel(dbCase.client_capacity_id),
    client_capacity_note: dbCase.client_capacity_note,
    opponents: opponents,
    lawyer_a,
    lawyer_b,
    partner,
    
    // Court & Circuit
    court: court || null,
    matter_destination: getOptionLabel(dbCase.matter_destination_id),
    circuit_name: getOptionLabel(dbCase.circuit_name_id),
    circuit_serial: getOptionLabel(dbCase.circuit_serial_id),
    circuit_shift: getOptionLabel(dbCase.circuit_shift_id),
    circuit_secretary: getOptionLabel(dbCase.circuit_secretary),
    court_floor: dbCase.court_floor ? dbCase.court_floor.toString() : 'N/A',
    court_hall: dbCase.court_hall ? dbCase.court_hall.toString() : 'N/A',
    
    // Status & Progress
    case_degree: getOptionLabel(dbCase.matter_degree_id),
    status: getOptionLabel(dbCase.matter_status_id),
    case_importance: getOptionLabel(dbCase.matter_importance_id),
    case_category: getOptionLabel(dbCase.matter_category_id),
    current_status: dbCase.current_status,
    matter_evaluation: dbCase.matter_evaluation,

    // Financials
    client_type: getOptionLabel(dbCase.client_type_id),
    allocated_budget: dbCase.allocated_budget,
    case_asked_amount: dbCase.matter_asked_amount || 0,
    case_judged_amount: dbCase.matter_judged_amount,
    financial_provision: dbCase.financial_provision,
    fee_letter: dbCase.fee_letter ?? null,
    contract_id: dbCase.contract_id,
    
    // Documents & Hearings
    hearings,
    tasks,
    documents,
  
    // Meta & Audit
    matter_shelf: dbCase.matter_shelf,
    client_branch: dbCase.client_branch,
    matter_branch_id: dbCase.matter_branch_id,
    category_legacy: dbCase.matter_category,
    degree_legacy: dbCase.matter_degree,
    status_legacy: dbCase.matter_status,
    court_text_legacy: dbCase.matter_court_text,
    circuit_legacy: dbCase.matter_circuit_legacy,
    case_description: dbCase.matter_description || 'No description available.',
    legal_opinion: dbCase.legal_opinion || 'No opinion recorded.',
    notes_1: dbCase.notes_1,
    notes_2: dbCase.notes_2,
    engagement_letter_no: dbCase.engagement_letter_no,
    created_at: '2015-11-01 15:57:54', // Mock data
    updated_at: '2015-11-01 15:57:54', // Mock data
    matter_select: dbCase.matter_select,

    // Compatibility fields
    case_number: dbCase.matter_name_ar,
    case_name_en: dbCase.matter_name_en,
    case_name_ar: dbCase.matter_name_en,
  };
}).filter((c): c is Case => c !== null); // Filter out any nulls from cases with missing data


export const getClientById = (id: number): Client | undefined => {
    const client = dbClients.find(c => c.id === id);
    if (!client) return undefined;

    return {
        ...client,
        contacts: dbContacts.filter(c => c.client_id === id),
        documents: dbClientDocuments.filter(doc => doc.client_id === id),
        power_of_attorneys: dbPowerOfAttorneys.filter(poa => poa.client_id === id),
        cases: mockCases.filter(c => c.client.id === id)
    };
};

export const getOpponentById = (id: number): Opponent | undefined => {
    const opponent = dbOpponents.find(o => o.id === id);
    if (!opponent) return undefined;
    
    return {
      ...opponent,
      cases: mockCases.filter(c => c.opponents.some(o => o.id === id))
    };
};

export const getLawyerById = (id: number): Lawyer | undefined => {
    const lawyer = dbLawyers.find(l => l.id === id);
    if (!lawyer) return undefined;

    return {
        ...lawyer,
        cases: mockCases.filter(c => 
            c.partner.id === id || 
            c.lawyer_a?.id === id || 
            c.lawyer_b?.id === id
        )
    };
};

export const getCourtById = (id: number): Court | undefined => {
    const court = dbCourts.find(c => c.id === id);
    if (!court) return undefined;

    return {
        ...court,
        cases: mockCases.filter(c => c.court?.id === id)
    };
};

export const getHearingById = (id: number): Hearing | undefined => {
    const hearing = dbHearings.find(h => h.id === id);
    if (!hearing) return undefined;

    const populatedHearing: Hearing = {
        ...hearing,
        case: mockCases.find(c => c.id === hearing.matter_id),
        lawyer: hearing.lawyer_id ? dbLawyers.find(l => l.id === hearing.lawyer_id) : undefined
    };
    return populatedHearing;
};

export const getDocumentById = (id: number): ClientDocument | undefined => {
    const doc = dbClientDocuments.find(d => d.id === id);
    if (!doc) return undefined;

    const movements = dbDocumentMovements
        .filter(m => m.document_id === id)
        .map(movement => ({
            ...movement,
            lawyer: dbLawyers.find(l => l.id === movement.lawyer_id)
        }))
        .sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());

    const populatedDoc: ClientDocument = {
        ...doc,
        client: dbClients.find(c => c.id === doc.client_id),
        case: doc.matter_id ? mockCases.find(c => c.id === doc.matter_id) : undefined,
        movements: movements,
    };
    return populatedDoc;
}

export const getRoleById = (id: number): Role | undefined => {
    return dbRoles.find(r => r.id === id);
};

export const getTeamById = (id: number): Team | undefined => {
    const team = dbTeams.find(t => t.id === id);
    if (!team) return undefined;

    const members = dbLawyers.filter(l => team.lawyer_ids.includes(l.id));

    return {
        ...team,
        members,
    };
};

export const getUsers = (): User[] => {
    return dbUsers.map(user => ({
        ...user,
        role: dbRoles.find(r => r.id === user.role_id)
    }));
};

export const getUserById = (id: number): User | undefined => {
    const user = dbUsers.find(u => u.id === id);
    if (!user) return undefined;

    return {
        ...user,
        role: dbRoles.find(r => r.id === user.role_id)
    };
};


// For filtering in the dashboard, use the full lists from the database
export const mockClients = dbClients;
export const mockOpponents = dbOpponents;
