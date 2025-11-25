import React, { useState, useMemo, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useI18n } from '../hooks/useI18n';
import { fetchTeam } from '../services/teams';
import { fetchLawyers } from '../services/lawyers';
import type { Team } from '../types';
import SearchableMultiSelect from '../components/SearchableMultiSelect';

const TeamDetailPage: React.FC = () => {
    const { id } = useParams<{ id?: string }>();
    const navigate = useNavigate();
    const { t, language } = useI18n();
    const isEditing = !!id && id !== 'new';
    const [team, setTeam] = useState<Team | null>(null);
    const [lawyers, setLawyers] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    
    const [formData, setFormData] = useState({
        name_en: '',
        name_ar: '',
        description_en: '',
        description_ar: '',
        lawyer_ids: [] as number[],
    });

    useEffect(() => {
        const loadData = async () => {
            try {
                setLoading(true);
                const [lawyersData] = await Promise.all([
                    fetchLawyers(),
                ]);
                setLawyers(lawyersData.data || lawyersData);

                if (isEditing && id) {
                    const teamData = await fetchTeam(id);
                    const teamObj = teamData.data || teamData;
                    setTeam(teamObj);
                    setFormData({
                        name_en: teamObj.name_en || '',
                        name_ar: teamObj.name_ar || '',
                        description_en: teamObj.description_en || '',
                        description_ar: teamObj.description_ar || '',
                        lawyer_ids: teamObj.lawyer_ids || [],
                    });
                }
            } catch (err: any) {
                setError(err.message || 'Failed to load data');
            } finally {
                setLoading(false);
            }
        };
        loadData();
    }, [id, isEditing]);

    const lawyerOptions = useMemo(() => 
        lawyers.map(l => ({
            value: l.id,
            label: language === 'ar' ? l.lawyer_name_ar : l.lawyer_name_en,
        })),
        [lawyers, language]
    );

    const handleFormChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handleMembersChange = (selectedIds: (string | number)[]) => {
        setFormData(prev => ({ ...prev, lawyer_ids: selectedIds as number[] }));
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        try {
            // TODO: Call createTeam or updateTeam API when implemented
            if (isEditing && id) {
                // await updateTeam(id, formData);
                console.log('Updating team:', formData);
            } else {
                // await createTeam(formData);
                console.log('Creating team:', formData);
            }
            navigate('/settings/teams');
        } catch (err: any) {
            alert('Failed to save team');
        }
    };
    
    if (loading) {
        return (
            <div className="container mx-auto">
                <div className="text-center py-10">
                    <p className="text-gray-600">{t('common.loading')}</p>
                </div>
            </div>
        );
    }

    if (error && isEditing) {
        return (
            <div className="container mx-auto">
                <div className="text-center py-10">
                    <p className="text-red-600">{t('common.error')}: {error}</p>
                    <button onClick={() => navigate('/settings/teams')} className="mt-4 text-primary-600 hover:underline">
                        &larr; {t('settings_page.back_to_settings')}
                    </button>
                </div>
            </div>
        );
    }

    const pageTitle = isEditing ? `${t('teams_page.edit_team')}: ${language === 'ar' ? team?.name_ar : team?.name_en}` : t('teams_page.new_team');

    return (
        <div className="container mx-auto">
            <button onClick={() => navigate('/settings/teams')} className="text-primary-600 hover:underline mb-4">&larr; {t('settings_page.back_to_settings')}</button>
            <h1 className="text-3xl font-bold text-gray-800 mb-6">{pageTitle}</h1>

            <form onSubmit={handleSubmit} className="bg-white p-6 rounded-lg shadow-md border space-y-6">
                 <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label htmlFor="name_en" className="block text-sm font-medium text-gray-700">Name (English)</label>
                        <input type="text" id="name_en" name="name_en" value={formData.name_en} onChange={handleFormChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label htmlFor="name_ar" className="block text-sm font-medium text-gray-700">Name (Arabic)</label>
                        <input type="text" id="name_ar" name="name_ar" value={formData.name_ar} onChange={handleFormChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                    </div>
                </div>
                <div>
                    <label htmlFor="description_en" className="block text-sm font-medium text-gray-700">Description (English)</label>
                    <textarea id="description_en" name="description_en" value={formData.description_en} onChange={handleFormChange} rows={2} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"></textarea>
                </div>
                <div>
                    <label htmlFor="description_ar" className="block text-sm font-medium text-gray-700">Description (Arabic)</label>
                    <textarea id="description_ar" name="description_ar" value={formData.description_ar} onChange={handleFormChange} rows={2} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"></textarea>
                </div>
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">{t('teams_page.members')}</label>
                    <SearchableMultiSelect
                        options={lawyerOptions}
                        selected={formData.lawyer_ids}
                        onChange={handleMembersChange}
                        placeholder={t('teams_page.add_or_remove_lawyers')}
                    />
                </div>
                 <div className="flex justify-end gap-3 pt-4 border-t mt-6">
                    <button type="button" onClick={() => navigate('/settings/teams')} className="px-6 py-2 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700">{t('common.cancel')}</button>
                    <button type="submit" className="px-6 py-2 bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary-700">{t('common.save')}</button>
                </div>
            </form>
        </div>
    );
};

export default TeamDetailPage;
