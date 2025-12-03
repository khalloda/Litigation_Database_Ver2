import React, { useState, useEffect, useMemo } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useI18n } from '../hooks/useI18n';
import { fetchRole, createRole, updateRole } from '../services/roles';
import type { Role, Permission } from '../types';
import { fetchPermissions, PermissionRecord } from '../services/permissions';

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
    const [availablePermissions, setAvailablePermissions] = useState<PermissionRecord[]>([]);

    // Load role details (when editing)
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

    // Load all available permissions for the current guard
    useEffect(() => {
        fetchPermissions()
            .then((perms) => {
                // Only use permissions for the web guard, since roles are created under web
                setAvailablePermissions(perms.filter((p) => p.guard_name === 'web'));
            })
            .catch((err: any) => {
                console.error('Failed to load permissions', err);
            });
    }, []);

    // Group permissions by their prefix before the dot (e.g. 'cases', 'clients', 'documents', 'admin', 'reports', etc.)
    const groupedPermissions = useMemo(() => {
        const groups: Record<string, PermissionRecord[]> = {};

        availablePermissions.forEach((perm) => {
            const [prefix] = perm.name.split('.');
            const groupKey = prefix || 'other';
            if (!groups[groupKey]) {
                groups[groupKey] = [];
            }
            groups[groupKey].push(perm);
        });

        // Sort permissions within each group for stable display
        Object.values(groups).forEach((perms) =>
            perms.sort((a, b) => a.name.localeCompare(b.name)),
        );

        return groups;
    }, [availablePermissions]);

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
            const payload: Partial<Role> = {
                name_en: formData.name_en,
                name_ar: formData.name_ar,
                description_en: formData.description_en,
                description_ar: formData.description_ar,
                permissions: Array.from(permissions),
            };

            // Backend RoleController expects a single `name` plus optional descriptions and permission names.
            const apiPayload: any = {
                name: payload.name_en || payload.name_ar,
                description_en: payload.description_en,
                description_ar: payload.description_ar,
                permissions: payload.permissions,
            };

            if (id && id !== 'new') {
                await updateRole(id, apiPayload);
            } else {
                await createRole(apiPayload);
            }

            navigate('/settings/roles');
        } catch (err: any) {
            console.error('Failed to save role', err);
            alert(err.message || 'Failed to save role');
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
                        {Object.keys(groupedPermissions).length > 0 ? (
                            Object.entries(groupedPermissions).map(([groupKey, perms]) => (
                                <div key={groupKey}>
                                    <h3 className="text-md font-semibold text-gray-700 border-b pb-2 mb-3">
                                        {t(`permissions.${groupKey}`) || groupKey}
                                    </h3>
                                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                        {perms.map((perm) => {
                                            const key = perm.name as Permission;
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
                                                        {t(`permission_labels.${key}`) || key}
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
