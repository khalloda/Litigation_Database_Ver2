import React, { useState, useMemo, useEffect } from 'react';
import { useI18n } from '../hooks/useI18n';
import { fetchCases } from '../services/cases';
import { fetchLawyers } from '../services/lawyers';
import { createTask } from '../services/tasks';
import Modal from './Modal';
import SearchableSelect from './SearchableSelect';
import type { TaskStatus, TaskPriority } from '../types';

interface NewTaskFormProps {
    onClose: () => void;
    onSave?: (formData: any) => void;
    parentId?: number;
}

const NewTaskForm: React.FC<NewTaskFormProps> = ({ onClose, onSave, parentId }) => {
    const { t, language } = useI18n();
    const [cases, setCases] = useState<any[]>([]);
    const [lawyers, setLawyers] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState<string | null>(null);
    
    const [formData, setFormData] = useState({
        title: '',
        description: '',
        caseId: '',
        dueDate: '',
        priority: 'medium' as TaskPriority,
        status: 'todo' as TaskStatus,
        parentId: parentId,
        performerId: '',
        court: '',
        circuit: '',
        last_follow_up: '',
        result: '',
    });

    useEffect(() => {
        const loadCasesAndLawyers = async () => {
            try {
                setLoading(true);
                const [casesData, lawyersData] = await Promise.all([
                    fetchCases(),
                    fetchLawyers(),
                ]);
                setCases(casesData.data || casesData);
                setLawyers(lawyersData.data || lawyersData);
            } catch (err: any) {
                setError(err.message || 'Failed to load cases or lawyers');
            } finally {
                setLoading(false);
            }
        };
        loadCasesAndLawyers();
    }, []);

    const caseOptions = useMemo(
        () =>
            cases
                .map((c) => ({
                    value: String(c.id),
                    label: `[${c.case_number || c.id}] ${
                        language === 'ar' ? c.case_name_ar || c.case_name_en : c.case_name_en || c.case_name_ar
                    }`,
                }))
                .sort((a, b) => a.label.localeCompare(b.label)),
        [cases, language],
    );

    const lawyerOptions = useMemo(
        () =>
            lawyers
                .map((l) => ({
                    value: String(l.id),
                    label: language === 'ar' ? l.lawyer_name_ar : l.lawyer_name_en,
                }))
                .sort((a, b) => a.label.localeCompare(b.label)),
        [lawyers, language],
    );

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handleSelectChange = (name: string, value: string | number) => {
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        try {
            setSubmitting(true);
            setError(null);
            const payload = {
                title: formData.title,
                description: formData.description,
                case_id: formData.caseId ? Number(formData.caseId) : null,
                due_date: formData.dueDate || null,
                priority: formData.priority,
                status: formData.status,
                parent_id: formData.parentId || null,
                lawyer_id: formData.performerId ? Number(formData.performerId) : null,
                court: formData.court || null,
                circuit: formData.circuit || null,
                last_follow_up: formData.last_follow_up || null,
                result: formData.result || null,
            };
            const result = await createTask(payload);
            onSave?.(result?.data ?? result);
            onClose();
        } catch (err: any) {
            setError(err.message || 'Failed to create task');
        } finally {
            setSubmitting(false);
        }
    };

    const title = parentId ? t('new_task_form.subtask_title') : t('new_task_form.title');

    return (
        <Modal title={title} onClose={onClose}>
            <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                    <label htmlFor="title" className="block text-sm font-medium text-gray-700">{t('new_task_form.task_title')}</label>
                    <input type="text" id="title" name="title" value={formData.title} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                </div>
                <div>
                    <label htmlFor="description" className="block text-sm font-medium text-gray-700">{t('common.description')}</label>
                    <textarea id="description" name="description" value={formData.description} onChange={handleChange} rows={3} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"></textarea>
                </div>
                {loading ? (
                    <div className="text-sm text-gray-500">Loading cases &amp; lawyers...</div>
                ) : (
                    <>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                {t('new_task_form.case')}
                            </label>
                            <SearchableSelect
                                options={caseOptions}
                                value={formData.caseId}
                                onChange={(v) => handleSelectChange('caseId', v)}
                                placeholder={t('new_task_form.select_case')}
                            />
                        </div>
                        <div className="mt-4">
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                {t('task.performer') || 'Task Performer'}
                            </label>
                            <SearchableSelect
                                options={lawyerOptions}
                                value={formData.performerId}
                                onChange={(v) => handleSelectChange('performerId', v)}
                                placeholder={t('new_task_form.select_lawyer') || 'Select performer'}
                            />
                        </div>
                    </>
                )}
                {error && (
                    <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm">
                        {error}
                    </div>
                )}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label htmlFor="dueDate" className="block text-sm font-medium text-gray-700">{t('new_task_form.due_date')}</label>
                        <input type="date" id="dueDate" name="dueDate" value={formData.dueDate} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label htmlFor="priority" className="block text-sm font-medium text-gray-700">{t('new_task_form.priority')}</label>
                        <select id="priority" name="priority" value={formData.priority} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm bg-white">
                            <option value="low">{t('priority.low')}</option>
                            <option value="medium">{t('priority.medium')}</option>
                            <option value="high">{t('priority.high')}</option>
                        </select>
                    </div>
                    <div>
                        <label htmlFor="last_follow_up" className="block text-sm font-medium text-gray-700">
                            {t('new_task_form.last_follow_up') || 'Last follow-up'}
                        </label>
                        <input
                            type="date"
                            id="last_follow_up"
                            name="last_follow_up"
                            value={formData.last_follow_up}
                            onChange={handleChange}
                            className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"
                        />
                    </div>
                </div>
                 <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label htmlFor="court" className="block text-sm font-medium text-gray-700">
                            {t('new_task_form.court') || 'Court'}
                        </label>
                        <input
                            type="text"
                            id="court"
                            name="court"
                            value={formData.court}
                            onChange={handleChange}
                            className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"
                        />
                    </div>
                    <div>
                        <label htmlFor="circuit" className="block text-sm font-medium text-gray-700">
                            {t('new_task_form.circuit') || 'Circuit'}
                        </label>
                        <input
                            type="text"
                            id="circuit"
                            name="circuit"
                            value={formData.circuit}
                            onChange={handleChange}
                            className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"
                        />
                    </div>
                </div>
                 <div>
                    <label htmlFor="status" className="block text-sm font-medium text-gray-700">
                        {t('new_task_form.status')}
                    </label>
                    <select
                        id="status"
                        name="status"
                        value={formData.status}
                        onChange={handleChange}
                        className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm bg-white"
                    >
                        <option value="todo">{t('status.todo')}</option>
                        <option value="in-progress">{t('status.in-progress')}</option>
                        <option value="completed">{t('status.completed')}</option>
                    </select>
                </div>
                <div>
                    <label htmlFor="result" className="block text-sm font-medium text-gray-700">
                        {t('new_task_form.result') || 'Result / Notes'}
                    </label>
                    <textarea
                        id="result"
                        name="result"
                        value={formData.result}
                        onChange={handleChange}
                        rows={3}
                        className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm"
                    />
                </div>
                <div className="flex justify-end gap-3 pt-4 border-t mt-6">
                    <button type="button" onClick={onClose} className="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300">
                        {t('common.cancel')}
                    </button>
                    <button type="submit" disabled={submitting} className="px-4 py-2 bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary-700 disabled:opacity-50">
                        {submitting ? t('common.saving') : t('common.save')}
                    </button>
                </div>
            </form>
        </Modal>
    );
};

export default NewTaskForm;