# React-to-Laravel API Integration - Implementation Plan

## Goal
Replace mock data in React frontend with real Laravel API endpoints to enable production functionality.

## User Review Required

> [!IMPORTANT]
> **Breaking Changes**:
> - `services/database.ts` will be deleted and replaced with API calls
> - All components must be updated to handle async data loading
> - Authentication will be required for all protected routes
> - Error handling must be implemented throughout the application

> [!WARNING]
> **Configuration Required**:
> - Laravel API base URL must be configured (`.env.local`)
> - CORS must be enabled on Laravel backend
> - Sanctum authentication must be properly configured

## Proposed Changes

### Phase 1: API Client Infrastructure

#### [NEW] [apiClient.ts](file:///d:/Claude/Litigation_Database_Ver2/Litigation_Database_Ver2/AiStudio-CLMS2/services/apiClient.ts)
Centralized Axios client with:
- Base URL configuration from environment variables
- Request/response interceptors for auth tokens
- Global error handling
- Request/response type safety
- Automatic token refresh logic

**Key Features**:
```typescript
- axios.create() with baseURL from env
- Request interceptor: Add Bearer token from localStorage
- Response interceptor: Handle 401 (redirect to login), 403, 500 errors
- Retry logic for network failures
- TypeScript generics for type-safe responses
```

---

### Phase 2: Service Layer Refactoring

#### [MODIFY] [services/cases.ts](file:///d:/Claude/Litigation_Database_Ver2/Litigation_Database_Ver2/AiStudio-CLMS2/services/cases.ts)
Replace mock data with API calls:
```typescript
// Before: import { dbCases } from './database'
// After: import { apiClient } from './apiClient'

export const fetchCases = () => apiClient.get<Case[]>('/api/cases')
export const fetchCase = (id: number) => apiClient.get<Case>(`/api/cases/${id}`)
export const createCase = (data: Partial<Case>) => apiClient.post<Case>('/api/cases', data)
export const updateCase = (id: number, data: Partial<Case>) => apiClient.put<Case>(`/api/cases/${id}`, data)
export const deleteCase = (id: number) => apiClient.delete(`/api/cases/${id}`)
```

#### [MODIFY] [services/clients.ts](file:///d:/Claude/Litigation_Database_Ver2/Litigation_Database_Ver2/AiStudio-CLMS2/services/clients.ts)
#### [MODIFY] [services/documents.ts](file:///d:/Claude/Litigation_Database_Ver2/Litigation_Database_Ver2/AiStudio-CLMS2/services/documents.ts)
#### [MODIFY] [services/hearings.ts](file:///d:/Claude/Litigation_Database_Ver2/Litigation_Database_Ver2/AiStudio-CLMS2/services/hearings.ts)
#### [MODIFY] [services/lawyers.ts](file:///d:/Claude/Litigation_Database_Ver2/Litigation_Database_Ver2/AiStudio-CLMS2/services/lawyers.ts)
#### [MODIFY] [services/courts.ts](file:///d:/Claude/Litigation_Database_Ver2/Litigation_Database_Ver2/AiStudio-CLMS2/services/courts.ts)
#### [MODIFY] [services/opponents.ts](file:///d:/Claude/Litigation_Database_Ver2/Litigation_Database_Ver2/AiStudio-CLMS2/services/opponents.ts)

Same pattern as cases.ts - replace mock imports with API calls.

---

### Phase 3: Authentication Service

#### [NEW] [services/auth.ts](file:///d:/Claude/Litigation_Database_Ver2/Litigation_Database_Ver2/AiStudio-CLMS2/services/auth.ts)
Handle authentication flow:
```typescript
export const login = (email: string, password: string) => 
  apiClient.post('/api/login', { email, password })

export const logout = () => apiClient.post('/api/logout')

export const getCurrentUser = () => apiClient.get('/api/user')

export const isAuthenticated = () => !!localStorage.getItem('auth_token')
```

#### [NEW] [contexts/AuthContext.tsx](file:///d:/Claude/Litigation_Database_Ver2/Litigation_Database_Ver2/AiStudio-CLMS2/contexts/AuthContext.tsx)
React context for auth state management:
- Store user object and token
- Provide login/logout functions
- Handle token persistence
- Provide loading states

---

### Phase 4: Error Handling

#### [NEW] [components/ErrorBoundary.tsx](file:///d:/Claude/Litigation_Database_Ver2/Litigation_Database_Ver2/AiStudio-CLMS2/components/ErrorBoundary.tsx)
React error boundary for catching render errors.

#### [NEW] [hooks/useApiError.ts](file:///d:/Claude/Litigation_Database_Ver2/Litigation_Database_Ver2/AiStudio-CLMS2/hooks/useApiError.ts)
Custom hook for handling API errors:
```typescript
- Display toast notifications for errors
- Log errors to console in development
- Format error messages for user display
- Handle validation errors (422)
```

---

### Phase 5: Environment Configuration

#### [NEW] [.env.local](file:///d:/Claude/Litigation_Database_Ver2/Litigation_Database_Ver2/AiStudio-CLMS2/.env.local)
```env
VITE_API_BASE_URL=http://litigation.local/api
VITE_APP_NAME=CLMS
```

#### [MODIFY] [vite.config.ts](file:///d:/Claude/Litigation_Database_Ver2/Litigation_Database_Ver2/AiStudio-CLMS2/vite.config.ts)
Add proxy configuration for development:
```typescript
server: {
  proxy: {
    '/api': {
      target: 'http://litigation.local',
      changeOrigin: true,
    }
  }
}
```

---

### Phase 6: Laravel Backend Updates

#### [MODIFY] [clm-app/config/cors.php](file:///d:/Claude/Litigation_Database_Ver2/Litigation_Database_Ver2/clm-app/config/cors.php)
Enable CORS for React frontend:
```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
'supports_credentials' => true,
```

#### [MODIFY] [clm-app/.env](file:///d:/Claude/Litigation_Database_Ver2/Litigation_Database_Ver2/clm-app/.env)
Add frontend URL:
```env
FRONTEND_URL=http://localhost:5173
SESSION_DRIVER=cookie
SANCTUM_STATEFUL_DOMAINS=localhost:5173,litigation.local
```

---

### Phase 7: Component Updates

#### [MODIFY] All Page Components
Update to use async data fetching:
```typescript
// Before:
const [cases, setCases] = useState(dbCases)

// After:
const [cases, setCases] = useState([])
const [loading, setLoading] = useState(true)
const [error, setError] = useState(null)

useEffect(() => {
  fetchCases()
    .then(data => setCases(data))
    .catch(err => setError(err))
    .finally(() => setLoading(false))
}, [])
```

---

### Phase 8: Data Cleanup

#### [DELETE] [services/database.ts](file:///d:/Claude/Litigation_Database_Ver2/Litigation_Database_Ver2/AiStudio-CLMS2/services/database.ts)
Remove mock data file entirely.

---

## Verification Plan

### Automated Tests

#### Backend Tests (Laravel/Pest)
```bash
# Test all API endpoints
php artisan test --filter=Api

# Specific controller tests
php artisan test --filter=CaseControllerTest
php artisan test --filter=ClientControllerTest
php artisan test --filter=DocumentControllerTest
```

#### Frontend Tests (Vitest)
```bash
# Test API client
npm run test -- apiClient.test.ts

# Test service layer
npm run test -- services/

# Test auth context
npm run test -- AuthContext.test.tsx
```

### Manual Verification

1. **Authentication Flow**:
   - Login with valid credentials → Success
   - Login with invalid credentials → Error message
   - Access protected route without token → Redirect to login
   - Logout → Clear token and redirect

2. **Data Fetching**:
   - Load cases list → Display real data from database
   - Load case detail → Display correct case information
   - Create new case → Save to database and update UI
   - Update case → Persist changes to database
   - Delete case → Remove from database and UI

3. **Error Handling**:
   - Network error → Display user-friendly message
   - 401 Unauthorized → Redirect to login
   - 403 Forbidden → Display permission error
   - 422 Validation Error → Display field-specific errors
   - 500 Server Error → Display generic error message

4. **Performance**:
   - Initial page load < 2 seconds
   - API response time < 500ms
   - No unnecessary re-renders
   - Proper loading states displayed

---

## Implementation Order

1. ✅ Create `apiClient.ts` with interceptors
2. ✅ Create `auth.ts` service
3. ✅ Create `AuthContext` and update App.tsx
4. ✅ Configure environment variables
5. ✅ Update Laravel CORS configuration
6. ✅ Refactor one service file (cases.ts) as proof of concept
7. ✅ Update one page component (CasesListPage) to test integration
8. ✅ Test authentication and data fetching
9. ✅ Refactor remaining service files
10. ✅ Update all page components
11. ✅ Add error handling throughout
12. ✅ Delete database.ts
13. ✅ Run full test suite
14. ✅ Manual QA testing

---

## Rollback Plan

If issues arise:
1. Revert to previous commit (mock data still available)
2. Keep `database.ts` as backup until full verification
3. Feature flag to toggle between mock/real API

---

## Success Criteria

- ✅ All API endpoints return correct data
- ✅ Authentication works end-to-end
- ✅ Error handling displays user-friendly messages
- ✅ No console errors in browser
- ✅ All CRUD operations work correctly
- ✅ Performance meets targets (<2s page load)
- ✅ Test coverage ≥70%
