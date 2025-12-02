import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useI18n } from '../hooks/useI18n';
import { fetchRole } from '../services/roles';
import type { Role, Permission } from '../types';
import { dbPermissions } from '../services/database';

const RoleDetailPage: React.FC = () => {
    const { id } = useParams<{ id: string }>();
    const navigate = useNavigate();
    const { t, language } = useI18n();
    const [role, setRole] = useState<Role | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [formData, setFormData] = useState({
        name_en: '',
        name_ar: '',
        description_en: '',
        description_ar: '',
    });
    
    const [permissions, setPermissions] = useState<Set<Permission>>(new Set());

    useEffect(() => {
        if (id && id !== 'new') {
            fetchRole(id)
                .then((data) => {
                    const roleData = data.data || data;
                    setRole(roleData);
                    setFormData({
                        name_en: roleData.name_en || roleData.name || '',
                        name_ar: roleData.name_ar || roleData.name || '',
                        description_en: roleData.description_en || '',
                        description_ar: roleData.description_ar || '',
                    });
                    setPermissions(new Set((roleData.permissions || []) as Permission[]));
                })
                .catch((err: any) => setError(err.message || 'Failed to load role'))
                .finally(() => setLoading(false));
        } else {
            setLoading(false);
        }
    }, [id]);

    const handleFormChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };
    
    const handlePermissionChange = (permission: Permission, isChecked: boolean) => {
        setPermissions(prev => {
            const newPermissions = new Set(prev);
            if (isChecked) {
                newPermissions.add(permission);
            } else {
                newPermissions.delete(permission);
            }
            return newPermissions;
        });
    };
    
    const handleSave = async () => {
        try {
            const updatedRole = {
                ...formData,
                permissions: Array.from(permissions),
            };
            // TODO: Call updateRole or createRole API when implemented
            console.log("Saving Role:", updatedRole);
            navigate('/settings/roles');
        } catch (err: any) {
            alert('Failed to save role');
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

    if (error && id !== 'new') {
        return (
            <div className="container mx-auto">
                <div className="text-center py-10">
                    <p className="text-red-600">{t('common.error')}: {error}</p>
                    <button onClick={() => navigate('/settings/roles')} className="mt-4 text-primary-600 hover:underline">
                        &larr; {t('settings_page.back_to_roles')}
                    </button>
                </div>
            </div>
        );
    }

    return (
        <div className="container mx-auto">
            <button onClick={() => navigate('/settings/roles')} className="text-primary-600 hover:underline mb-4">&larr; {t('settings_page.back_to_roles')}</button>
            <div className="flex justify-between items-center mb-6">
                <h1 className="text-3xl font-bold text-gray-800">
                    {id === 'new' ? t('roles_page.new_role') : (
                        <>
                            {t('roles_page.edit_role')}: <span className="text-primary-600">{language === 'ar' ? role?.name_ar : role?.name_en}</span>
                        </>
                    )}
                </h1>
            </div>

            <div className="space-y-6">
                {/* Role Details Form */}
                <div className="bg-white p-6 rounded-lg shadow-md border">
                    <h2 className="text-xl font-bold text-gray-800 mb-4">{t('roles_page.role_details')}</h2>
                    <div className="space-y-4">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label htmlFor="name_en" className="block text-sm font-medium text-gray-700">Name (English)</label>
                                <input type="text" id="name_en" name="name_en" value={formData.name_en} onChange={handleFormChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                            </div>
                             <div>
                                <label htmlFor="name_ar" className="block text-sm font-medium text-gray-700">Name (Arabic)</label>
                                <input type="text" id="name_ar" name="name_ar" value={formData.name_ar} onChange={handleFormChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
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
                    </div>
                </div>

                {/* Permissions */}
                <div className="bg-white p-6 rounded-lg shadow-md border">
                    <h2 className="text-xl font-bold text-gray-800 mb-4">
                        {t('roles_page.assign_permissions')}
                    </h2>
                    <div className="space-y-4">
                        {dbPermissions && dbPermissions.length > 0 ? (
                            dbPermissions.map((group) => (
                                <div key={group.groupKey}>
                                    <h3 className="text-md font-semibold text-gray-700 border-b pb-2 mb-3">
                                        {t(`permissions.${group.groupKey}`) || group.groupKey}
                                    </h3>
                                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                        {group.permissions.map((perm) => {
                                            const key = perm.key as Permission;
                                            return (
                                                <div key={key} className="flex items-center">
                                                    <input
                                                        type="checkbox"
                                                        id={key}
                                                        checked={permissions.has(key)}
                                                        onChange={(e) =>
                                                            handlePermissionChange(key, e.target.checked)
                                                        }
                                                        className="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                                    />
                                                    <label
                                                        htmlFor={key}
                                                        className="ms-2 text-sm text-gray-600"
                                                    >
                                                        {language === 'ar'
                                                            ? perm.description_ar
                                                            : perm.description_en}
                                                    </label>
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>
                            ))
                        ) : (
                            <p className="text-sm text-gray-500">
                                {t('roles_page.no_permissions') ||
                                    'No permissions have been configured yet.'}
                            </p>
                        )}
                    </div>
                </div>
            </div>

             <div className="flex justify-end gap-3 pt-6 mt-6 border-t">
                <button type="button" onClick={() => navigate('/settings/roles')} className="px-6 py-2 bg-gray-600 text-white rounded-lg font-semibold hover:bg-gray-700">
                    {t('common.cancel')}
                </button>
                <button type="button" onClick={handleSave} className="px-6 py-2 bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary-700">
                    {t('common.save')}
                </button>
            </div>
        </div>
    );
};

export default RoleDetailPage;
