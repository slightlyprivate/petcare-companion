import { useState } from 'react';
import {
  useCreatePetRoutine,
  useUpdatePetRoutine,
  useDeletePetRoutine,
} from '../api/routines/hooks';
import { useDeleteConfirmation } from './useDeleteConfirmation';
import type { PetRoutine } from '../api/routines/types';

interface UseRoutineModalReturn {
  // Modal state
  isOpen: boolean;
  isEditMode: boolean;
  editingRoutine: PetRoutine | null;

  // Form state
  formState: {
    name: string;
    description: string;
    timeOfDay: string;
    daysOfWeek: number[];
    error: string | null;
  };

  // Form setters
  setName: (name: string) => void;
  setDescription: (description: string) => void;
  setTimeOfDay: (time: string) => void;
  setDaysOfWeek: (days: number[]) => void;
  toggleDayOfWeek: (day: number) => void;

  // Modal actions
  openCreate: () => void;
  openEdit: (routine: PetRoutine) => void;
  close: () => void;
  handleSubmit: (e: React.FormEvent) => Promise<void>;

  // Delete state
  deleteState: {
    isConfirmOpen: boolean;
    isDeleting: boolean;
    confirmDelete: (id: string | number) => void;
    cancelDelete: () => void;
    executeDelete: () => Promise<void>;
  };

  // Loading states
  isSubmitting: boolean;
}

/**
 * Hook to manage routine modal state and actions
 */
export function useRoutineModal(petId: string | number): UseRoutineModalReturn {
  const createRoutine = useCreatePetRoutine();
  const updateRoutine = useUpdatePetRoutine();
  const deleteRoutine = useDeletePetRoutine();

  // Modal state
  const [isOpen, setIsOpen] = useState(false);
  const [editingRoutine, setEditingRoutine] = useState<PetRoutine | null>(null);

  // Form state
  const [name, setName] = useState('');
  const [description, setDescription] = useState('');
  const [timeOfDay, setTimeOfDay] = useState('09:00');
  const [daysOfWeek, setDaysOfWeek] = useState<number[]>([]);
  const [error, setError] = useState<string | null>(null);

  // Delete confirmation
  const deleteConfirm = useDeleteConfirmation<string | number>();

  const resetForm = () => {
    setName('');
    setDescription('');
    setTimeOfDay('09:00');
    setDaysOfWeek([]);
    setError(null);
  };

  const openCreate = () => {
    resetForm();
    setEditingRoutine(null);
    setIsOpen(true);
  };

  const openEdit = (routine: PetRoutine) => {
    setName(routine.name);
    setDescription(routine.description || '');
    setTimeOfDay(routine.time_of_day);
    setDaysOfWeek(routine.days_of_week);
    setError(null);
    setEditingRoutine(routine);
    setIsOpen(true);
  };

  const close = () => {
    setIsOpen(false);
    setEditingRoutine(null);
    resetForm();
  };

  const toggleDayOfWeek = (day: number) => {
    setDaysOfWeek((prev) => {
      if (prev.includes(day)) {
        return prev.filter((d) => d !== day);
      }
      return [...prev, day].sort();
    });
  };

  const validateForm = (): boolean => {
    if (!name.trim()) {
      setError('Routine name is required');
      return false;
    }
    if (name.length > 100) {
      setError('Routine name must be less than 100 characters');
      return false;
    }
    if (!timeOfDay) {
      setError('Time of day is required');
      return false;
    }
    if (daysOfWeek.length === 0) {
      setError('Please select at least one day of the week');
      return false;
    }
    setError(null);
    return true;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!validateForm()) {
      return;
    }

    try {
      if (editingRoutine) {
        await updateRoutine.mutateAsync({
          id: editingRoutine.id,
          name,
          description: description || undefined,
          time_of_day: timeOfDay,
          days_of_week: daysOfWeek,
        });
      } else {
        await createRoutine.mutateAsync({
          petId,
          name,
          description: description || undefined,
          time_of_day: timeOfDay,
          days_of_week: daysOfWeek,
        });
      }
      close();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Failed to save routine');
    }
  };

  const executeDelete = async () => {
    await deleteConfirm.executeDelete((id) => deleteRoutine.mutateAsync(id));
  };

  return {
    // Modal state
    isOpen,
    isEditMode: !!editingRoutine,
    editingRoutine,

    // Form state
    formState: {
      name,
      description,
      timeOfDay,
      daysOfWeek,
      error,
    },

    // Form setters
    setName,
    setDescription,
    setTimeOfDay,
    setDaysOfWeek,
    toggleDayOfWeek,

    // Modal actions
    openCreate,
    openEdit,
    close,
    handleSubmit,

    // Delete state
    deleteState: {
      isConfirmOpen: deleteConfirm.isConfirmOpen,
      isDeleting: deleteRoutine.isPending,
      confirmDelete: deleteConfirm.confirmDelete,
      cancelDelete: deleteConfirm.cancelDelete,
      executeDelete,
    },

    // Loading states
    isSubmitting: createRoutine.isPending || updateRoutine.isPending,
  };
}
