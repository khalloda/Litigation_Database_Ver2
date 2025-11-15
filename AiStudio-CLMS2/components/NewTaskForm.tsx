import React, { useState, useMemo } from 'react';
import { useI18n } from '../hooks/useI18n';
import { mockCases } from '../services/mockData';
import Modal from './Modal';
import SearchableSelect from './SearchableSelect';
import type { TaskStatus, TaskPriority } from '../types';

interface NewTaskFormProps {
    onClose: () => void;
    onSave: (formData: any) => void;
    parentId?: number;
}

const NewTaskForm: React.FC<NewTaskFormProps> = ({ onClose, onSave, parentId }) => {
    const { t, language } = useI18n();
    const [formData, setFormData] = useState({
        title: '',
        description: '',
        caseId: '',
        dueDate: '',
        priority: 'medium' as TaskPriority,
        status: 'todo' as TaskStatus,
        parentId: parentId,
    });

    const caseOptions = useMemo(() =>
        mockCases.map(c => ({
            value: c.id,
            label: `[${c.case_number}] ${language === 'ar' ? c.case_name_ar : c.case_name_en}`
        })).sort((a, b) => a.label.localeCompare(b.label)),
        [language]
    );

    const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handleSelectChange = (name: string, value: string | number) => {
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        onSave(formData);
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
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">{t('new_task_form.case')}</label>
                    <SearchableSelect options={caseOptions} value={formData.caseId} onChange={(v) => handleSelectChange('caseId', v)} placeholder={t('new_task_form.select_case')} />
                </div>
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
                </div>
                 <div>
                    <label htmlFor="status" className="block text-sm font-medium text-gray-700">{t('new_task_form.status')}</label>
                    <select id="status" name="status" value={formData.status} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm bg-white">
                        <option value="todo">{t('status.todo')}</option>
                        <option value="in-progress">{t('status.in-progress')}</option>
                        <option value="completed">{t('status.completed')}</option>
                    </select>
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
        </Modal>
    );
};

export default NewTaskForm;