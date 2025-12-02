
import React, { useEffect, useState } from 'react';
import { useLocation, useNavigate, Outlet } from 'react-router-dom';
import { useI18n } from '../hooks/useI18n';
import { fetchCurrentUser, logout as apiLogout } from '../services/auth';
import { CaseIcon, ReportIcon, SettingsIcon, TaskIcon, LanguageIcon, ClientIcon, OpponentIcon, UserIcon, CourtIcon, CalendarIcon, DocumentIcon, SparklesIcon } from './icons';
import type { Language } from '../types';

interface NavItemProps {
  icon: React.ReactNode;
  label: string;
  active?: boolean;
  onClick: () => void;
}

const NavItem: React.FC<NavItemProps> = ({ icon, label, active, onClick }) => (
  <a 
    href="#" 
    onClick={(e) => { e.preventDefault(); onClick(); }}
    className={`flex items-center p-3 rounded-lg transition-colors ${active ? 'bg-primary-500 text-white' : 'text-gray-600 hover:bg-primary-100 hover:text-primary-800'}`}
  >
    {icon}
    <span className="ms-4 font-medium">{label}</span>
  </a>
);

interface SidebarProps {
    currentView: string;
    onNavigate: (path: string) => void;
}

const sidebarLogo = '/assets/logo-BU5yR0AT.png';

const Sidebar: React.FC<SidebarProps> = ({ currentView, onNavigate }) => {
    const { t } = useI18n();
    return (
        <aside className="w-64 bg-white border-e shadow-sm flex-shrink-0 flex flex-col p-4">
            <div className="px-3 py-4 flex flex-col items-center">
                <img
                  src={sidebarLogo}
                  alt="CLMS Logo"
                  className="w-full max-w-[160px] object-contain mb-3"
                />
                <span className="text-xl font-bold text-gray-800 text-center">
                  {t('app.title')}
                </span>
            </div>
            <nav className="mt-8 flex flex-col gap-2">
                <NavItem icon={<SparklesIcon />} label={t('app.dashboard')} active={currentView === ''} onClick={() => onNavigate('/')} />
                <NavItem icon={<CaseIcon />} label={t('app.cases')} active={currentView === 'cases'} onClick={() => onNavigate('/cases')} />
                <NavItem icon={<ClientIcon />} label={t('app.clients')} active={currentView === 'clients'} onClick={() => onNavigate('/clients')} />
                <NavItem icon={<OpponentIcon />} label={t('app.opponents')} active={currentView === 'opponents'} onClick={() => onNavigate('/opponents')} />
                <NavItem icon={<UserIcon />} label={t('app.lawyers')} active={currentView === 'lawyers'} onClick={() => onNavigate('/lawyers')} />
                <NavItem icon={<CourtIcon />} label={t('app.courts')} active={currentView === 'courts'} onClick={() => onNavigate('/courts')} />
                <NavItem icon={<CalendarIcon />} label={t('app.hearings')} active={currentView === 'hearings'} onClick={() => onNavigate('/hearings')} />
                <NavItem icon={<DocumentIcon />} label={t('app.documents')} active={currentView === 'documents'} onClick={() => onNavigate('/documents')} />
                <NavItem icon={<DocumentIcon />} label={t('app.power_of_attorneys')} active={currentView === 'power-of-attorneys'} onClick={() => onNavigate('/power-of-attorneys')} />
                <NavItem icon={<TaskIcon />} label={t('app.tasks')} active={currentView === 'tasks'} onClick={() => onNavigate('/tasks')} />
                <NavItem icon={<ReportIcon />} label={t('app.reports')} active={currentView === 'reports'} onClick={() => onNavigate('/reports')} />
            </nav>
            <div className="mt-auto">
                <NavItem icon={<SettingsIcon />} label={t('app.settings')} active={['settings', 'roles', 'teams', 'users'].includes(currentView)} onClick={() => onNavigate('/settings')} />
            </div>
        </aside>
    );
};


const Header: React.FC = () => {
    const { t, language, setLanguage } = useI18n();
    const navigate = useNavigate();
    const [currentUser, setCurrentUser] = useState<{ id: number; name: string; email: string } | null>(null);
    const [menuOpen, setMenuOpen] = useState(false);

    useEffect(() => {
        fetchCurrentUser()
            .then((data) => {
                const user = data?.user || data;
                if (user) {
                    setCurrentUser({ id: user.id, name: user.name, email: user.email });
                }
            })
            .catch(() => {
                // If fetching current user fails, ProtectedRoute will eventually redirect to login
                setCurrentUser(null);
            });
    }, []);

    const handleLanguageChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
        setLanguage(e.target.value as Language);
    };

    const handleLogout = async () => {
        try {
            await apiLogout();
        } catch (err) {
            console.error('Logout failed', err);
        } finally {
            navigate('/login', { replace: true });
        }
    };

    return (
        <header className="bg-white p-4 border-b flex justify-between items-center relative">
             <div className="relative w-full max-w-md">
                <div className="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg className="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                        <path stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                    </svg>
                </div>
                <input
                    type="search"
                    className="block w-full p-2.5 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-primary-500 focus:border-primary-500"
                    placeholder={t('app.search_placeholder')}
                />
            </div>
            <div className="flex items-center gap-4">
                 <div className="relative">
                    <label htmlFor="language-select" className="sr-only">{t('app.language')}</label>
                    <div className="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                        <LanguageIcon className="w-5 h-5 text-gray-500"/>
                    </div>
                    <select
                        id="language-select"
                        value={language}
                        onChange={handleLanguageChange}
                        className="block w-full p-2.5 ps-10 pe-8 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-primary-500 focus:border-primary-500 appearance-none"
                    >
                        <option value="en">English</option>
                        <option value="ar">العربية</option>
                    </select>
                </div>
                <div className="relative">
                    <button
                        type="button"
                        className="flex items-center gap-2 focus:outline-none"
                        onClick={() => setMenuOpen((open) => !open)}
                    >
                        <img
                            className="w-10 h-10 rounded-full border border-gray-300"
                            src="https://picsum.photos/100"
                            alt={currentUser?.name || 'User'}
                        />
                    </button>
                    {menuOpen && (
                        <div className="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-lg shadow-lg z-20">
                            <div className="px-4 py-3 border-b border-gray-100">
                                <p className="text-sm font-semibold text-gray-800">
                                    {currentUser?.name || t('app.unknown_user') || 'User'}
                                </p>
                                <p className="text-xs text-gray-500 truncate">
                                    {currentUser?.email}
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={() => {
                                    if (currentUser) {
                                        navigate(`/settings/users/${currentUser.id}`);
                                    } else {
                                        navigate('/settings/users');
                                    }
                                    setMenuOpen(false);
                                }}
                                className="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                            >
                                {t('app.my_profile') || 'My Profile'}
                            </button>
                            <button
                                type="button"
                                onClick={handleLogout}
                                className="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 border-t border-gray-100"
                            >
                                {t('app.logout')}
                            </button>
                        </div>
                    )}
                </div>
            </div>
        </header>
    );
};

const Layout: React.FC = () => {
    const location = useLocation();
    const navigate = useNavigate();
    const { direction } = useI18n();
    
    // Extract the first path segment to determine current view
    const pathSegments = location.pathname.split('/').filter(Boolean);
    const currentView = pathSegments[0] || '';
    
    return (
        <div className={`flex h-screen bg-gray-50 font-sans ${direction}`}>
            <Sidebar currentView={currentView} onNavigate={navigate} />
            <div className="flex-1 flex flex-col overflow-hidden">
                <Header />
                <main className="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                    <Outlet />
                </main>
            </div>
        </div>
    );
};

export default Layout;
