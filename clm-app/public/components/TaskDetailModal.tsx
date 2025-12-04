import React, { useEffect, useState } from 'react';
import { useI18n } from '../hooks/useI18n';
import { fetchTask } from '../services/tasks';
import api from '../services/api';

interface TaskDetailModalProps {
  taskId: number;
  onClose: () => void;
  onUpdated?: () => void;
}

interface ApiSubtask {
  id: number;
  task_id: number;
  performer?: string | null;
  next_date?: string | null;
  result?: string | null;
  procedure_date?: string | null;
  report?: boolean;
}

const TaskDetailModal: React.FC<TaskDetailModalProps> = ({ taskId, onClose, onUpdated }) => {
  const { t } = useI18n();
  const [task, setTask] = useState<any | null>(null);
  const [subtasks, setSubtasks] = useState<ApiSubtask[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);
  const [newSubtask, setNewSubtask] = useState<{ performer: string; next_date: string; result: string }>({
    performer: '',
    next_date: '',
    result: '',
  });

  useEffect(() => {
    const load = async () => {
      try {
        setLoading(true);
        setError(null);
        const response = await fetchTask(taskId);
        const data = response?.data ?? response;
        setTask(data);
        setSubtasks(data.subtasks ?? []);
      } catch (e: any) {
        setError(e?.message || 'Failed to load task');
      } finally {
        setLoading(false);
      }
    };
    load();
  }, [taskId]);

  const handleCreateSubtask = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      setSubmitting(true);
      setError(null);
      const payload = {
        performer: newSubtask.performer || null,
        next_date: newSubtask.next_date || null,
        result: newSubtask.result || null,
      };
      const response = await api.post(`/tasks/${taskId}/subtasks`, payload);
      const created: ApiSubtask = response.data?.data ?? response.data;
      setSubtasks((prev) => [...prev, created]);
      setNewSubtask({ performer: '', next_date: '', result: '' });
      onUpdated?.();
    } catch (e: any) {
      setError(e?.response?.data?.message || e?.message || 'Failed to create subtask');
    } finally {
      setSubmitting(false);
    }
  };

  const handleDeleteSubtask = async (subtaskId: number) => {
    if (!window.confirm(t('settings_page.delete_confirm_text') || 'Are you sure you want to delete this subtask?')) {
      return;
    }
    try {
      await api.delete(`/tasks/${taskId}/subtasks/${subtaskId}`);
      setSubtasks((prev) => prev.filter((s) => s.id !== subtaskId));
      onUpdated?.();
    } catch (e: any) {
      setError(e?.response?.data?.message || e?.message || 'Failed to delete subtask');
    }
  };

  if (loading) {
    return (
      <div className="fixed inset-0 z-40 flex items-center justify-center bg-black/30">
        <div className="bg-white rounded-xl shadow-lg p-6 w-full max-w-xl">
          <p className="text-gray-600">{t('common.loading')}</p>
        </div>
      </div>
    );
  }

  if (!task) {
    return null;
  }

  return (
    <div className="fixed inset-0 z-40 flex items-center justify-center bg-black/30">
      <div className="bg-white rounded-xl shadow-lg p-6 w-full max-w-3xl max-h-[80vh] overflow-y-auto">
        <div className="flex justify-between items-center mb-4">
          <h2 className="text-xl font-semibold text-gray-800">
            {t('tasks_page.title')} #{task.id}
          </h2>
          <button
            type="button"
            onClick={onClose}
            className="text-gray-500 hover:text-gray-800 text-sm font-semibold"
          >
            {t('common.close') || 'Close'}
          </button>
        </div>

        {error && (
          <div className="mb-4 rounded-md bg-red-50 border border-red-200 px-4 py-2 text-sm text-red-700">
            {error}
          </div>
        )}

        <div className="space-y-1 mb-6">
          <p className="font-bold text-gray-900">{task.title ?? task.required_work}</p>
          {task.description && <p className="text-sm text-gray-700 whitespace-pre-wrap">{task.description}</p>}
          {task.case && (
            <p className="text-xs text-gray-500">
              Case: {task.case.matter_name_en} / {task.case.matter_name_ar}
            </p>
          )}
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h3 className="text-sm font-semibold text-gray-700 mb-2">
              {t('task.subtasks') || 'Sub-tasks'}
            </h3>
            {subtasks.length === 0 ? (
              <p className="text-sm text-gray-500">{t('tasks_page.no_tasks') || 'No sub-tasks yet.'}</p>
            ) : (
              <ul className="space-y-2">
                {subtasks.map((s) => (
                  <li
                    key={s.id}
                    className="flex justify-between items-start gap-3 border rounded-lg px-3 py-2 text-sm"
                  >
                    <div>
                      {s.result && <p className="font-medium text-gray-800">{s.result}</p>}
                      {s.performer && (
                        <p className="text-xs text-gray-600">
                          {t('task.performer') || 'Performer'}: {s.performer}
                        </p>
                      )}
                      {s.next_date && (
                        <p className="text-xs text-gray-600">
                          {t('new_task_form.due_date') || 'Next Date'}:{' '}
                          {new Date(s.next_date).toLocaleDateString()}
                        </p>
                      )}
                    </div>
                    <button
                      type="button"
                      onClick={() => handleDeleteSubtask(s.id)}
                      className="text-xs text-red-600 hover:text-red-800 font-semibold"
                    >
                      {t('settings_page.delete') || 'Delete'}
                    </button>
                  </li>
                ))}
              </ul>
            )}
          </div>

          <div>
            <h3 className="text-sm font-semibold text-gray-700 mb-2">
              {t('task.add_subtask') || 'Add Sub-task'}
            </h3>
            <form onSubmit={handleCreateSubtask} className="space-y-3">
              <div>
                <label className="block text-xs font-medium text-gray-700 mb-1">
                  {t('task.performer') || 'Performer'}
                </label>
                <input
                  type="text"
                  className="w-full rounded-md border border-gray-300 px-2 py-1 text-sm"
                  value={newSubtask.performer}
                  onChange={(e) => setNewSubtask((prev) => ({ ...prev, performer: e.target.value }))}
                />
              </div>
              <div>
                <label className="block text-xs font-medium text-gray-700 mb-1">
                  {t('new_task_form.due_date') || 'Next Date'}
                </label>
                <input
                  type="date"
                  className="w-full rounded-md border border-gray-300 px-2 py-1 text-sm"
                  value={newSubtask.next_date}
                  onChange={(e) => setNewSubtask((prev) => ({ ...prev, next_date: e.target.value }))}
                />
              </div>
              <div>
                <label className="block text-xs font-medium text-gray-700 mb-1">
                  {t('common.description') || 'Description'}
                </label>
                <textarea
                  className="w-full rounded-md border border-gray-300 px-2 py-1 text-sm"
                  rows={3}
                  value={newSubtask.result}
                  onChange={(e) => setNewSubtask((prev) => ({ ...prev, result: e.target.value }))}
                />
              </div>
              <div className="pt-2 flex justify-end">
                <button
                  type="submit"
                  disabled={submitting}
                  className="px-4 py-2 rounded-lg bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700 disabled:opacity-50"
                >
                  {submitting ? t('common.saving') || 'Saving…' : t('common.save') || 'Save'}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
};

export default TaskDetailModal;


