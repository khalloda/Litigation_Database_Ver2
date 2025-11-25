import React, { useState, useMemo, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useI18n } from '../hooks/useI18n';
import { fetchUser } from '../services/users';
import { fetchRoles } from '../services/roles';
import type { User } from '../types';
import SearchableSelect from '../components/SearchableSelect';

const UserDetailPage: React.FC = () => {
    const { id } = useParams<{ id?: string }>();
    const navigate = useNavigate();
    const { t, language } = useI18n();
    const isEditing = !!id && id !== 'new';
    const [user, setUser] = useState<User | null>(null);
    const [roles, setRoles] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    const [formData, setFormData] = useState({
        name_en: '',
        name_ar: '',
        email: '',
        password: '',
        role_id: '',
        is_active: true,
    });

    useEffect(() => {
        const loadData = async () => {
            try {
                setLoading(true);
                const [rolesData] = await Promise.all([
                    fetchRoles(),
                ]);
                setRoles(rolesData.data || rolesData);

                if (isEditing && id) {
                    const userData = await fetchUser(id);
                    const userObj = userData.data || userData;
                    setUser(userObj);
                    setFormData({
                        name_en: userObj.name_en || '',
                        name_ar: userObj.name_ar || '',
                        email: userObj.email || '',
                        password: '',
                        role_id: userObj.role_id || '',
                        is_active: userObj.is_active ?? true,
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

    const roleOptions = useMemo(() => 
        roles.map(r => ({
            value: r.id,
            label: language === 'ar' ? r.name_ar : r.name_en,
        })),
        [roles, language]
    );

    const handleFormChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value, type, checked } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: type === 'checkbox' ? checked : value,
        }));
    };

    const handleSelectChange = (value: string | number) => {
        setFormData(prev => ({ ...prev, role_id: value }));
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        try {
            // TODO: Call createUser or updateUser API when implemented
            if (isEditing && id) {
                // await updateUser(id, formData);
                console.log('Updating user:', formData);
            } else {
                // await createUser(formData);
                console.log('Creating user:', formData);
            }
            navigate('/settings/users');
        } catch (err: any) {
            alert('Failed to save user');
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
                    <button onClick={() => navigate('/settings/users')} className="mt-4 text-primary-600 hover:underline">
                        &larr; {t('settings_page.back_to_settings')}
                    </button>
                </div>
            </div>
        );
    }

    const pageTitle = isEditing 
        ? `${t('new_user_form.title_edit')}: ${language === 'ar' ? user?.name_ar : user?.name_en}` 
        : t('new_user_form.title_new');

    return (
        <div className="container mx-auto">
            <button onClick={() => navigate('/settings/users')} className="text-primary-600 hover:underline mb-4">&larr; {t('settings_page.back_to_settings')}</button>
            <h1 className="text-3xl font-bold text-gray-800 mb-6">{pageTitle}</h1>

            <form onSubmit={handleSubmit} className="bg-white p-6 rounded-lg shadow-md border space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label htmlFor="name_en" className="block text-sm font-medium text-gray-700">{t('new_user_form.name_en')}</label>
                        <input type="text" id="name_en" name="name_en" value={formData.name_en} onChange={handleFormChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label htmlFor="name_ar" className="block text-sm font-medium text-gray-700">{t('new_user_form.name_ar')}</label>
                        <input type="text" id="name_ar" name="name_ar" value={formData.name_ar} onChange={handleFormChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                    </div>
                </div>
                <div>
                    <label htmlFor="email" className="block text-sm font-medium text-gray-700">{t('new_user_form.email')}</label>
                    <input type="email" id="email" name="email" value={formData.email} onChange={handleFormChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                </div>
                <div>
                    <label htmlFor="password" className="block text-sm font-medium text-gray-700">{t('new_user_form.password')}</label>
                    <input type="password" id="password" name="password" value={formData.password} onChange={handleFormChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                    {isEditing && <p className="mt-1 text-xs text-gray-500">{t('new_user_form.password_help')}</p>}
                </div>
                 <div className="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">{t('new_user_form.role')}</label>
                        <SearchableSelect
                            options={roleOptions}
                            value={formData.role_id}
                            onChange={handleSelectChange}
                            placeholder={t('new_user_form.select_role')}
                        />
                    </div>
                    <div className="flex items-center gap-2">
                        <input type="checkbox" id="is_active" name="is_active" checked={formData.is_active} onChange={handleFormChange} className="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
                        <label htmlFor="is_active" className="text-sm font-medium text-gray-700">{t('new_user_form.active')}</label>
                    </div>
                </div>
                <div className="flex justify-end gap-3 pt-4 border-t mt-6">
                    <button type="button" onClick={() => navigate('/settings/users')} className="px-6 py-2 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700">{t('common.cancel')}</button>
                    <button type="submit" className="px-6 py-2 bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary-700">{t('common.save')}</button>
                </div>
            </form>
        </div>
    );
};

export default UserDetailPage;
