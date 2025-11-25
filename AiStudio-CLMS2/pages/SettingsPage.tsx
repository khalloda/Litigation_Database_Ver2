import React, { useState, useMemo, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useI18n } from '../hooks/useI18n';
import { fetchOptionSets } from '../services/options';
import type { OptionSet, OptionValue } from '../types';
import Modal from '../components/Modal';
import { PlusIcon, PencilIcon, TrashIcon, ChevronRightIcon, SettingsIcon, ShieldCheckIcon, UserGroupIcon } from '../components/icons';

interface OptionValueFormProps {
    onClose: () => void;
    onSave: (value: Omit<OptionValue, 'id' | 'set_id'>) => void;
    initialData?: Omit<OptionValue, 'id' | 'set_id'>;
}

const OptionValueForm: React.FC<OptionValueFormProps> = ({ onClose, onSave, initialData }) => {
    const { t } = useI18n();
    const [formData, setFormData] = useState({
        code: initialData?.code || '',
        label_en: initialData?.label_en || '',
        label_ar: initialData?.label_ar || '',
    });

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        onSave(formData);
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            <div>
                <label htmlFor="code" className="block text-sm font-medium text-gray-700">{t('settings_page.code')}</label>
                <input type="text" id="code" name="code" value={formData.code} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
            </div>
            <div>
                <label htmlFor="label_en" className="block text-sm font-medium text-gray-700">{t('settings_page.label_en')}</label>
                <input type="text" id="label_en" name="label_en" value={formData.label_en} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
            </div>
            <div>
                <label htmlFor="label_ar" className="block text-sm font-medium text-gray-700">{t('settings_page.label_ar')}</label>
                <input type="text" id="label_ar" name="label_ar" value={formData.label_ar} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
            </div>
            <div className="flex justify-end gap-3 pt-4 border-t mt-6">
                <button type="button" onClick={onClose} className="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300">
                    {t('common.cancel')}
                </button>
                <button type="submit" className="px-4 py-2 bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary-700">
                    {t('common.save')}
                </button>
            </div>
        </form>
    );
};

const SettingCard: React.FC<{ title: string; description: string; icon: React.ReactNode; onClick: () => void; }> = ({ title, description, icon, onClick }) => (
    <div 
        onClick={onClick}
        className="bg-white p-6 rounded-lg shadow-sm border flex items-center justify-between cursor-pointer hover:shadow-md hover:border-primary-300 transition-all group"
        role="button"
        tabIndex={0}
        onKeyDown={(e) => (e.key === 'Enter' || e.key === ' ') && onClick()}
    >
        <div className="flex items-center gap-4">
            <div className="bg-primary-100 text-primary-600 p-3 rounded-full">
                {icon}
            </div>
            <div>
                <h3 className="text-lg font-bold text-gray-800">{title}</h3>
                <p className="text-gray-500 text-sm">{description}</p>
            </div>
        </div>
        <ChevronRightIcon className="w-6 h-6 text-gray-400 group-hover:text-primary-600 transition-colors" />
    </div>
);

const SettingsPage: React.FC = () => {
    const navigate = useNavigate();
    const { t, language } = useI18n();
    const [view, setView] = useState<'hub' | 'optionSets'>('hub');
    const [selectedSet, setSelectedSet] = useState<OptionSet | null>(null);
    const [isFormOpen, setIsFormOpen] = useState(false);
    const [editingValue, setEditingValue] = useState<OptionValue | null>(null);
    const [optionSets, setOptionSets] = useState<OptionSet[]>([]);
    const [optionValues, setOptionValues] = useState<OptionValue[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const loadData = async () => {
            try {
                setLoading(true);
                const setsData = await fetchOptionSets();
                // Service function already handles pagination extraction
                const sets = Array.isArray(setsData) ? setsData : [];
                setOptionSets(sets);
                
                // Debug: Log first set structure
                if (sets.length > 0) {
                    console.log('First option set structure:', sets[0]);
                    console.log('First set optionValues:', sets[0].optionValues);
                    console.log('First set option_values:', sets[0].option_values);
                }
                
                // Extract option values from the loaded sets (they're already included in the relationship)
                const allValues: OptionValue[] = [];
                
                // First, try to extract from relationship if present
                sets.forEach(set => {
                    // Try different possible property names (Laravel might use snake_case)
                    const values = set.optionValues || set.option_values || set.values || [];
                    if (Array.isArray(values) && values.length > 0) {
                        values.forEach((val: any) => {
                            allValues.push({
                                id: val.id,
                                set_id: set.id,
                                code: val.code || '',
                                label_en: val.label_en || '',
                                label_ar: val.label_ar || '',
                            });
                        });
                    }
                });
                
                // If no values found in relationships, load them separately
                if (allValues.length === 0 && sets.length > 0) {
                    console.log('No values in relationships, loading separately...');
                    try {
                        // Load values for each set using the set key
                        const valuesPromises = sets.map(async (set: any) => {
                            try {
                                const values = await fetchOptionsBySetKey(set.key);
                                return Array.isArray(values) ? values.map((val: any) => ({
                                    id: val.id,
                                    set_id: set.id,
                                    code: val.code || '',
                                    label_en: val.label_en || '',
                                    label_ar: val.label_ar || '',
                                })) : [];
                            } catch (err) {
                                console.warn(`Failed to load values for set ${set.key}:`, err);
                                return [];
                            }
                        });
                        const valuesArrays = await Promise.all(valuesPromises);
                        valuesArrays.forEach(values => {
                            allValues.push(...values);
                        });
                    } catch (err) {
                        console.error('Error loading option values separately:', err);
                    }
                }
                
                setOptionValues(allValues);
                console.log('Loaded option sets:', sets.length, 'option values:', allValues.length);
            } catch (err: any) {
                console.error('Error loading option sets:', err);
                setError(err.response?.data?.message || err.message || 'Failed to load option sets');
            } finally {
                setLoading(false);
            }
        };
        loadData();
    }, []);

    const optionValuesForSelectedSet = useMemo(() => {
        if (!selectedSet) return [];
        return optionValues.filter(val => val.set_id === selectedSet.id);
    }, [selectedSet, optionValues]);

    const handleSave = (value: Omit<OptionValue, 'id' | 'set_id'>) => {
        if (editingValue) {
            console.log('UPDATING value:', { ...value, id: editingValue.id, set_id: editingValue.set_id });
        } else {
            console.log('ADDING new value to set', selectedSet?.key, ':', { ...value, id: Date.now(), set_id: selectedSet?.id });
        }
        closeForm();
    };
    
    const handleDelete = (valueId: number) => {
        if(window.confirm(t('settings_page.delete_confirm_text'))) {
            console.log('DELETING value with id:', valueId);
        }
    };
    
    const openFormToEdit = (value: OptionValue) => {
        setEditingValue(value);
        setIsFormOpen(true);
    };

    const openFormToAdd = () => {
        setEditingValue(null);
        setIsFormOpen(true);
    };

    const closeForm = () => {
        setIsFormOpen(false);
        setEditingValue(null);
    };
    
    const valuesCountMap = useMemo(() => {
        return optionValues.reduce((acc, value) => {
            acc[value.set_id] = (acc[value.set_id] || 0) + 1;
            return acc;
        }, {} as Record<number, number>);
    }, [optionValues]);

    // Show loading/error states
    if (loading) {
        return (
            <div className="container mx-auto">
                <div className="text-center py-10">
                    <p className="text-gray-600">{t('common.loading')}</p>
                </div>
            </div>
        );
    }

    if (error && view === 'optionSets') {
        return (
            <div className="container mx-auto">
                <div className="text-center py-10">
                    <p className="text-red-600">{t('common.error')}: {error}</p>
                    <button onClick={() => window.location.reload()} className="mt-4 text-primary-600 hover:underline">
                        Retry
                    </button>
                </div>
            </div>
        );
    }

    if (view === 'optionSets') {
        // Master View: Table of Option Sets
        if (!selectedSet) {
            return (
                 <div className="container mx-auto">
                    <button onClick={() => setView('hub')} className="text-primary-600 hover:underline mb-4">&larr; {t('settings_page.back_to_settings')}</button>
                    <div className="flex justify-between items-center mb-6">
                        <h1 className="text-3xl font-bold text-gray-800">{t('settings_page.manage_option_sets')}</h1>
                        <button
                            onClick={() => alert('Create New Option Set form not implemented yet.')}
                            className="flex items-center gap-2 px-4 py-2 bg-primary-600 border border-transparent rounded-lg text-white font-semibold hover:bg-primary-700 transition-colors"
                        >
                            <PlusIcon className="w-5 h-5" />
                            {t('settings_page.create_option_set')}
                        </button>
                    </div>

                     <div className="bg-white rounded-lg shadow-md overflow-hidden border">
                        <div className="overflow-x-auto">
                            <table className="min-w-full">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="p-3 text-start font-semibold text-gray-600 text-sm">{t('settings_page.key')}</th>
                                        <th className="p-3 text-start font-semibold text-gray-600 text-sm">{t('app.name')}</th>
                                        <th className="p-3 text-start font-semibold text-gray-600 text-sm">{t('common.description')}</th>
                                        <th className="p-3 text-center font-semibold text-gray-600 text-sm">{t('settings_page.values_count')}</th>
                                        <th className="p-3 text-start font-semibold text-gray-600 text-sm">{t('settings_page.status')}</th>
                                        <th className="p-3 text-start font-semibold text-gray-600 text-sm">{t('settings_page.actions')}</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {optionSets.map(set => (
                                        <tr key={set.id}>
                                            <td className="p-3 text-sm text-gray-500 font-mono">{set.key}</td>
                                            <td className="p-3 text-sm text-gray-800 font-medium">{language === 'ar' ? set.name_ar : set.name_en}</td>
                                            <td className="p-3 text-sm text-gray-600 max-w-xs">{language === 'ar' ? set.description_ar : set.description_en}</td>
                                            <td className="p-3 text-sm text-center">
                                                <span className="bg-blue-100 text-blue-800 font-bold text-xs px-2 py-1 rounded-full">
                                                    {valuesCountMap[set.id] || 0}
                                                </span>
                                            </td>
                                            <td className="p-3 text-sm">
                                                <span className="bg-green-100 text-green-800 font-medium text-xs px-2 py-1 rounded-full">
                                                    {t('status.active')}
                                                </span>
                                            </td>
                                            <td className="p-3 text-sm">
                                                <div className="flex items-center gap-1">
                                                    <button onClick={() => setSelectedSet(set)} className="p-2 bg-blue-500 text-white rounded hover:bg-blue-600" title="Manage Values"><ChevronRightIcon className="w-4 h-4" /></button>
                                                    <button className="p-2 bg-yellow-500 text-white rounded hover:bg-yellow-600" title="Edit Set"><PencilIcon className="w-4 h-4" /></button>
                                                    <button className="p-2 bg-red-500 text-white rounded hover:bg-red-600" title="Delete Set"><TrashIcon className="w-4 h-4" /></button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                     </div>
                </div>
            );
        }
        
        // Detail View: Managing values for a selected set
        return (
            <div className="container mx-auto">
                <button onClick={() => setSelectedSet(null)} className="text-primary-600 hover:underline mb-4">&larr; {t('settings_page.back_to_option_sets')}</button>
                 <div className="bg-white p-4 rounded-lg shadow-sm border">
                    <div>
                        <div className="flex justify-between items-center mb-3">
                            <h2 className="text-lg font-bold text-gray-700">
                                {t('settings_page.values_for')} <span className="text-primary-600">{language === 'ar' ? selectedSet.name_ar : selectedSet.name_en}</span>
                            </h2>
                            <button
                                onClick={openFormToAdd}
                                className="flex items-center gap-2 px-3 py-1.5 bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary-700 text-sm"
                            >
                                <PlusIcon className="w-4 h-4" />
                                {t('settings_page.add_new_value')}
                            </button>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="min-w-full">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="p-2 text-start font-semibold text-gray-600 text-sm">{t('settings_page.id')}</th>
                                        <th className="p-2 text-start font-semibold text-gray-600 text-sm">{t('settings_page.code')}</th>
                                        <th className="p-2 text-start font-semibold text-gray-600 text-sm">{t('settings_page.label_en')}</th>
                                        <th className="p-2 text-start font-semibold text-gray-600 text-sm">{t('settings_page.label_ar')}</th>
                                        <th className="p-2 text-start font-semibold text-gray-600 text-sm">{t('settings_page.actions')}</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {optionValuesForSelectedSet.length === 0 ? (
                                        <tr>
                                            <td colSpan={5} className="p-4 text-center text-gray-500">
                                                No values found
                                            </td>
                                        </tr>
                                    ) : (
                                        optionValuesForSelectedSet.map(val => (
                                            <tr key={val.id}>
                                                <td className="p-2 text-sm text-gray-500">{val.id}</td>
                                                <td className="p-2 text-sm text-gray-700 font-mono">{val.code}</td>
                                                <td className="p-2 text-sm text-gray-700">{val.label_en}</td>
                                                <td className="p-2 text-sm text-gray-700">{val.label_ar}</td>
                                                <td className="p-2 text-sm space-x-2">
                                                    <button onClick={() => openFormToEdit(val)} className="text-blue-600 hover:underline font-medium">{t('settings_page.edit')}</button>
                                                    <button onClick={() => handleDelete(val.id)} className="text-red-600 hover:underline font-medium">{t('settings_page.delete')}</button>
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {isFormOpen && (
                     <Modal
                        title={editingValue ? t('settings_page.edit_value') : t('settings_page.add_new_value')}
                        onClose={closeForm}
                    >
                        <OptionValueForm 
                            onClose={closeForm}
                            onSave={handleSave}
                            initialData={editingValue ? { code: editingValue.code, label_en: editingValue.label_en, label_ar: editingValue.label_ar } : undefined}
                        />
                    </Modal>
                )}
            </div>
        );
    }
    
    return (
        <div className="container mx-auto">
            <h1 className="text-3xl font-bold text-gray-800 mb-6">{t('settings_page.title')}</h1>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <SettingCard 
                    title={t('settings_page.users')} 
                    description={t('settings_page.users_desc')}
                    icon={<UserGroupIcon className="w-6 h-6" />}
                    onClick={() => navigate('/settings/users')}
                />
                <SettingCard 
                    title={t('settings_page.roles')} 
                    description={t('settings_page.roles_desc')}
                    icon={<ShieldCheckIcon className="w-6 h-6" />}
                    onClick={() => navigate('/settings/roles')}
                />
                <SettingCard 
                    title={t('settings_page.teams')} 
                    description={t('settings_page.teams_desc')}
                    icon={<UserGroupIcon className="w-6 h-6" />}
                    onClick={() => navigate('/settings/teams')}
                />
                <SettingCard 
                    title={t('settings_page.manage_option_sets')} 
                    description={t('settings_page.option_sets_desc')}
                    icon={<SettingsIcon className="w-6 h-6" />}
                    onClick={() => setView('optionSets')}
                />
            </div>
        </div>
    );
};

export default SettingsPage;
