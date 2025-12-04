import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { I18nProvider } from './context/I18nContext';
import Layout from './components/Layout';
import { ProtectedRoute } from './components/ProtectedRoute';
import DashboardPage from './pages/DashboardPage';
import CasesListPage from './pages/CasesListPage';
import CaseDetailPage from './pages/CaseDetailPage';
import ClientDetailPage from './pages/ClientDetailPage';
import OpponentDetailPage from './pages/OpponentDetailPage';
import ClientsListPage from './pages/ClientsListPage';
import OpponentsListPage from './pages/OpponentsListPage';
import TasksPage from './pages/TasksPage';
import SettingsPage from './pages/SettingsPage';
import LawyersListPage from './pages/LawyersListPage';
import LawyerDetailPage from './pages/LawyerDetailPage';
import CourtsListPage from './pages/CourtsListPage';
import CourtDetailPage from './pages/CourtDetailPage';
import HearingsListPage from './pages/HearingsListPage';
import HearingDetailPage from './pages/HearingDetailPage';
import DocumentsListPage from './pages/DocumentsListPage';
import DocumentDetailPage from './pages/DocumentDetailPage';
import ReportsPage from './pages/ReportsPage';
import UploadDocumentPage from './pages/UploadDocumentPage';
import RolesListPage from './pages/RolesListPage';
import RoleDetailPage from './pages/RoleDetailPage';
import TeamsListPage from './pages/TeamsListPage';
import TeamDetailPage from './pages/TeamDetailPage';
import UsersListPage from './pages/UsersListPage';
import UserDetailPage from './pages/UserDetailPage';
import LoginPage from './pages/LoginPage';
import PowerOfAttorneyListPage from './pages/PowerOfAttorneyListPage';
import PowerOfAttorneyDetailPage from './pages/PowerOfAttorneyDetailPage';

const App: React.FC = () => {
  return (
    <BrowserRouter>
      <I18nProvider>
        <Routes>
          <Route path="/login" element={<LoginPage />} />
          <Route path="/" element={<ProtectedRoute />}>
            <Route element={<Layout />}>
              <Route index element={<DashboardPage />} />
              <Route path="cases" element={<CasesListPage />} />
              <Route path="cases/:id" element={<CaseDetailPage />} />
              <Route path="clients" element={<ClientsListPage />} />
              <Route path="clients/:id" element={<ClientDetailPage />} />
              <Route path="opponents" element={<OpponentsListPage />} />
              <Route path="opponents/:id" element={<OpponentDetailPage />} />
              <Route path="lawyers" element={<LawyersListPage />} />
              <Route path="lawyers/:id" element={<LawyerDetailPage />} />
              <Route path="courts" element={<CourtsListPage />} />
              <Route path="courts/:id" element={<CourtDetailPage />} />
              <Route path="hearings" element={<HearingsListPage />} />
              <Route path="hearings/:id" element={<HearingDetailPage />} />
              <Route path="documents" element={<DocumentsListPage />} />
              <Route path="documents/create" element={<UploadDocumentPage />} />
              <Route path="documents/:id" element={<DocumentDetailPage />} />
              <Route path="documents/:id/edit" element={<UploadDocumentPage />} />
              <Route path="power-of-attorneys" element={<PowerOfAttorneyListPage />} />
              <Route path="power-of-attorneys/:id" element={<PowerOfAttorneyDetailPage />} />
              <Route path="tasks" element={<TasksPage />} />
              <Route path="tasks/:id" element={<TasksPage />} />
              <Route path="reports" element={<ReportsPage />} />
              <Route path="settings" element={<SettingsPage />} />
              <Route path="settings/roles" element={<RolesListPage />} />
              <Route path="settings/roles/:id" element={<RoleDetailPage />} />
              <Route path="settings/teams" element={<TeamsListPage />} />
              <Route path="settings/teams/:id" element={<TeamDetailPage />} />
              <Route path="settings/teams/new" element={<TeamDetailPage />} />
              <Route path="settings/users" element={<UsersListPage />} />
              <Route path="settings/users/:id" element={<UserDetailPage />} />
              <Route path="settings/users/new" element={<UserDetailPage />} />
              <Route path="*" element={<Navigate to="/" replace />} />
            </Route>
          </Route>
        </Routes>
      </I18nProvider>
    </BrowserRouter>
  );
}

export default App;
