
import type { Client, Lawyer, Opponent, OptionValue, OptionSet, Contact, ClientDocument, PowerOfAttorney, DBCase, Court, Hearing, DocumentMovement, Role, PermissionGroup, Team, User } from '../types';

// This file is populated from the SQL dumps provided in the initial prompt.

// Empty arrays for tables with no data provided, to prevent import errors.
export const dbContacts: Contact[] = [];
export const dbClientDocuments: ClientDocument[] = [
    { 
        id: 1, client_id: 3, matter_id: 1116, document_name: "Initial Complaint Filing",
        document_type: "Pleading", deposit_date: "2023-01-15", document_storage_type: 'digital',
        mfiles_uploaded: true, responsible_lawyer: "Mohamed Abdelsalam", movement_card: false, case_number: "983 / 11ق"
    },
    { 
        id: 2, client_id: 3, matter_id: 1116, document_name: "Competitor Analysis Report",
        document_type: "Evidence", deposit_date: "2023-02-20", document_storage_type: 'digital',
        mfiles_uploaded: true, responsible_lawyer: "Ehab Hamdy", movement_card: false, case_number: "983 / 11ق"
    },
    { 
        id: 3, client_id: 133, matter_id: 573, document_name: "EGX Committee Decision Transcript",
        document_type: "Official Record", deposit_date: "2024-02-05", document_storage_type: 'physical',
        mfiles_uploaded: false, responsible_lawyer: "Fatma Al-Zahraa", movement_card: true, case_number: "55 / 2024"
    },
    { 
        id: 4, client_id: 2, matter_id: 29, document_name: "Copyright Registration Certificate",
        document_type: "Evidence", deposit_date: "2023-05-12", document_storage_type: 'both',
        mfiles_uploaded: true, responsible_lawyer: "Mona El-Shazly", movement_card: false, case_number: "123 / 2023"
    },
    {
        id: 5, client_id: 1, matter_id: null, document_name: "Master Service Agreement",
        document_type: "Contract", deposit_date: "2020-01-10", document_storage_type: 'digital',
        mfiles_uploaded: true, responsible_lawyer: "John Doe", movement_card: false, case_number: null
    },
    { 
        id: 6, client_id: 2, matter_id: 29, document_name: "Cease and Desist Letter",
        document_type: "Correspondence", deposit_date: "2023-04-28", document_storage_type: 'digital',
        mfiles_uploaded: true, responsible_lawyer: "Jane Smith", movement_card: false, case_number: "123 / 2023"
    }
];
export const dbPowerOfAttorneys: PowerOfAttorney[] = [];

export const dbDocumentMovements: Omit<DocumentMovement, 'lawyer'>[] = [
    { id: 1, document_id: 3, date: '2024-03-10', from_location: 'Archive Room', to_location: 'Fatma Al-Zahraa', status: 'checked_out', lawyer_id: 6, notes: 'Needed for case review.' },
    { id: 2, document_id: 3, date: '2024-03-15', from_location: 'Fatma Al-Zahraa', to_location: 'Court File Prep', status: 'transferred', lawyer_id: 6, notes: 'Sent for copying.' },
    { id: 3, document_id: 3, date: '2024-03-20', from_location: 'Court File Prep', to_location: 'Fatma Al-Zahraa', status: 'checked_in', lawyer_id: 6, notes: 'Returned after copying.' },
    { id: 4, document_id: 3, date: '2024-04-01', from_location: 'Fatma Al-Zahraa', to_location: 'Archive Room', status: 'archived', lawyer_id: 6, notes: 'Case review complete.' },
];

export const dbOptionSets: OptionSet[] = [
    { id: 18, key: 'capacity.type', name_en: 'Capacity Types', name_ar: 'أنواع الصفات', description_en: 'Capacity types for clients and opponents', description_ar: 'أنواع الصفات للعملاء والخصوم' },
    { id: 24, key: 'case.branch', name_en: 'Case Branches', name_ar: 'فروع القضايا', description_en: 'List of case branches', description_ar: 'قائمة بفروع القضايا' },
    { id: 17, key: 'case.category', name_en: 'Case Categories', name_ar: 'فئات القضايا', description_en: 'List of case categories', description_ar: 'قائمة بفئات القضايا' },
    { id: 25, key: 'case.degree', name_en: 'Case Degrees', name_ar: 'درجات القضايا', description_en: 'List of case degrees', description_ar: 'قائمة بدرجات القضايا' },
    { id: 16, key: 'case.importance', name_en: 'Case Importance', name_ar: 'أهمية القضية', description_en: 'List of case importance levels', description_ar: 'قائمة بمستويات أهمية القضايا' },
    { id: 15, key: 'case.status', name_en: 'Case Status', name_ar: 'حالة القضية', description_en: 'List of case statuses', description_ar: 'قائمة بحالات القضايا' },
    { id: 26, key: 'client.cash_or_probono', name_en: 'Cash or Pro Bono', name_ar: 'نقدي أو تطوعي', description_en: 'Client payment type classification', description_ar: 'تصنيف نوع دفع العميل' },
    { id: 19, key: 'circuit.name', name_en: 'Circuit Names', name_ar: 'أسماء الدوائر', description_en: 'List of circuit names', description_ar: 'قائمة بأسماء الدوائر' },
    { id: 22, key: 'court.circuit_secretary', name_en: 'Circuit Secretaries', name_ar: 'أمناء سر الدوائر', description_en: 'List of circuit secretaries', description_ar: 'قائمة بأسماء أمناء سر الدوائر' },
    { id: 20, key: 'circuit.serial', name_en: 'Circuit Serials', name_ar: 'مسلسلات الدوائر', description_en: 'Numeric and alphabetic serials for circuits', description_ar: 'المسلسلات الرقمية والأبجدية للدوائر' },
    { id: 21, key: 'circuit.shift', name_en: 'Circuit Shifts', name_ar: 'فترات الدوائر', description_en: 'Morning or night shift', description_ar: 'فترة صباحية أو مسائية' },
    { id: 27, key: 'client.status', name_en: 'Client Status', name_ar: 'حالة العميل', description_en: 'Current status of the client relationship', description_ar: 'الحالة الحالية لعلاقة العميل' },
    { id: 28, key: 'court.circuit', name_en: 'Court Circuits', name_ar: 'دوائر المحاكم', description_en: 'List of court circuits', description_ar: 'قائمة بدوائر المحاكم' },
    { id: 29, key: 'court.floor', name_en: 'Court Floors', name_ar: 'طوابق المحاكم', description_en: 'List of court floor numbers', description_ar: 'قائمة بأرقام طوابق المحاكم' },
    { id: 30, key: 'court.hall', name_en: 'Court Halls', name_ar: 'قاعات المحاكم', description_en: 'List of court hall numbers', description_ar: 'قائمة بأرقام قاعات المحاكم' },
    { id: 31, key: 'client.documents_location', name_en: 'Documents Location', name_ar: 'موقع المستندات', description_en: 'Physical location of client documents', description_ar: 'الموقع الفعلي لمستندات العميل' },
    { id: 12, key: 'lawyer.title', name_en: 'Lawyer Titles', name_ar: 'ألقاب المحامين', description_en: 'Standardized titles for lawyers', description_ar: 'الألقاب الموحدة للمحامين' },
    { id: 32, key: 'client.power_of_attorney_location', name_en: 'Power of Attorney Location', name_ar: 'موقع التوكيل', description_en: 'Physical location of power of attorney document', description_ar: 'الموقع الفعلي لمستند التوكيل' },
    { id: 23, key: 'document_type', name_en: 'Document Type', name_ar: 'نوع المستند', description_en: 'Categories for client documents', description_ar: 'فئات مستندات العملاء' },
];


// Mocked data for tables where no data was provided
export const dbOpponents: Opponent[] = [
    { id: 1, opponent_name_ar: "الخصم الأول", opponent_name_en: "First Opponent", is_active: true, normalized_name: 'first opponent' },
    { id: 2, opponent_name_ar: "هيئة المنافسة", opponent_name_en: "Competitor Authority", is_active: true, normalized_name: 'competitor authority' },
    { id: 3, opponent_name_ar: "ميمز مصر", opponent_name_en: "Memes Egypt", is_active: false, normalized_name: 'memes egypt' },
    { id: 4, opponent_name_ar: "لجنة البورصة المصرية", opponent_name_en: "EGX Committee", is_active: true, normalized_name: 'egx committee' },
    { id: 5, opponent_name_ar: "شركة شحن", opponent_name_en: "Shipping Co", is_active: true, normalized_name: 'shipping co' },
    { id: 6, opponent_name_ar: "الدولة", opponent_name_en: "The State", is_active: true, normalized_name: 'the state' },
];

export const dbCourts: Court[] = [
    { id: 1, court_name_ar: 'محكمة القاهرة الاقتصادية', court_name_en: 'Cairo Economic Court', is_active: true },
    { id: 2, court_name_ar: 'محكمة النقض', court_name_en: 'Court of Cassation', is_active: true },
    { id: 3, court_name_ar: 'محكمة استئناف القاهرة', court_name_en: 'Cairo Court of Appeals', is_active: false },
];

export const dbOptionValues: OptionValue[] = [
    // set_id: 15 = case_status
    { id: 1, set_id: 15, code: 'active', label_en: 'Active', label_ar: 'سارية' },
    { id: 2, set_id: 15, code: 'closed', label_en: 'Closed', label_ar: 'منتهية' },
    { id: 3, set_id: 15, code: 'pending', label_en: 'Pending', label_ar: 'معلقة' },

    // set_id: 16 = matter_importance
    { id: 4, set_id: 16, code: 'critical', label_en: 'Critical', label_ar: 'حرجة' },
    { id: 5, set_id: 16, code: 'urgent', label_en: 'Urgent', label_ar: 'عاجل' },
    { id: 6, set_id: 16, code: 'normal', label_en: 'Normal', label_ar: 'عادية' },
    { id: 7, set_id: 16, code: 'important', label_en: 'Important', label_ar: 'هامة' },
    
    // set_id: 17 = matter_category (example)
    { id: 8, set_id: 17, code: 'litigation', label_en: 'Litigation', label_ar: 'تقاضي' },
    { id: 9, set_id: 17, code: 'arbitration', label_en: 'Arbitration', label_ar: 'تحكيم' },
    
    // set_id: 18 = party_capacity
    { id: 10, set_id: 18, code: 'plaintiff', label_en: 'Plaintiff', label_ar: 'مدعي' },
    { id: 11, set_id: 18, code: 'defendant_civil', label_en: 'Defendant (Civil)', label_ar: 'مدعى عليه' },
    { id: 12, set_id: 18, code: 'appellant', label_en: 'Appellant', label_ar: 'مستأنف' },
    { id: 13, set_id: 18, code: 'respondent', label_en: 'Respondent', label_ar: 'مستأنف ضده' },

    // set_id: 12 = lawyer_title
    { id: 116, set_id: 12, code: 'founding_partner', label_en: 'Founding Partner', label_ar: 'شريك مؤسس' },
    { id: 117, set_id: 12, code: 'managing_partner', label_en: 'Managing Partner', label_ar: 'شريك مدير' },
    { id: 118, set_id: 12, code: 'partner', label_en: 'Partner', label_ar: 'شريك' },
    { id: 119, set_id: 12, code: 'senior_associate', label_en: 'Senior Associate', label_ar: 'محامي أول' },
    { id: 120, set_id: 12, code: 'associate', label_en: 'Associate', label_ar: 'محامي' },
    { id: 121, set_id: 12, code: 'junior_associate', label_en: 'Junior Associate', label_ar: 'محامي مبتدئ' },

    // circuit details
    { id: 200, set_id: 19, code: 'c_name_1', label_en: 'First Circuit', label_ar: 'الدائرة الأولى' },
    { id: 201, set_id: 20, code: 'c_serial_1', label_en: 'Serial A', label_ar: 'مسلسل أ' },
    { id: 202, set_id: 21, code: 'c_shift_morning', label_en: 'Morning', label_ar: 'صباحي' },
    { id: 203, set_id: 22, code: 'c_sec_ahmed', label_en: 'Ahmed Ali', label_ar: 'أحمد علي' },
    
    // set_id: 23 = document_type
    { id: 300, set_id: 23, code: 'pleading', label_en: 'Pleading', label_ar: 'مرافعة' },
    { id: 301, set_id: 23, code: 'evidence', label_en: 'Evidence', label_ar: 'دليل' },
    { id: 302, set_id: 23, code: 'contract', label_en: 'Contract', label_ar: 'عقد' },
    { id: 303, set_id: 23, code: 'correspondence', label_en: 'Correspondence', label_ar: 'مراسلات' },
    { id: 304, set_id: 23, code: 'official_record', label_en: 'Official Record', label_ar: 'سجل رسمي' },
];

// Mocked hearings data
export const dbHearings: Hearing[] = [
    { id: 1, matter_id: 1116, date: '2024-07-20', decision: 'Postponed for document submission.', next_hearing_date: '2024-08-20', report: false, notify_client: false, lawyer_id: 3, notes: 'Client requested postponement to gather more evidence.', procedure: 'Review', court: 'Cairo Economic Court', circuit: 'Third' },
    { id: 2, matter_id: 1116, date: '2024-06-15', decision: 'Initial hearing.', next_hearing_date: '2024-07-20', report: false, notify_client: false, lawyer_id: 3, notes: 'Scheduled first session.', procedure: 'First Session', court: 'Cairo Economic Court', circuit: 'Third' },
    { id: 3, matter_id: 573, date: '2024-07-01', decision: 'Judgement rendered in favor of the client.', next_hearing_date: undefined, report: true, notify_client: true, lawyer_id: 6, notes: 'Final verdict. Case closed.', procedure: 'Verdict', court: 'Cairo Economic Court', circuit: 'First' },
    { id: 4, matter_id: 29, date: '2024-09-10', decision: 'Pending expert report submission.', next_hearing_date: '2024-10-15', report: false, notify_client: true, lawyer_id: 2, notes: 'Awaiting technical expert report on copyright infringement.', procedure: 'Expert Review', court: 'Court of Cassation', circuit: 'IP Circuit' },
    { id: 5, matter_id: 29, date: '2024-08-05', decision: 'Evidence presented by plaintiff.', next_hearing_date: '2024-09-10', report: false, notify_client: false, lawyer_id: 2, notes: 'Presented documents related to copyright ownership.', procedure: 'Evidence Submission', court: 'Court of Cassation', circuit: 'IP Circuit' }
];

// Mocked raw cases data
export const dbCases: DBCase[] = [
    {
        id: 1116, client_id: 3, matter_name_ar: "983 / 11ق", matter_name_en: "Toyota Egypt vs. Competitor Authority", matter_description: "A case regarding competitive practices in the automotive market.",
        matter_status_id: 1, matter_category_id: 8, court_id: 1, matter_importance_id: 7, matter_start_date: "2023-01-15", matter_end_date: null, matter_partner_id: 5, lawyer_a: 'Ehab Hamdy', lawyer_b: null,
        opponent_id: 2, client_capacity_id: 10, opponent_capacity_id: 11, matter_select: true, matter_asked_amount: 5000000, matter_judged_amount: null,
        legal_opinion: 'Strong position for the client, potential for a favorable settlement.', circuit_name_id: 200, circuit_serial_id: 201, circuit_shift_id: 202, circuit_secretary: 203, court_floor: 3, court_hall: 5, fee_letter: 150000, client_in_case_name: null, opponent_in_case_name: null, contract_id: null, engagement_letter_no: null, matter_status: null, matter_category: null, matter_circuit_legacy: null, matter_degree: null, matter_degree_id: null, matter_court_text: null, matter_destination: null, matter_destination_id: null, matter_importance: null, matter_evaluation: null, matter_shelf: null, matter_partner: null, allocated_budget: null, team_id: 1, financial_provision: null, current_status: null, notes_1: null, notes_2: null, client_and_capacity: null, opponent_and_capacity: null, client_branch: null, matter_branch_id: null, client_type: null, client_type_id: null, client_capacity_note: null, opponent_capacity_note: null
    },
    {
        id: 573, client_id: 133, matter_name_ar: "55 / 2024", matter_name_en: "Masters vs. EGX Committee", matter_description: "Dispute over a decision made by the stock exchange committee.",
        matter_status_id: 2, matter_category_id: 8, court_id: 1, matter_importance_id: 4, matter_start_date: "2024-02-01", matter_end_date: "2024-07-01", matter_partner_id: 6,
        lawyer_a: null, lawyer_b: null, opponent_id: 4, client_capacity_id: 10, opponent_capacity_id: 11, matter_select: true, matter_asked_amount: 1200000, matter_judged_amount: 1200000,
        legal_opinion: 'The committee decision is appealable based on procedural errors.', circuit_name_id: null, circuit_serial_id: null, circuit_shift_id: null, circuit_secretary: null, court_floor: null, court_hall: null, fee_letter: null, client_in_case_name: null, opponent_in_case_name: null, contract_id: null, engagement_letter_no: null, matter_status: null, matter_category: null, matter_circuit_legacy: null, matter_degree: null, matter_degree_id: null, matter_court_text: null, matter_destination: null, matter_destination_id: null, matter_importance: null, matter_evaluation: null, matter_shelf: null, matter_partner: null, allocated_budget: null, team_id: 2, financial_provision: null, current_status: null, notes_1: null, notes_2: null, client_and_capacity: null, opponent_and_capacity: null, client_branch: null, matter_branch_id: null, client_type: null, client_type_id: null, client_capacity_note: null, opponent_capacity_note: null
    },
    {
        id: 29, client_id: 2, matter_name_ar: "123 / 2023", matter_name_en: "Modern TV vs. Memes Egypt", matter_description: "Intellectual property dispute.",
        matter_status_id: 1, matter_category_id: 9, court_id: 2, matter_importance_id: 5, matter_start_date: "2023-05-10", matter_end_date: null, matter_partner_id: 4, lawyer_a: 'Jane Smith', lawyer_b: null,
        opponent_id: 3, client_capacity_id: 10, opponent_capacity_id: 11, matter_select: true, matter_asked_amount: 750000, matter_judged_amount: null,
        legal_opinion: 'Clear case of copyright infringement. High chance of success.', circuit_name_id: null, circuit_serial_id: null, circuit_shift_id: null, circuit_secretary: null, court_floor: null, court_hall: null, fee_letter: 75000, client_in_case_name: null, opponent_in_case_name: null, contract_id: null, engagement_letter_no: null, matter_status: null, matter_category: null, matter_circuit_legacy: null, matter_degree: null, matter_degree_id: null, matter_court_text: null, matter_destination: null, matter_destination_id: null, matter_importance: null, matter_evaluation: null, matter_shelf: null, matter_partner: null, allocated_budget: null, team_id: 3, financial_provision: null, current_status: null, notes_1: null, notes_2: null, client_and_capacity: null, opponent_and_capacity: null, client_branch: null, matter_branch_id: null, client_type: null, client_type_id: null, client_capacity_note: null, opponent_capacity_note: null
    }
];

export const dbLawyers: Lawyer[] = [
  { id: 1, lawyer_name_ar: "جون دو", lawyer_name_en: "John Doe", title_id: 118, lawyer_email: "john.doe@example.com", attendance_track: true },
  { id: 2, lawyer_name_ar: "جين سميث", lawyer_name_en: "Jane Smith", title_id: 119, lawyer_email: "jane.smith@example.com", attendance_track: true },
  { id: 3, lawyer_name_ar: "إيهاب حمدي", lawyer_name_en: "Ehab Hamdy", title_id: 120, lawyer_email: "ehab.hamdy@example.com", attendance_track: true },
  { id: 4, lawyer_name_ar: "منى الشاذلي", lawyer_name_en: "Mona El-Shazly", title_id: 117, lawyer_email: "mona.elshazly@example.com", attendance_track: true },
  { id: 5, lawyer_name_ar: "محمد عبد السلام", lawyer_name_en: "Mohamed Abdelsalam", title_id: 116, lawyer_email: "mohamed.a@example.com", attendance_track: true },
  { id: 6, lawyer_name_ar: "فاطمة الزهراء", lawyer_name_en: "Fatma Al-Zahraa", title_id: 118, lawyer_email: "fatma.z@example.com", attendance_track: true },
];

export const dbClients: Client[] = [
  { id: 1, client_name_ar: "الشركة المتحدة", client_name_en: "United Company", client_print_name: "United Co.", client_code: "UC001", status: "Active" , client_start: "2020-01-01" },
  { id: 2, client_name_ar: "شركة مودرن تي في", client_name_en: "Modern TV Company", client_print_name: "Modern TV", client_code: "MTV01", status: "Inactive", client_start: "2019-05-20" },
  { id: 3, client_name_ar: "تويوتا مصر", client_name_en: "Toyota Egypt", client_print_name: "Toyota Egypt", client_code: "TOY-EG", status: "Active", client_start: "2018-03-12" },
  { id: 133, client_name_ar: "ماسترز", client_name_en: "Masters", client_print_name: "Masters", client_code: "MAS01", status: "Active", client_start: "2021-11-01" },
];

export const dbTeams: Omit<Team, 'members'>[] = [
    {
        id: 1,
        name_en: "Litigation Team Alpha",
        name_ar: "فريق التقاضي ألفا",
        description_en: "Specializes in high-stakes corporate litigation.",
        description_ar: "متخصص في قضايا الشركات الكبرى.",
        lawyer_ids: [1, 3, 5],
    },
    {
        id: 2,
        name_en: "Arbitration Experts",
        name_ar: "خبراء التحكيم",
        description_en: "Handles all international and domestic arbitration cases.",
        description_ar: "يتولى جميع قضايا التحكيم الدولية والمحلية.",
        lawyer_ids: [2, 4, 6],
    },
    {
        id: 3,
        name_en: "IP & Media Law",
        name_ar: "الملكية الفكرية والإعلام",
        description_en: "Focused on intellectual property, copyright, and media disputes.",
        description_ar: "يركز على نزاعات الملكية الفكرية وحقوق النشر والإعلام.",
        lawyer_ids: [2, 4],
    }
];

export const dbRoles: Role[] = [
    {
        id: 1,
        name_en: "Administrator",
        name_ar: "مدير النظام",
        description_en: "Has full access to all system features and settings.",
        description_ar: "لديه وصول كامل لجميع ميزات النظام والإعدادات.",
        permissions: ['case:create', 'case:view', 'case:edit', 'case:delete', 'client:create', 'client:view', 'client:edit', 'client:delete', 'document:create', 'document:view', 'document:edit', 'document:delete', 'user:manage', 'roles:manage']
    },
    {
        id: 2,
        name_en: "Partner",
        name_ar: "شريك",
        description_en: "Can manage cases, clients, and view reports.",
        description_ar: "يمكنه إدارة القضايا والعملاء وعرض التقارير.",
        permissions: ['case:create', 'case:view', 'case:edit', 'case:delete', 'client:create', 'client:view', 'client:edit', 'document:view', 'document:edit']
    },
    {
        id: 3,
        name_en: "Senior Associate",
        name_ar: "محامي أول",
        description_en: "Can work on assigned cases and manage documents.",
        description_ar: "يمكنه العمل على القضايا المعينة وإدارة المستندات.",
        permissions: ['case:view', 'case:edit', 'client:view', 'document:create', 'document:view', 'document:edit']
    },
    {
        id: 4,
        name_en: "Paralegal",
        name_ar: "مساعد قانوني",
        description_en: "Can view case details and upload documents.",
        description_ar: "يمكنه عرض تفاصيل القضايا وتحميل المستندات.",
        permissions: ['case:view', 'client:view', 'document:create', 'document:view']
    }
];

export const dbPermissions: PermissionGroup[] = [
    {
        groupKey: 'case_management',
        permissions: [
            { key: 'case:create', description_en: 'Create new cases', description_ar: 'إنشاء قضايا جديدة' },
            { key: 'case:view', description_en: 'View case details', description_ar: 'عرض تفاصيل القضايا' },
            { key: 'case:edit', description_en: 'Edit case information', description_ar: 'تعديل معلومات القضية' },
            { key: 'case:delete', description_en: 'Delete cases', description_ar: 'حذف القضايا' },
        ]
    },
    {
        groupKey: 'client_management',
        permissions: [
            { key: 'client:create', description_en: 'Create new clients', description_ar: 'إنشاء عملاء جدد' },
            { key: 'client:view', description_en: 'View client details', description_ar: 'عرض تفاصيل العملاء' },
            { key: 'client:edit', description_en: 'Edit client information', description_ar: 'تعديل معلومات العميل' },
            { key: 'client:delete', description_en: 'Delete clients', description_ar: 'حذف العملاء' },
        ]
    },
    {
        groupKey: 'document_management',
        permissions: [
            { key: 'document:create', description_en: 'Create/Upload documents', description_ar: 'إنشاء/تحميل المستندات' },
            { key: 'document:view', description_en: 'View documents', description_ar: 'عرض المستندات' },
            { key: 'document:edit', description_en: 'Edit document details', description_ar: 'تعديل تفاصيل المستند' },
            { key: 'document:delete', description_en: 'Delete documents', description_ar: 'حذف المستندات' },
        ]
    },
    {
        groupKey: 'system_administration',
        permissions: [
            { key: 'user:manage', description_en: 'Manage users and their assignments', description_ar: 'إدارة المستخدمين وتعييناتهم' },
            { key: 'roles:manage', description_en: 'Manage roles and permissions', description_ar: 'إدارة الأدوار والصلاحيات' },
        ]
    }
];

export const dbUsers: Omit<User, 'role'>[] = [
    { id: 1, name_en: 'Super Admin', name_ar: 'المدير العام', email: 'admin@clms.com', role_id: 1, is_active: true },
    { id: 2, name_en: 'Alice Partner', name_ar: 'أليس شريك', email: 'alice.p@clms.com', role_id: 2, is_active: true },
    { id: 3, name_en: 'Bob Associate', name_ar: 'بوب محامي', email: 'bob.a@clms.com', role_id: 3, is_active: true },
    { id: 4, name_en: 'Charlie Paralegal', name_ar: 'تشارلي مساعد', email: 'charlie.p@clms.com', role_id: 4, is_active: false },
];
