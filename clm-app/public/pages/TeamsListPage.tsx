import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useI18n } from '../hooks/useI18n';
import { fetchTeams } from '../services/teams';
import type { Team } from '../types';
import { PlusIcon, PencilIcon, TrashIcon } from '../components/icons';

const TeamsListPage: React.FC = () => {
    const navigate = useNavigate();
    const { t, language } = useI18n();
    const [teams, setTeams] = useState<Team[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const loadTeams = async () => {
            try {
                setLoading(true);
                const data = await fetchTeams();
                setTeams(data.data || data);
            } catch (err: any) {
                setError(err.message || 'Failed to load teams');
            } finally {
                setLoading(false);
            }
        };
        loadTeams();
    }, []);

    const handleDelete = async (teamId: number) => {
        if (window.confirm(t('teams_page.confirm_delete_text'))) {
            try {
                // TODO: Call deleteTeam API when implemented
                console.log("Deleting team with ID:", teamId);
                const data = await fetchTeams();
                setTeams(data.data || data);
            } catch (err: any) {
                alert('Failed to delete team');
            }
        }
    };

    if (loading) {
        return (
            <div className="container mx-auto">
                <div className="text-center py-10">
                    <p className="text-gray-600">Loading...</p>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="container mx-auto">
                <div className="text-center py-10">
                    <p className="text-red-600">Error: {error}</p>
                </div>
            </div>
        );
    }

    return (
        <div className="container mx-auto">
            <div className="flex justify-between items-center mb-6">
                <h1 className="text-3xl font-bold text-gray-800">{t('teams_page.title')}</h1>
                <button
                    onClick={() => navigate('/settings/teams/new')}
                    className="flex items-center gap-2 px-4 py-2 bg-primary-600 border border-transparent rounded-lg text-white font-semibold hover:bg-primary-700 transition-colors"
                >
                    <PlusIcon className="w-5 h-5" />
                    {t('teams_page.new_team')}
                </button>
            </div>

            <div className="bg-white rounded-lg shadow-md overflow-hidden border">
                <div className="overflow-x-auto">
                    {teams.length > 0 ? (
                        <table className="min-w-full">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="p-3 text-start font-semibold text-gray-600 text-sm">{t('teams_page.team_name')}</th>
                                    <th className="p-3 text-start font-semibold text-gray-600 text-sm">{t('teams_page.description')}</th>
                                    <th className="p-3 text-center font-semibold text-gray-600 text-sm">{t('teams_page.members')}</th>
                                    <th className="p-3 text-start font-semibold text-gray-600 text-sm">{t('teams_page.actions')}</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {teams.map(team => (
                                    <tr key={team.id}>
                                        <td className="p-3 text-sm text-gray-800 font-medium">{language === 'ar' ? team.name_ar : team.name_en}</td>
                                        <td className="p-3 text-sm text-gray-600 max-w-md">{language === 'ar' ? team.description_ar : team.description_en}</td>
                                        <td className="p-3 text-center text-sm">
                                            <span className="bg-blue-100 text-blue-800 font-bold px-2 py-1 rounded-full">{team.lawyer_ids?.length || 0}</span>
                                        </td>
                                        <td className="p-3 text-sm">
                                            <div className="flex items-center gap-2">
                                                <button onClick={() => navigate(`/settings/teams/${team.id}`)} className="p-2 bg-blue-500 text-white rounded hover:bg-blue-600" title={t('teams_page.edit_team')}>
                                                    <PencilIcon className="w-4 h-4" />
                                                </button>
                                                <button onClick={() => handleDelete(team.id)} className="p-2 bg-red-500 text-white rounded hover:bg-red-600" title={t('teams_page.delete_team')}>
                                                    <TrashIcon className="w-4 h-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    ) : <p className="text-center p-10 text-gray-500">{t('teams_page.no_teams')}</p>}
                </div>
            </div>
        </div>
    );
};

export default TeamsListPage;
