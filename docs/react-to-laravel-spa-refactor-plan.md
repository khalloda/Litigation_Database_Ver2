# React to Laravel SPA Refactoring Plan

## Diagnostic Summary

### Current Architecture

**Routing System:**

- Custom state-based navigation in `App.tsx` using `ViewState` type and `history` array
- Navigation functions: `navigateTo()`, `goBack()`, `navigateRoot()`
- 25+ view states mapped to different pages/components
- No URL-based routing (all client-side state)

**Data Layer:**

- Heavy reliance on `services/mockData.ts` (exports `mockCases`, `mockClients`, `mockTasks`, etc.)
- `services/database.ts` contains static data arrays (`dbCases`, `dbClients`, `dbLawyers`, etc.)
- Mock data imported directly in 15+ components/pages
- Helper functions: `getClientById()`, `getCaseById()`, etc. query mock arrays

**i18n System:**

- Custom `I18nContext` provider in `context/I18nContext.tsx`
- Fetches JSON files from `/locales/{lang}.json` (en.json, ar.json)
- Already SPA-compatible (works with static files)
- RTL support via `dir` attribute on document element

**AI Integration:**

- `services/geminiService.ts` makes direct calls to Google Gemini API
- Uses `@google/genai` package
- API key exposed via `process.env.GEMINI_API_KEY` (security risk)
- Two functions: `generateCaseSummary()` and `analyzeDocument()`
- Used in `CaseCard.tsx` and `DocumentAnalyzer.tsx`

**File Uploads:**

- `UploadDocumentPage.tsx` has form but likely just calls `onSave()` callback
- No actual file upload implementation visible

**Components Structure:**

- 25 page components in `pages/`
- 17 reusable components in `components/`
- Layout component handles sidebar navigation

---

## Step 1: Install React Router

**Action:**

```bash
npm install react-router-dom
npm install --save-dev @types/react-router-dom  # if using TypeScript
```

**Files to modify:**

- `package.json` (adds dependency)

---

## Step 2: Refactor App.tsx to Use React Router

**File:** `App.tsx`

**Changes:**

- Remove `ViewState` type and `history` state management
- Replace `AppContent` component with React Router setup
- Convert all `navigateTo()` calls to React Router navigation
- Map all 25+ view states to URL routes

**New structure:**

```tsx
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { I18nProvider } from './context/I18nContext';
import Layout from './components/Layout';
// ... import all pages

export default function App() {
  return (
    <BrowserRouter>
      <I18nProvider>
        <Routes>
          <Route path="/" element={<Layout />}>
            <Route index element={<DashboardPage />} />
            <Route path="cases" element={<CasesListPage />} />
            <Route path="cases/:id" element={<CaseDetailPage />} />
            <Route path="cases/create" element={<NewCaseForm />} />
            <Route path="clients" element={<ClientsListPage />} />
            <Route path="clients/:id" element={<ClientDetailPage />} />
            <Route path="clients/create" element={<NewClientForm />} />
            <Route path="opponents" element={<OpponentsListPage />} />
            <Route path="opponents/:id" element={<OpponentDetailPage />} />
            <Route path="lawyers" element={<LawyersListPage />} />
            <Route path="lawyers/:id" element={<LawyerDetailPage />} />
            <Route path="courts" element={<CourtsListPage />} />
            <Route path="courts/:id" element={<CourtDetailPage />} />
            <Route path="hearings" element={<HearingsListPage />} />
            <Route path="hearings/create" element={<NewHearingForm />} />
            <Route path="hearings/:id" element={<HearingDetailPage />} />
            <Route path="documents" element={<DocumentsListPage />} />
            <Route path="documents/create" element={<UploadDocumentPage />} />
            <Route path="documents/:id" element={<DocumentDetailPage />} />
            <Route path="documents/:id/edit" element={<UploadDocumentPage />} />
            <Route path="tasks" element={<TasksPage />} />
            <Route path="reports" element={<ReportsPage />} />
            <Route path="settings" element={<SettingsPage />} />
            <Route path="settings/roles" element={<RolesListPage />} />
            <Route path="settings/roles/:id" element={<RoleDetailPage />} />
            <Route path="settings/teams" element={<TeamsListPage />} />
            <Route path="settings/teams/:id" element={<TeamDetailPage />} />
            <Route path="settings/users" element={<UsersListPage />} />
            <Route path="settings/users/:id" element={<UserDetailPage />} />
            <Route path="settings/users/new" element={<UserDetailPage />} />
            <Route path="*" element={<Navigate to="/" replace />} />
          </Route>
        </Routes>
      </I18nProvider>
    </BrowserRouter>
  );
}
```

**Files affected:**

- `App.tsx` (complete rewrite)
- Remove `ViewState` type export (or keep for reference during migration)

---

## Step 3: Update Layout Component for React Router

**File:** `components/Layout.tsx`

**Changes:**

- Remove `currentView` prop (use `useLocation()` hook instead)
- Remove `onNavigate` prop (use `useNavigate()` hook)
- Update `Sidebar` to use `Link` components or `useNavigate()` for navigation
- Use `useLocation()` to determine active nav item

**New structure:**

```tsx
import { useLocation, useNavigate, Outlet } from 'react-router-dom';

const Layout: React.FC = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const { direction } = useI18n();
  
  const currentView = location.pathname.split('/')[1] || 'dashboard';
  
  return (
    <div className={`flex h-screen bg-gray-50 font-sans ${direction}`}>
      <Sidebar currentView={currentView} onNavigate={navigate} />
      <div className="flex-1 flex flex-col overflow-hidden">
        <Header />
        <main className="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
          <Outlet /> {/* React Router outlet for nested routes */}
        </main>
      </div>
    </div>
  );
};
```

**Files affected:**

- `components/Layout.tsx`

---

## Step 4: Create Central API Service Layer

**New file:** `services/api.ts`

**Content:**

```typescript
import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  withCredentials: true, // For Laravel Sanctum session cookies
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor for auth tokens (if using token-based auth)
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Response interceptor for error handling
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Handle unauthorized - redirect to login
      localStorage.removeItem('auth_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;
```

**New service files to create:**

1. **`services/auth.ts`** - Authentication endpoints
2. **`services/cases.ts`** - Case CRUD operations
3. **`services/clients.ts`** - Client CRUD operations
4. **`services/opponents.ts`** - Opponent operations
5. **`services/lawyers.ts`** - Lawyer operations
6. **`services/courts.ts`** - Court operations
7. **`services/hearings.ts`** - Hearing CRUD operations
8. **`services/documents.ts`** - Document operations (including uploads)
9. **`services/tasks.ts`** - Task operations
10. **`services/users.ts`** - User management
11. **`services/roles.ts`** - Role management
12. **`services/teams.ts`** - Team management
13. **`services/ai.ts`** - AI/Gemini proxy endpoints
14. **`services/options.ts`** - Option sets/values (for dropdowns)

**Example service file structure (`services/cases.ts`):**

```typescript
import api from './api';
import type { Case } from '../types';

export async function fetchCases(params?: {
  status?: string;
  client_id?: number;
  partner_id?: number;
  opponent_id?: number;
  search?: string;
}) {
  const response = await api.get('/cases', { params });
  return response.data;
}

export async function fetchCase(id: number | string) {
  const response = await api.get(`/cases/${id}`);
  return response.data;
}

export async function createCase(payload: Partial<Case>) {
  const response = await api.post('/cases', payload);
  return response.data;
}

export async function updateCase(id: number | string, payload: Partial<Case>) {
  const response = await api.put(`/cases/${id}`, payload);
  return response.data;
}

export async function deleteCase(id: number | string) {
  const response = await api.delete(`/cases/${id}`);
  return response.data;
}
```

**Files to create:**

- `services/api.ts` (new)
- `services/auth.ts` (new)
- `services/cases.ts` (new)
- `services/clients.ts` (new)
- `services/opponents.ts` (new)
- `services/lawyers.ts` (new)
- `services/courts.ts` (new)
- `services/hearings.ts` (new)
- `services/documents.ts` (new)
- `services/tasks.ts` (new)
- `services/users.ts` (new)
- `services/roles.ts` (new)
- `services/teams.ts` (new)
- `services/ai.ts` (new)
- `services/options.ts` (new)

---

## Step 5: Replace Mock Data with API Calls

**Components to update (replace mock imports with API hooks):**

1. **`pages/DashboardPage.tsx`**

   - Replace `mockCases`, `mockClients`, `mockOpponents` imports
   - Add `useEffect` to fetch cases on mount
   - Add loading/error states

2. **`pages/CaseDetailPage.tsx`**

   - Replace `caseData` prop with `useParams()` + API call
   - Fetch case by ID from route params

3. **`pages/ClientsListPage.tsx`**

   - Replace mock clients with API fetch

4. **`pages/ClientDetailPage.tsx`**

   - Replace `client` prop with API fetch by ID

5. **`pages/OpponentsListPage.tsx`**

   - Replace mock opponents with API fetch

6. **`pages/OpponentDetailPage.tsx`**

   - Replace mock data with API fetch

7. **`pages/LawyersListPage.tsx`**

   - Replace mock lawyers with API fetch

8. **`pages/LawyerDetailPage.tsx`**

   - Replace mock data with API fetch

9. **`pages/CourtsListPage.tsx`**

   - Replace mock courts with API fetch

10. **`pages/CourtDetailPage.tsx`**

    - Replace mock data with API fetch

11. **`pages/HearingsListPage.tsx`**

    - Replace `mockCases`, `mockClients` with API calls

12. **`pages/HearingDetailPage.tsx`**

    - Replace mock data with API fetch

13. **`pages/DocumentsListPage.tsx`**

    - Replace `mockCases`, `mockClients` with API calls

14. **`pages/DocumentDetailPage.tsx`**

    - Replace mock data with API fetch

15. **`pages/TasksPage.tsx`**

    - Replace `mockTasks`, `mockCases` with API calls

16. **`pages/ReportsPage.tsx`**

    - Replace `mockCases`, `mockClients` with API calls

17. **`pages/UsersListPage.tsx`**

    - Replace `getUsers()` with API call

18. **`pages/RolesListPage.tsx`**

    - Replace mock roles with API call

19. **`pages/TeamsListPage.tsx`**

    - Replace mock teams with API call

20. **`components/NewCaseForm.tsx`**

    - Replace mock data dropdowns with API calls
    - Update form submission to call API

21. **`components/NewHearingForm.tsx`**

    - Replace `mockCases` with API call
    - Update form submission to call API

22. **`components/NewTaskForm.tsx`**

    - Replace `mockCases` with API call
    - Update form submission to call API

23. **`pages/UploadDocumentPage.tsx`**

    - Replace `mockCases`, `mockClients` with API calls
    - Implement actual file upload via FormData

**Pattern for updating components:**

```tsx
// Before
import { mockCases } from '../services/mockData';
const [cases] = useState(mockCases);

// After
import { useEffect, useState } from 'react';
import { fetchCases } from '../services/cases';

const [cases, setCases] = useState([]);
const [loading, setLoading] = useState(true);
const [error, setError] = useState(null);

useEffect(() => {
  fetchCases()
    .then((data) => setCases(data))
    .catch((err) => setError(err.message))
    .finally(() => setLoading(false));
}, []);
```

**Files to update:**

- All 25 page components
- Form components that use mock data
- Any other components importing from `mockData.ts` or `database.ts`

**Files that can be deleted after migration:**

- `services/mockData.ts` (keep for reference initially, delete after testing)
- `services/database.ts` (keep for reference initially, delete after testing)

---

## Step 6: Update Page Components to Use React Router Hooks

**Changes needed in all page components:**

1. Replace `onBack()` prop with `useNavigate()` hook
2. Replace `onSelectCase()`, `onSelectClient()`, etc. props with `useNavigate()` calls
3. Replace ID props with `useParams()` hook for route params
4. Remove callback props, use direct navigation

**Example transformation:**

**Before (`CaseDetailPage.tsx`):**

```tsx
interface CaseDetailPageProps {
  caseData: Case;
  onBack: () => void;
  onSelectClient: (id: number) => void;
}

const CaseDetailPage: React.FC<CaseDetailPageProps> = ({ caseData, onBack, onSelectClient }) => {
  // ...
};
```

**After:**

```tsx
import { useParams, useNavigate } from 'react-router-dom';
import { useEffect, useState } from 'react';
import { fetchCase } from '../services/cases';

const CaseDetailPage: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [caseData, setCaseData] = useState<Case | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (id) {
      fetchCase(id)
        .then(setCaseData)
        .finally(() => setLoading(false));
    }
  }, [id]);

  if (loading) return <div>Loading...</div>;
  if (!caseData) return <div>Case not found</div>;

  return (
    // ... JSX with navigate('/clients/' + clientId) instead of onSelectClient
  );
};
```

**Files to update:**

- All page components (25 files)

---

## Step 7: Create Authentication Service & Protected Routes

**New file:** `services/auth.ts`

**Content:**

```typescript
import api from './api';

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  // ... other user fields
}

export async function login(credentials: LoginCredentials) {
  const response = await api.post('/login', credentials);
  // If using token auth:
  if (response.data.token) {
    localStorage.setItem('auth_token', response.data.token);
  }
  return response.data;
}

export async function logout() {
  const response = await api.post('/logout');
  localStorage.removeItem('auth_token');
  return response.data;
}

export async function fetchCurrentUser() {
  const response = await api.get('/user');
  return response.data;
}

export function getAuthToken(): string | null {
  return localStorage.getItem('auth_token');
}
```

**New file:** `components/ProtectedRoute.tsx`

**Content:**

```typescript
import { Navigate, Outlet } from 'react-router-dom';
import { useEffect, useState } from 'react';
import { fetchCurrentUser } from '../services/auth';

export function ProtectedRoute() {
  const [isAuthenticated, setIsAuthenticated] = useState<boolean | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchCurrentUser()
      .then(() => setIsAuthenticated(true))
      .catch(() => setIsAuthenticated(false))
      .finally(() => setLoading(false));
  }, []);

  if (loading) {
    return <div>Loading...</div>;
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  return <Outlet />;
}
```

**Update `App.tsx` to wrap protected routes:**

```tsx
<Route element={<ProtectedRoute />}>
  <Route path="/cases" element={<CasesListPage />} />
  {/* ... all protected routes */}
</Route>
```

**Files to create:**

- `services/auth.ts` (new)
- `components/ProtectedRoute.tsx` (new)

**Files to update:**

- `App.tsx` (wrap routes with ProtectedRoute)

---

## Step 8: Update i18n for SPA (Keep Current System)

**No major changes needed** - current i18n system already works for SPA.

**Optional improvements:**

- Ensure locale files are in `public/locales/` directory (for static serving)
- Consider adding API endpoint fallback: `/api/translations/{lang}` (for future Laravel integration)

**Files to check:**

- `context/I18nContext.tsx` (already SPA-compatible)
- `hooks/useI18n.ts` (verify it works)
- Ensure `locales/en.json` and `locales/ar.json` are accessible

---

## Step 9: Implement File Upload Service

**Update file:** `services/documents.ts`

**Add upload function:**

```typescript
import api from './api';

export async function uploadDocument(payload: {
  file: File;
  client_id?: number;
  matter_id?: number;
  document_name?: string;
  document_type?: string;
  // ... other fields
}) {
  const formData = new FormData();
  formData.append('file', payload.file);
  if (payload.client_id) formData.append('client_id', String(payload.client_id));
  if (payload.matter_id) formData.append('matter_id', String(payload.matter_id));
  // ... append other fields

  const response = await api.post('/documents', formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });
  return response.data;
}

export async function updateDocument(id: number | string, payload: FormData) {
  const response = await api.post(`/documents/${id}`, payload, {
    headers: { 'Content-Type': 'multipart/form-data' },
  });
  return response.data;
}
```

**Update file:** `pages/UploadDocumentPage.tsx`

**Changes:**

- Replace `onSave()` callback with actual API call
- Use `uploadDocument()` service function
- Add file input handling
- Show upload progress
- Navigate after successful upload

**Files to update:**

- `services/documents.ts` (add upload functions)
- `pages/UploadDocumentPage.tsx` (implement actual upload)

---

## Step 10: Move Gemini AI to Backend Proxy

**Update file:** `services/ai.ts` (new)

**Content:**

```typescript
import api from './api';
import type { DocumentAnalysisResult, Language } from '../types';

export async function generateCaseSummary(
  caseId: number | string,
  language: Language
): Promise<string> {
  const response = await api.post(`/ai/case-summary`, {
    case_id: caseId,
    language,
  });
  return response.data.summary;
}

export async function analyzeDocument(
  documentText: string,
  language: Language
): Promise<DocumentAnalysisResult> {
  const response = await api.post('/ai/analyze-document', {
    text: documentText,
    language,
  });
  return response.data;
}
```

**Update files:**

- `components/CaseCard.tsx` - Replace `generateCaseSummary()` import
- `components/DocumentAnalyzer.tsx` - Replace `analyzeDocument()` import

**Remove:**

- `services/geminiService.ts` (delete after migration)
- Remove `@google/genai` from `package.json` dependencies

**Files to create:**

- `services/ai.ts` (new)

**Files to update:**

- `components/CaseCard.tsx`
- `components/DocumentAnalyzer.tsx`
- `package.json` (remove @google/genai)

---

## Step 11: Update Vite Config for Laravel Deployment

**Update file:** `vite.config.ts`

**Changes:**

```typescript
import path from 'path';
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, '.'),
    },
  },
  build: {
    outDir: 'dist', // Default - will copy to Laravel public/ during deployment
    emptyOutDir: true,
    // Ensure relative paths for assets
    assetsDir: 'assets',
    rollupOptions: {
      output: {
        manualChunks: undefined, // Or configure code splitting
      },
    },
  },
  // Remove Gemini API key from frontend
  // define: {
  //   'process.env.API_KEY': ... // REMOVE THIS
  // },
});
```

**Deployment note:**

- Build command: `npm run build`
- Copy `dist/*` contents to Laravel `public/` directory
- Ensure Laravel routes handle SPA fallback (serve `index.html` for non-API routes)

**Files to update:**

- `vite.config.ts`

---

## Step 12: Install Axios (if not present)

**Action:**

```bash
npm install axios
```

**Files to update:**

- `package.json`

---

## Laravel Backend Assumptions

### Expected API Endpoints

**Authentication:**

- `POST /api/login` - Login (returns token or sets session)
- `POST /api/logout` - Logout
- `GET /api/user` - Get current authenticated user

**Resources (RESTful):**

- `GET /api/cases` - List cases (with query params for filtering)
- `GET /api/cases/{id}` - Get case details
- `POST /api/cases` - Create case
- `PUT /api/cases/{id}` - Update case
- `DELETE /api/cases/{id}` - Delete case

Similar patterns for:

- `/api/clients`
- `/api/opponents`
- `/api/lawyers`
- `/api/courts`
- `/api/hearings`
- `/api/documents` (with file upload support)
- `/api/tasks`
- `/api/users`
- `/api/roles`
- `/api/teams`

**Options/Reference Data:**

- `GET /api/options/{setKey}` - Get option values by set key

**AI Endpoints:**

- `POST /api/ai/case-summary` - Generate case summary
- `POST /api/ai/analyze-document` - Analyze document text

### Laravel Route Configuration

**Expected `routes/api.php`:**

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('cases', CaseController::class);
    Route::apiResource('clients', ClientController::class);
    // ... all resources
    Route::post('/ai/case-summary', [AIController::class, 'generateCaseSummary']);
    Route::post('/ai/analyze-document', [AIController::class, 'analyzeDocument']);
});
```

**Expected `routes/web.php` (SPA fallback):**

```php
// API routes first
Route::prefix('api')->group(function () {
    require __DIR__.'/api.php';
});

// SPA fallback - serve React index.html for all non-API routes
Route::get('/{any}', function () {
    return file_get_contents(public_path('index.html'));
})->where('any', '.*');
```

### Authentication Method

**Option A: Laravel Sanctum (Token-based)**

- Frontend stores token in localStorage
- Token sent in Authorization header
- Backend validates token on each request

**Option B: Laravel Sanctum (Session-based)**

- Uses cookies (requires `withCredentials: true`)
- No token storage needed
- Simpler for same-domain setup

**Recommendation:** Use Session-based Sanctum for same-domain deployment (simpler, more secure).

---

## Checklist

### Phase 1: Setup & Routing

- [ ] Install react-router-dom
- [ ] Refactor App.tsx to use React Router
- [ ] Update Layout.tsx for React Router
- [ ] Update all page components to use useParams/useNavigate
- [ ] Test all routes work correctly

### Phase 2: API Layer

- [ ] Create services/api.ts
- [ ] Create all domain service files (cases, clients, etc.)
- [ ] Create services/auth.ts
- [ ] Create services/ai.ts
- [ ] Install axios

### Phase 3: Replace Mock Data

- [ ] Update DashboardPage.tsx
- [ ] Update CaseDetailPage.tsx
- [ ] Update ClientsListPage.tsx
- [ ] Update ClientDetailPage.tsx
- [ ] Update all other list/detail pages (20+ files)
- [ ] Update form components
- [ ] Remove mock data imports

### Phase 4: Authentication

- [ ] Create ProtectedRoute component
- [ ] Wrap protected routes in App.tsx
- [ ] Update auth service for Laravel endpoints
- [ ] Test authentication flow

### Phase 5: File Uploads & AI

- [ ] Implement file upload in documents service
- [ ] Update UploadDocumentPage.tsx
- [ ] Replace Gemini calls with AI service
- [ ] Remove @google/genai dependency

### Phase 6: Build & Deploy Prep

- [ ] Update vite.config.ts
- [ ] Test build output
- [ ] Document deployment process
- [ ] Create .env.example for API base URL

### Phase 7: Cleanup

- [ ] Delete services/mockData.ts
- [ ] Delete services/database.ts
- [ ] Delete services/geminiService.ts
- [ ] Remove unused imports
- [ ] Update TypeScript types if needed

---

## Estimated Effort

- **Step 1-3 (Routing):** 4-6 hours
- **Step 4 (API Layer):** 3-4 hours
- **Step 5 (Replace Mock Data):** 12-16 hours
- **Step 6 (Page Updates):** 8-10 hours
- **Step 7 (Auth):** 2-3 hours
- **Step 8 (i18n):** 1 hour (minimal changes)
- **Step 9 (File Uploads):** 2-3 hours
- **Step 10 (AI Migration):** 1-2 hours
- **Step 11 (Vite Config):** 1 hour
- **Step 12 (Axios):** 15 minutes

**Total:** ~35-45 hours of development work

---

## Notes

1. Keep mock data files during migration for reference, delete after testing
2. Test each component after updating to ensure API integration works
3. Laravel backend must be implemented in parallel or before frontend deployment
4. Consider adding error boundaries for better error handling
5. Add loading states to all components that fetch data
6. Consider adding React Query or SWR for better data fetching/caching
7. Ensure CORS is configured if frontend and backend are on different domains (not needed for same-domain setup)

