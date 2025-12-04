
import React, { useMemo, useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useI18n } from '../hooks/useI18n';
import { fetchTasks } from '../services/tasks';
import { fetchCases } from '../services/cases';
import type { Task, TaskStatus, TaskPriority } from '../types';
import { CalendarIcon, XIcon, PlusIcon } from '../components/icons';
import NewTaskForm from '../components/NewTaskForm';
import TaskDetailModal from '../components/TaskDetailModal';

const PriorityBadge: React.FC<{ priority: TaskPriority }> = ({ priority }) => {
    const { t } = useI18n();
    const classes = {
        high: 'bg-red-100 text-red-800',
        medium: 'bg-yellow-100 text-yellow-800',
        low: 'bg-gray-100 text-gray-800',
    };
    return (
        <span className={`px-2 py-1 text-xs font-medium rounded-full ${classes[priority]}`}>
            {t(`priority.${priority}`)}
        </span>
    );
};

interface HierarchicalTask extends Task {
    subTasks: HierarchicalTask[];
}

const TaskCard: React.FC<{ 
    task: HierarchicalTask, 
    cases: any[];
    navigate: (path: string) => void;
    completedSubTasks: Set<number>;
    onToggleSubTask: (taskId: number) => void;
    onAddSubTask: (parentId: number) => void;
    onOpenDetail: (taskId: number) => void;
}> = ({ task, cases, navigate, completedSubTasks, onToggleSubTask, onAddSubTask, onOpenDetail }) => {
    const { t, language } = useI18n();
    const relatedCase = cases.find(c => c.id === task.case_id);

    const handleCaseClick = (e: React.MouseEvent) => {
        e.stopPropagation();
        navigate(`/cases/${task.case_id}`);
    }

    const handleCardClick = () => {
        onOpenDetail(task.id);
    };

    return (
        <div
            className="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-3 cursor-pointer hover:shadow-md transition-shadow"
            onClick={handleCardClick}
        >
            <div className="flex justify-between items-start">
              <p className="font-bold text-gray-800">{task.title}</p>
              <button
              onClick={(e) => { e.stopPropagation(); onAddSubTask(task.id); }}
                className="text-xs flex items-center gap-1 text-primary-600 hover:text-primary-800 font-semibold"
                aria-label={`${t('task.add_subtask')} for ${task.title}`}
              >
                  <PlusIcon className="w-3 h-3"/>
                  {t('task.add_subtask')}
              </button>
            </div>
            {task.description && <p className="text-sm text-gray-600 mt-1">{task.description}</p>}
            <div className="mt-3 flex justify-between items-center text-sm text-gray-500">
                <div className="flex items-center gap-2">
                    <CalendarIcon className="w-4 h-4" />
                    <span>{t('task.due_date')}: {new Date(task.dueDate).toLocaleDateString()}</span>
                </div>
                <PriorityBadge priority={task.priority} />
            </div>
            {relatedCase && (
                <div className="mt-2 pt-2 border-t border-gray-100">
                    <a 
                        href="#" 
                        onClick={handleCaseClick}
                        className="text-sm text-primary-600 hover:underline"
                    >
                       {t('task.case')}: {language === 'ar' ? relatedCase.case_name_ar : relatedCase.case_name_en}
                    </a>
                </div>
            )}
            {task.subTasks && task.subTasks.length > 0 && (
                <div className="mt-3 pt-3 border-t border-gray-100">
                    <h4 className="text-xs font-bold text-gray-500 uppercase mb-2">{t('task.subtasks')}</h4>
                    <div className="space-y-2">
                        {task.subTasks.map(subTask => (
                            <div key={subTask.id} className="flex items-center gap-2 ps-2">
                                <input
                                    id={`subtask-${subTask.id}`}
                                    type="checkbox"
                                    checked={completedSubTasks.has(subTask.id)}
                                    onChange={() => onToggleSubTask(subTask.id)}
                                    className="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 cursor-pointer"
                                />
                                <label htmlFor={`subtask-${subTask.id}`} className={`text-sm ${completedSubTasks.has(subTask.id) ? 'text-gray-400 line-through' : 'text-gray-700'} cursor-pointer`}>
                                    {subTask.title}
                                </label>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
};

const TaskColumn: React.FC<{ 
    title: string;
    tasks: HierarchicalTask[];
    status: TaskStatus;
    cases: any[];
    navigate: (path: string) => void;
    completedSubTasks: Set<number>;
    onToggleSubTask: (taskId: number) => void;
    onAddSubTask: (parentId: number) => void;
    onOpenDetail: (taskId: number) => void;
}> = ({ title, tasks, status, cases, navigate, completedSubTasks, onToggleSubTask, onAddSubTask, onOpenDetail }) => {
    const { t } = useI18n();
    const statusClasses = {
        todo: 'border-blue-500',
        'in-progress': 'border-yellow-500',
        completed: 'border-green-500',
    };

    return (
        <div className="bg-gray-50 rounded-lg p-4 flex-1">
            <h3 className={`font-semibold text-gray-700 pb-3 mb-3 border-b-2 ${statusClasses[status]}`}>{title} ({tasks.length})</h3>
            <div className="space-y-3">
                {tasks.length > 0 ? (
                    tasks.map(task => (
                        <TaskCard 
                            key={task.id} 
                            task={task} 
                            cases={cases}
                            navigate={navigate}
                            completedSubTasks={completedSubTasks}
                            onToggleSubTask={onToggleSubTask}
                            onAddSubTask={onAddSubTask}
                            onOpenDetail={onOpenDetail}
                        />
                    ))
                ) : (
                    <p className="text-sm text-gray-500 p-4 text-center">{t('tasks_page.no_tasks')}</p>
                )}
            </div>
        </div>
    );
};


const TasksPage: React.FC = () => {
    const navigate = useNavigate();
    const { t, language } = useI18n();
    const [completedSubTasks, setCompletedSubTasks] = useState(new Set<number>());
    const [searchTerm, setSearchTerm] = useState('');
    const [newTaskModalState, setNewTaskModalState] = useState<{ isOpen: boolean; parentId?: number }>({ isOpen: false, parentId: undefined });
    const [filters, setFilters] = useState({
        priority: '',
        caseId: ''
    });
    const [tasks, setTasks] = useState<Task[]>([]);
    const [cases, setCases] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [detailTaskId, setDetailTaskId] = useState<number | null>(null);

    useEffect(() => {
        const loadData = async () => {
            try {
                setLoading(true);
                const [tasksData, casesData] = await Promise.all([
                    fetchTasks(),
                    fetchCases(),
                ]);
                setTasks(tasksData.data || tasksData);
                setCases(casesData.data || casesData);
            } catch (err: any) {
                setError(err.message || 'Failed to load data');
            } finally {
                setLoading(false);
            }
        };
        loadData();
    }, []);

    const handleFilterChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
        setFilters(prev => ({ ...prev, [e.target.name]: e.target.value }));
    };
    
    const clearFilters = () => {
        setFilters({ priority: '', caseId: '' });
    };

    const handleSaveTask = async () => {
        try {
            setNewTaskModalState({ isOpen: false });
            const refreshed = await fetchTasks();
            setTasks(Array.isArray(refreshed) ? refreshed : refreshed?.data || []);
        } catch (err: any) {
            setError(err.message || 'Failed to refresh tasks');
        }
    };

    const handleOpenNewTaskModal = (parentId?: number) => {
        setNewTaskModalState({ isOpen: true, parentId });
    };

    const toggleSubTask = (taskId: number) => {
        setCompletedSubTasks(prev => {
            const newSet = new Set(prev);
            if (newSet.has(taskId)) {
                newSet.delete(taskId);
            } else {
                newSet.add(taskId);
            }
            return newSet;
        });
    };
    
    const casesWithTasks = useMemo(() => {
        const caseIdsWithTasks = new Set(tasks.map(t => t.case_id));
        return cases.filter(c => caseIdsWithTasks.has(c.id));
    }, [tasks, cases]);

    const hierarchicalTasks = useMemo(() => {
        const tasksById = new Map(tasks.map(task => [task.id, { ...task, subTasks: [] as HierarchicalTask[] }]));
        const topLevelTasks: HierarchicalTask[] = [];

        for (const task of tasksById.values()) {
            if (task.parentId && tasksById.has(task.parentId)) {
                const parent = tasksById.get(task.parentId)!;
                parent.subTasks.push(task);
            } else {
                topLevelTasks.push(task);
            }
        }
        
        const filteredByDropdowns = topLevelTasks.filter(task => {
            if (filters.priority && task.priority !== filters.priority) return false;
            if (filters.caseId && task.case_id !== parseInt(filters.caseId, 10)) return false;
            return true;
        });

        if (!searchTerm) {
            return filteredByDropdowns;
        }

        const lowercasedSearch = searchTerm.toLowerCase();
        const taskMatches = (t: Task) => {
            const relatedCase = cases.find(c => c.id === t.case_id);
            const caseName = relatedCase ? `${relatedCase.case_name_ar} ${relatedCase.case_name_en}` : '';
            const searchableText = `${t.title} ${t.description || ''} ${caseName}`.toLowerCase();
            return searchableText.includes(lowercasedSearch);
        };

        return filteredByDropdowns
            .map(task => {
                const parentMatches = taskMatches(task);
                const matchingSubTasks = task.subTasks.filter(taskMatches);

                if (parentMatches || matchingSubTasks.length > 0) {
                    return {
                        ...task,
                        subTasks: parentMatches ? task.subTasks : matchingSubTasks,
                    };
                }
                return null;
            })
            .filter((task): task is HierarchicalTask => task !== null);

    }, [searchTerm, filters, tasks, cases]);


    const tasksByStatus = useMemo(() => {
        return hierarchicalTasks.reduce((acc, task) => {
            acc[task.status].push(task);
            return acc;
        }, { todo: [], 'in-progress': [], completed: [] } as Record<TaskStatus, HierarchicalTask[]>);
    }, [hierarchicalTasks]);

    if (loading) {
        return (
            <div className="container mx-auto">
                <div className="text-center py-10">
                    <p className="text-gray-600">{t('common.loading')}</p>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="container mx-auto">
                <div className="text-center py-10">
                    <p className="text-red-600">{t('common.error')}: {error}</p>
                </div>
            </div>
        );
    }

    return (
        <div className="container mx-auto">
            <div className="flex justify-between items-center mb-6">
                <h1 className="text-3xl font-bold text-gray-800">{t('tasks_page.title')}</h1>
                <button
                    onClick={() => handleOpenNewTaskModal()}
                    className="flex items-center gap-2 px-4 py-2 bg-primary-600 border border-transparent rounded-lg text-white font-semibold hover:bg-primary-700 transition-colors"
                >
                    <PlusIcon className="w-5 h-5" />
                    {t('tasks_page.new_task')}
                </button>
            </div>

            <div className="bg-white p-3 rounded-lg shadow-sm mb-6 border flex flex-col sm:flex-row gap-4">
                 <div className="relative flex-grow">
                    <div className="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                        <svg className="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                            <path stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                        </svg>
                    </div>
                    <input
                        type="search"
                        className="block w-full p-2.5 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-primary-500 focus:border-primary-500"
                        placeholder={t('app.search_placeholder_generic')}
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                    />
                </div>
                <select name="priority" value={filters.priority} onChange={handleFilterChange} className="p-2.5 border border-gray-300 rounded-lg text-sm bg-white">
                    <option value="">{t('filters.all_priorities')}</option>
                    <option value="high">{t('priority.high')}</option>
                    <option value="medium">{t('priority.medium')}</option>
                    <option value="low">{t('priority.low')}</option>
                </select>
                <select name="caseId" value={filters.caseId} onChange={handleFilterChange} className="p-2.5 border border-gray-300 rounded-lg text-sm bg-white">
                    <option value="">{t('filters.all_cases')}</option>
                    {casesWithTasks.map(c => <option key={c.id} value={c.id}>{language === 'ar' ? c.case_name_ar : c.case_name_en}</option>)}
                </select>
                {(filters.priority || filters.caseId) && (
                     <button
                        onClick={clearFilters}
                        className="flex items-center justify-center gap-2 text-sm text-red-600 hover:text-red-800 font-semibold px-3"
                    >
                        <XIcon className="w-4 h-4" />
                        {t('dashboard.clear_filters')}
                    </button>
                )}
            </div>
            
            <div className="flex flex-col md:flex-row gap-6">
                <TaskColumn 
                    title={t('tasks_page.todo')} 
                    tasks={tasksByStatus.todo} 
                    status="todo"
                    cases={cases}
                    navigate={navigate}
                    completedSubTasks={completedSubTasks}
                    onToggleSubTask={toggleSubTask}
                    onAddSubTask={handleOpenNewTaskModal}
                    onOpenDetail={(id) => setDetailTaskId(id)}
                />
                <TaskColumn 
                    title={t('tasks_page.in_progress')} 
                    tasks={tasksByStatus['in-progress']} 
                    status="in-progress"
                    cases={cases}
                    navigate={navigate}
                    completedSubTasks={completedSubTasks}
                    onToggleSubTask={toggleSubTask}
                    onAddSubTask={handleOpenNewTaskModal}
                    onOpenDetail={(id) => setDetailTaskId(id)}
                />
                <TaskColumn 
                    title={t('tasks_page.completed')} 
                    tasks={tasksByStatus.completed} 
                    status="completed"
                    cases={cases}
                    navigate={navigate}
                    completedSubTasks={completedSubTasks}
                    onToggleSubTask={toggleSubTask}
                    onAddSubTask={handleOpenNewTaskModal}
                    onOpenDetail={(id) => setDetailTaskId(id)}
                />
            </div>
            
            {newTaskModalState.isOpen && (
                <NewTaskForm
                    onClose={() => setNewTaskModalState({ isOpen: false })}
                    onSave={handleSaveTask}
                    parentId={newTaskModalState.parentId}
                />
            )}
            {detailTaskId !== null && (
                <TaskDetailModal
                    taskId={detailTaskId}
                    onClose={() => setDetailTaskId(null)}
                    onUpdated={handleSaveTask}
                />
            )}
        </div>
    );
};

export default TasksPage;