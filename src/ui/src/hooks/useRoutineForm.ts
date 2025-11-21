import { useState } from 'react';

interface RoutineFormState {
  name: string;
  description: string;
  timeOfDay: string;
  daysOfWeek: number[];
}

interface UseRoutineFormProps {
  initialValues?: Partial<RoutineFormState>;
  onSuccess?: () => void;
}

interface UseRoutineFormReturn extends RoutineFormState {
  showForm: boolean;
  formError: string | null;
  isSubmitting: boolean;
  setName: (name: string) => void;
  setDescription: (description: string) => void;
  setTimeOfDay: (time: string) => void;
  setDaysOfWeek: (days: number[]) => void;
  toggleDayOfWeek: (day: number) => void;
  handleSubmit: (e: React.FormEvent) => Promise<boolean>;
  toggleForm: () => void;
  resetForm: () => void;
  validate: () => boolean;
}

const defaultState: RoutineFormState = {
  name: '',
  description: '',
  timeOfDay: '09:00',
  daysOfWeek: [],
};

/**
 * Hook to manage routine form state and submission
 */
export function useRoutineForm({
  initialValues,
  onSuccess,
}: UseRoutineFormProps): UseRoutineFormReturn {
  const [showForm, setShowForm] = useState(false);
  const [name, setName] = useState(initialValues?.name || defaultState.name);
  const [description, setDescription] = useState(
    initialValues?.description || defaultState.description,
  );
  const [timeOfDay, setTimeOfDay] = useState(initialValues?.timeOfDay || defaultState.timeOfDay);
  const [daysOfWeek, setDaysOfWeek] = useState<number[]>(
    initialValues?.daysOfWeek || defaultState.daysOfWeek,
  );
  const [formError, setFormError] = useState<string | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const toggleDayOfWeek = (day: number) => {
    setDaysOfWeek((prev) => {
      if (prev.includes(day)) {
        return prev.filter((d) => d !== day);
      }
      return [...prev, day].sort();
    });
  };

  const validate = (): boolean => {
    if (!name.trim()) {
      setFormError('Routine name is required');
      return false;
    }
    if (name.length > 100) {
      setFormError('Routine name must be less than 100 characters');
      return false;
    }
    if (!timeOfDay) {
      setFormError('Time of day is required');
      return false;
    }
    if (daysOfWeek.length === 0) {
      setFormError('Please select at least one day of the week');
      return false;
    }
    setFormError(null);
    return true;
  };

  const resetForm = () => {
    setName(initialValues?.name || defaultState.name);
    setDescription(initialValues?.description || defaultState.description);
    setTimeOfDay(initialValues?.timeOfDay || defaultState.timeOfDay);
    setDaysOfWeek(initialValues?.daysOfWeek || defaultState.daysOfWeek);
    setFormError(null);
    setIsSubmitting(false);
  };

  const handleSubmit = async (e: React.FormEvent): Promise<boolean> => {
    e.preventDefault();

    if (!validate()) {
      return false;
    }

    setIsSubmitting(true);
    setFormError(null);

    try {
      // Note: Actual API call will be done by parent component
      // This hook only manages form state
      onSuccess?.();
      resetForm();
      return true;
    } catch (err) {
      setFormError(err instanceof Error ? err.message : 'Failed to save routine');
      return false;
    } finally {
      setIsSubmitting(false);
    }
  };

  const toggleForm = () => {
    if (showForm) {
      resetForm();
    }
    setShowForm(!showForm);
  };

  return {
    name,
    description,
    timeOfDay,
    daysOfWeek,
    showForm,
    formError,
    isSubmitting,
    setName,
    setDescription,
    setTimeOfDay,
    setDaysOfWeek,
    toggleDayOfWeek,
    handleSubmit,
    toggleForm,
    resetForm,
    validate,
  };
}
